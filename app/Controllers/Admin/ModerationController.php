<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class ModerationController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    private function ensureReportsTable(): void
    {
        try {
            $this->db = \Config\Database::connect();
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `user_reports` (
                    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `reporter_id`   INT UNSIGNED NOT NULL,
                    `reported_user_id` INT UNSIGNED NULL,
                    `target_type`   ENUM('user', 'listing', 'comment', 'message') NOT NULL DEFAULT 'user',
                    `target_id`     INT UNSIGNED NULL,
                    `reason`        ENUM('spam', 'scam', 'inappropriate', 'harassment', 'fake', 'illegal', 'other') NOT NULL DEFAULT 'other',
                    `description`   TEXT NULL,
                    `status`        ENUM('pending', 'reviewed', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
                    `admin_note`    TEXT NULL,
                    `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    KEY `idx_ur_reporter` (`reporter_id`),
                    KEY `idx_ur_reported_user` (`reported_user_id`),
                    KEY `idx_ur_target` (`target_type`, `target_id`),
                    KEY `idx_ur_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (\Throwable $e) {
            log_message('error', '[Moderation] ensure table failed: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Panel Moderasi & Laporan Pengguna
     * GET /admin/reports
     */
    public function index()
    {
        $this->ensureReportsTable();
        $db = \Config\Database::connect();

        $status = $this->request->getGet('status') ?: 'pending';
        $search = trim($this->request->getGet('q') ?? '');

        $builder = $db->table('user_reports')
            ->select('user_reports.*, u.name AS reporter_name, u.email AS reporter_email, ru.name AS reported_name, ru.email AS reported_email')
            ->join('users u', 'u.id = user_reports.reporter_id', 'left')
            ->join('users ru', 'ru.id = user_reports.reported_user_id', 'left');

        if ($status !== 'all') {
            $builder->where('user_reports.status', $status);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('u.name', $search)
                ->orLike('u.email', $search)
                ->orLike('ru.name', $search)
                ->orLike('ru.email', $search)
                ->orLike('user_reports.description', $search)
                ->groupEnd();
        }

        $reports = $builder
            ->orderBy('user_reports.created_at', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();

        $stats = $db->table('user_reports')
            ->select("status, COUNT(*) AS total")
            ->groupBy('status')
            ->get()
            ->getResultArray();
        $statMap = [];
        foreach ($stats as $s) {
            $statMap[$s['status']] = (int) $s['total'];
        }

        $data = [
            'pageTitle'  => 'Moderasi Konten & Laporan Pengguna',
            'activeMenu' => 'reports',
            'reports'    => $reports,
            'status'     => $status,
            'search'     => $search,
            'statMap'    => $statMap,
        ];

        return view('admin/moderation/index', $data);
    }

    /**
     * Update status laporan
     * POST /admin/reports/update/:id
     */
    public function updateStatus(int $id)
    {
        $newStatus = $this->request->getPost('status') ?? '';
        $adminNote = trim($this->request->getPost('admin_note') ?? '');

        $valid = ['pending', 'reviewed', 'resolved', 'dismissed'];
        if (!in_array($newStatus, $valid, true)) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        $db = \Config\Database::connect();
        $db->table('user_reports')->update([
            'status'     => $newStatus,
            'admin_note' => $adminNote,
        ], ['id' => $id]);

        return redirect()->back()->with('success', 'Status laporan #' . $id . ' diperbarui menjadi "' . $newStatus . '".');
    }

    /**
     * Ban user (hapus akun) karena pelanggaran berat
     * POST /admin/reports/ban-user/:id
     */
    public function banUser(int $id)
    {
        $db = \Config\Database::connect();
        $report = $db->table('user_reports')->where('id', $id)->get()->getRowArray();
        if (!$report) {
            return redirect()->back()->with('error', 'Laporan tidak ditemukan.');
        }

        $userId = (int) ($report['reported_user_id'] ?? 0);
        if ($userId <= 0) {
            return redirect()->back()->with('error', 'User yang dilaporkan tidak dapat diidentifikasi.');
        }

        $currentUserId = (int) session()->get('user_id');
        if ($currentUserId === $userId) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $this->userModel->delete($userId);

        $db->table('user_reports')
            ->where('reported_user_id', $userId)
            ->update(['status' => 'resolved', 'admin_note' => 'Akun dibanned permanen karena pelanggaran berat.']);

        return redirect()->back()->with('success', 'Akun pengguna #' . $userId . ' berhasil dihapus (banned permanen).');
    }
}