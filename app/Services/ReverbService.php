<?php

namespace App\Services;

class ReverbService
{
    protected string $appId;
    protected string $appKey;
    protected string $appSecret;
    protected string $host;
    protected int    $port;
    protected string $scheme;
    protected bool   $enabled;

    public function __construct()
    {
        // Ambil konfigurasi dari environment (.env) atau default Reverb
        $this->appId     = env('REVERB_APP_ID', 'duitku-app');
        $this->appKey    = env('REVERB_APP_KEY', 'duitku-key');
        $this->appSecret = env('REVERB_APP_SECRET', 'duitku-secret');
        $this->host      = env('REVERB_HOST', '127.0.0.1');
        $this->port      = (int)env('REVERB_PORT', 8080);
        $this->scheme    = env('REVERB_SCHEME', 'http');
        $this->enabled   = (bool)env('REVERB_ENABLED', true);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->appKey);
    }

    public function getConfig(): array
    {
        return [
            'appKey' => $this->appKey,
            'host'   => $this->host,
            'port'   => $this->port,
            'scheme' => $this->scheme,
        ];
    }

    /**
     * Broadcast pesan ke channel tertentu via Pusher / Laravel Reverb HTTP API
     *
     * @param string|array $channels  Contoh: "user.2" atau ["user.2", "user.5"]
     * @param string       $event     Contoh: "new-message"
     * @param array        $data      Payload data yang dikirimkan
     * @return bool
     */
    public function broadcast($channels, string $event, array $data): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $channelsList = is_array($channels) ? array_values($channels) : [$channels];
            $bodyArray = [
                'name'     => $event,
                'channels' => $channelsList,
                'data'     => json_encode($data),
            ];
            $body = json_encode($bodyArray);

            $path = "/apps/{$this->appId}/events";
            $params = [
                'auth_key'       => $this->appKey,
                'auth_timestamp' => time(),
                'auth_version'   => '1.0',
                'body_md5'       => md5($body),
            ];
            ksort($params);

            // Format signature string Pusher: METHOD\nPATH\nQUERY_STRING
            $queryString = http_build_query($params);
            $signData = "POST\n{$path}\n{$queryString}";
            $signature = hash_hmac('sha256', $signData, $this->appSecret);

            $url = "{$this->scheme}://{$this->host}:{$this->port}{$path}?{$queryString}&auth_signature={$signature}";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
            ]);
            // Timeout pendek agar tidak memperlambat respon jika Reverb offline
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 300);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 600);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return ($httpCode >= 200 && $httpCode < 300);
        } catch (\Throwable $e) {
            log_message('error', 'ReverbService broadcast error: ' . $e->getMessage());
            return false;
        }
    }
}
