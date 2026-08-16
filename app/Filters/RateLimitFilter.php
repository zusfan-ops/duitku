<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class RateLimitFilter implements FilterInterface
{
    /**
     * Rate limit authentication and sensitive endpoints
     * Max 10 requests per minute per IP address
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();
        $ip = $request->getIPAddress();
        $path = $request->getUri()->getPath();

        // Unique key per IP and action path
        $key = 'rl_' . md5($ip . '_' . $path);

        // Allow 10 requests in 60 seconds
        if ($throttler->check($key, 10, 60) === false) {
            $isJson = $request->isAJAX() || str_starts_with($path, 'api/');
            
            if ($isJson) {
                return Services::response()
                    ->setStatusCode(429)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Terlalu banyak percobaan. Silakan tunggu 1 menit sebelum mencoba kembali.'
                    ]);
            }

            return Services::response()
                ->setStatusCode(429)
                ->setBody('
                    <!DOCTYPE html>
                    <html lang="id">
                    <head><title>429 Too Many Requests</title><meta charset="UTF-8"><style>body{font-family:sans-serif;text-align:center;padding:50px;background:#0F172A;color:#fff}</style></head>
                    <body>
                        <h1>⚠️ Terlalu Banyak Percobaan</h1>
                        <p>Akses Anda dibatasi sementara demi keamanan. Silakan tunggu 1 menit sebelum mencoba login kembali.</p>
                        <a href="/login" style="color:#FB923C">← Kembali ke Login</a>
                    </body>
                    </html>
                ');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add rate-limiting security headers
        $response->setHeader('X-RateLimit-Limit', '10');
    }
}
