<?php

namespace App\Services;

class FcmService
{
    protected string $serviceAccountPath;
    protected string $projectId = 'duitku-19896';

    public function __construct()
    {
        $writablePath = defined('WRITEPATH') ? WRITEPATH : dirname(__DIR__, 2) . '/writable/';
        $rootPath     = defined('ROOTPATH') ? ROOTPATH : dirname(__DIR__, 2) . '/';

        $candidates = [
            $writablePath . 'firebase-service-account.json',
            $rootPath . 'duitku-19896-firebase-adminsdk-fbsvc-5cb52030bc.json',
            $rootPath . 'firebase-service-account.json',
        ];

        // Also check if any file matches *-firebase-adminsdk-*.json in root
        $globFiles = glob($rootPath . '*-firebase-adminsdk-*.json');
        if (!empty($globFiles)) {
            $candidates = array_merge($candidates, $globFiles);
        }

        $this->serviceAccountPath = $writablePath . 'firebase-service-account.json';
        foreach ($candidates as $path) {
            if (file_exists($path) && filesize($path) > 50) {
                $this->serviceAccountPath = $path;
                break;
            }
        }

        if (file_exists($this->serviceAccountPath)) {
            $data = json_decode(file_get_contents($this->serviceAccountPath), true);
            if (!empty($data['project_id'])) {
                $this->projectId = $data['project_id'];
            }
        }
    }

    /**
     * Memeriksa apakah file kredensial service account Firebase sudah terpasang
     */
    public function isConfigured(): bool
    {
        return file_exists($this->serviceAccountPath) && filesize($this->serviceAccountPath) > 20;
    }

    /**
     * Menyimpan file kredensial service account
     */
    public function saveServiceAccount(string $jsonContent): bool
    {
        $decoded = json_decode($jsonContent, true);
        if (!$decoded || empty($decoded['private_key']) || empty($decoded['client_email'])) {
            return false;
        }

        return file_put_contents($this->serviceAccountPath, $jsonContent) !== false;
    }

    /**
     * Menghasilkan OAuth2 Bearer Access Token dari Service Account Firebase (HTTP v1 API)
     */
    public function getAccessToken(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);
        if (!$serviceAccount) return null;

        $clientEmail = $serviceAccount['client_email'] ?? '';
        $privateKey  = $serviceAccount['private_key'] ?? '';

        if (empty($clientEmail) || empty($privateKey)) return null;

        $now = time();
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $payload = json_encode([
            'iss'   => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $now + 3600,
            'iat'   => $now,
        ]);

        $base64Header  = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode($payload);
        $signatureData = $base64Header . '.' . $base64Payload;

        $signature = '';
        $success = openssl_sign($signatureData, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$success) {
            log_message('error', 'FCM: Gagal menandatangani JWT dengan private key.');
            return null;
        }

        $jwt = $signatureData . '.' . $this->base64UrlEncode($signature);

        // Request access token dari Google OAuth2
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        curl_close($ch);

        $resData = json_decode($response, true);
        return $resData['access_token'] ?? null;
    }

    /**
     * Kirim push notifikasi massal ke Topic (misal: 'duitku_broadcasts')
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Service Account Firebase belum dikonfigurasi atau Access Token gagal dibuat.'];
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        // Convert semua data payload ke string (FCM data payload mengharuskan string key-value)
        $stringData = [];
        foreach ($data as $k => $v) {
            $stringData[(string)$k] = (string)$v;
        }
        $stringData['title'] = $title;
        $stringData['message'] = $body;

        $payload = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $stringData,
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'duitku_broadcast_channel',
                        'sound'      => 'default',
                        'default_vibrate_timings' => true,
                        'default_sound'           => true,
                    ],
                ],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json; UTF-8',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $resData = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'response' => $resData];
        }

        return ['success' => false, 'http_code' => $httpCode, 'response' => $resData];
    }

    /**
     * Kirim push notifikasi langsung ke Token Perangkat tertentu
     */
    public function sendToToken(string $deviceToken, string $title, string $body, array $data = []): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Service Account Firebase belum dikonfigurasi atau Access Token gagal dibuat.'];
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $stringData = [];
        foreach ($data as $k => $v) {
            $stringData[(string)$k] = (string)$v;
        }
        $stringData['title'] = $title;
        $stringData['message'] = $body;

        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $stringData,
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'duitku_broadcast_channel',
                        'sound'      => 'default',
                        'default_vibrate_timings' => true,
                        'default_sound'           => true,
                    ],
                ],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json; UTF-8',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $resData = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'response' => $resData];
        }

        return ['success' => false, 'http_code' => $httpCode, 'response' => $resData];
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
