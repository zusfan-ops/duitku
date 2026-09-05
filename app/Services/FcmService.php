<?php

namespace App\Services;

class FcmService
{
    protected string $serviceAccountPath;
    protected string $projectId = 'duitku-19896';

    protected ?string $clientEmail = null;
    protected ?string $privateKey  = null;

    public function __construct()
    {
        $writablePath = defined('WRITEPATH') ? WRITEPATH : dirname(__DIR__, 2) . '/writable/';
        $rootPath     = defined('ROOTPATH') ? ROOTPATH : dirname(__DIR__, 2) . '/';

        // 1. Coba ambil dari Config\Firebase jika tersedia
        try {
            if (class_exists('Config\Firebase')) {
                $firebaseConfig = config('Firebase') ?? new \Config\Firebase();
                if (!empty($firebaseConfig->privateKey) && !empty($firebaseConfig->clientEmail)) {
                    $this->projectId   = $firebaseConfig->projectId ?? 'duitku-19896';
                    $this->clientEmail = $firebaseConfig->clientEmail;
                    $this->privateKey  = $firebaseConfig->privateKey;
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika helper config belum aktif
        }

        // 2. Cek file-file JSON candidate
        $candidates = [
            $writablePath . 'firebase-service-account.json',
            $rootPath . 'duitku-19896-firebase-adminsdk-fbsvc-5cb52030bc.json',
            $rootPath . 'firebase-service-account.json',
        ];

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
            if (empty($this->clientEmail) && !empty($data['client_email'])) {
                $this->clientEmail = $data['client_email'];
            }
            if (empty($this->privateKey) && !empty($data['private_key'])) {
                $this->privateKey = $data['private_key'];
            }
        }
    }

    /**
     * Memeriksa apakah file kredensial service account Firebase sudah terpasang
     */
    public function isConfigured(): bool
    {
        if (!empty($this->clientEmail) && !empty($this->privateKey)) {
            return true;
        }

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

        $this->clientEmail = $decoded['client_email'];
        $this->privateKey  = $decoded['private_key'];
        if (!empty($decoded['project_id'])) {
            $this->projectId = $decoded['project_id'];
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

        $clientEmail = $this->clientEmail;
        $privateKey  = $this->privateKey;

        if (empty($clientEmail) || empty($privateKey)) {
            if (file_exists($this->serviceAccountPath)) {
                $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);
                $clientEmail = $serviceAccount['client_email'] ?? '';
                $privateKey  = $serviceAccount['private_key'] ?? '';
            }
        }

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
            $this->lastError = 'Gagal menandatangani JWT dengan OpenSSL private key.';
            log_message('error', 'FCM: ' . $this->lastError);
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            $this->lastError = 'cURL error saat request OAuth2 Google: ' . $curlError;
            log_message('error', 'FCM: ' . $this->lastError);
            return null;
        }

        $resData = json_decode($response, true);
        if ($httpCode !== 200 || empty($resData['access_token'])) {
            $errDesc = $resData['error_description'] ?? $resData['error'] ?? ('HTTP ' . $httpCode . ': ' . $response);
            $this->lastError = 'Google OAuth2 error: ' . $errDesc;
            log_message('error', 'FCM: ' . $this->lastError);
            return null;
        }

        return $resData['access_token'];
    }

    public string $lastError = '';

    /**
     * Kirim push notifikasi massal ke Topic (misal: 'duitku_broadcasts')
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            $errMsg = $this->lastError ?: 'Service Account Firebase belum dikonfigurasi atau Access Token gagal dibuat.';
            return ['success' => false, 'message' => $errMsg];
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        // Convert semua data payload ke string (FCM data payload mengharuskan string key-value)
        $stringData = [];
        foreach ($data as $k => $v) {
            $stringData[(string)$k] = (string)$v;
        }
        $stringData['title'] = $title;
        $stringData['message'] = $body;

        $isChat = (!empty($data['type']) && $data['type'] === 'marketplace_chat');
        $channelId = $isChat ? 'duitku_chat_channel' : 'duitku_broadcast_channel';

        $androidNotification = [
            'channel_id'              => $channelId,
            'sound'                   => 'default',
            'default_vibrate_timings' => true,
            'default_sound'           => true,
            'notification_priority'   => 'PRIORITY_MAX',
            'visibility'              => 'PUBLIC',
        ];

        if ($isChat) {
            $tag = 'chat_' . ($data['listing_id'] ?? '0') . '_' . ($data['buyer_id'] ?? '0');
            $androidNotification['tag'] = $tag;
        }

        $payload = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $stringData,
                'android' => [
                    'priority'     => 'HIGH',
                    'notification' => $androidNotification,
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'message' => 'cURL error ke Google FCM: ' . $curlError];
        }

        $resData = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'response' => $resData];
        }

        $errDetail = $resData['error']['message'] ?? json_encode($resData);
        return ['success' => false, 'http_code' => $httpCode, 'message' => $errDetail, 'response' => $resData];
    }

    /**
     * Diagnosis koneksi lengkap ke Google Firebase Cloud Messaging
     */
    public function testConnection(): array
    {
        $steps = [];

        // 1. Cek Kredensial
        $configured = $this->isConfigured();
        $steps['1_credentials'] = [
            'status'  => $configured ? 'OK' : 'FAILED',
            'project' => $this->projectId,
            'email'   => $this->clientEmail ? substr($this->clientEmail, 0, 10) . '...' : 'none',
            'key'     => !empty($this->privateKey) ? 'Found (' . strlen($this->privateKey) . ' bytes)' : 'none',
        ];

        if (!$configured) {
            return ['success' => false, 'message' => 'Kredensial Firebase belum terpasang', 'steps' => $steps];
        }

        // 2. Cek Token OAuth2
        $token = $this->getAccessToken();
        $steps['2_oauth2_token'] = [
            'status' => $token ? 'OK' : 'FAILED',
            'token'  => $token ? substr($token, 0, 20) . '...' : null,
            'error'  => $this->lastError ?: null,
        ];

        if (!$token) {
            return ['success' => false, 'message' => 'Gagal generate OAuth2 Access Token: ' . $this->lastError, 'steps' => $steps];
        }

        // 3. Test Kirim Ping ke FCM
        $sendResult = $this->sendToTopic('duitku_broadcasts', '🔔 Uji Coba Koneksi FCM DuitKu', 'Ini adalah pesan tes koneksi Firebase Cloud Messaging dari Admin Panel.', [
            'type' => 'test',
            'timestamp' => (string)time(),
        ]);

        $steps['3_fcm_send'] = $sendResult;

        return [
            'success' => !empty($sendResult['success']),
            'message' => !empty($sendResult['success']) ? 'Koneksi ke Google FCM Berhasil 100%!' : ($sendResult['message'] ?? 'FCM gagal kirim'),
            'steps'   => $steps,
        ];
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
