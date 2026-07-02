<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MigrateController extends Controller
{
    public function index()
    {
        try {
            $migrate = \Config\Services::migrations();
            $migrate->latest();
            $db     = \Config\Database::connect();
            $tables = $db->listTables();
            return "✅ Database berhasil di-update ke versi terbaru!<br><br>"
                 . "Tabel yang ada: <strong>" . implode(', ', $tables) . "</strong><br><br>"
                 . "<em>Anda sudah bisa menghapus MigrateController setelah selesai.</em>";
        } catch (\Throwable $e) {
            return "❌ Gagal update database: " . $e->getMessage();
        }
    }
}
