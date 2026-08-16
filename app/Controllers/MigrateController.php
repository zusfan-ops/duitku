<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MigrateController extends Controller
{
    public function index()
    {
        // Only allow in development or for logged in users
        if (ENVIRONMENT === 'production' && !session()->get('user_id')) {
            return $this->response->setStatusCode(403)->setBody('Access Denied: Migration endpoint is restricted.');
        }

        try {
            $migrate = \Config\Services::migrations();
            $migrate->latest();
            return "✅ Database berhasil di-update ke versi terbaru!";
        } catch (\Throwable $e) {
            return "❌ Gagal update database: " . $e->getMessage();
        }
    }
}
