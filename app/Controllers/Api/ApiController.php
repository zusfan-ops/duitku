<?php

namespace App\Controllers\Api;

use App\Libraries\ApiAuth;
use CodeIgniter\Controller;

abstract class ApiController extends Controller
{
    protected function uid(): int
    {
        return ApiAuth::id();
    }

    protected function ok(array $data = []): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response
            ->setContentType('application/json')
            ->setJSON(array_merge(['success' => true], $data));
    }

    protected function fail(string $message, int $status = 200): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON(['success' => false, 'message' => $message]);
    }

    protected function amount($raw): float
    {
        if ($raw === null || $raw === '') {
            return 0.0;
        }
        $str = str_replace(['.', ','], ['', '.'], (string) $raw);
        return (float) $str;
    }
}
