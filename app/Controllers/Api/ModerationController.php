<?php

namespace App\Controllers\Api;

use App\Models\UserFriendModel;
use App\Models\UserModel;

class ModerationController extends ApiController
{
    protected UserFriendModel $friendModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->friendModel = new UserFriendModel();
        $this->userModel   = new UserModel();
        $this->ensureReportsTable();
    }

    private function ensureReportsTable(): void
    {
        try {
            $db = \Config\Database::connect();
            $db->query("
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
     * POST /api/user/block
     * Body: { "user_id": 123 }
     */
    public function blockUser()
    {
        $body   = $this->request->getJSON(true);
        $userId = (int) ($body['user_id'] ?? 0);
        $myId   = $this->uid();

        if ($userId <= 0 || $userId === $myId) {
            return $this->fail('User ID tidak valid.', 400);
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return $this->fail('Pengguna tidak ditemukan.', 404);
        }

        $existing = $this->friendModel
            ->where('user_id', $myId)
            ->where('friend_id', $userId)
            ->first();

        if ($existing) {
            $this->friendModel->update($existing['id'], ['status' => 'blocked']);
        } else {
            $this->friendModel->insert([
                'user_id'   => $myId,
                'friend_id' => $userId,
                'status'    => 'blocked',
            ]);
        }

        // Auto-block reverse direction as an implicit friendship destroy
        $reverse = $this->friendModel
            ->where('user_id', $userId)
            ->where('friend_id', $myId)
            ->first();
        if ($reverse && $reverse['status'] === 'accepted') {
            $this->friendModel->update($reverse['id'], ['status' => 'blocked']);
        }

        return $this->ok(['message' => 'Pengguna berhasil diblokir.']);
    }

    /**
     * POST /api/user/unblock
     * Body: { "user_id": 123 }
     */
    public function unblockUser()
    {
        $body   = $this->request->getJSON(true);
        $userId = (int) ($body['user_id'] ?? 0);
        $myId   = $this->uid();

        if ($userId <= 0) {
            return $this->fail('User ID tidak valid.', 400);
        }

        $existing = $this->friendModel
            ->where('user_id', $myId)
            ->where('friend_id', $userId)
            ->where('status', 'blocked')
            ->first();

        if ($existing) {
            $this->friendModel->delete($existing['id']);
        }

        return $this->ok(['message' => 'Blokir dibuka.']);
    }

    /**
     * GET /api/user/blocked-list
     */
    public function blockedList()
    {
        $myId = $this->uid();

        $blocked = $this->friendModel
            ->where('user_id', $myId)
            ->where('status', 'blocked')
            ->get()
            ->getResultArray();

        $userIds  = array_column($blocked, 'friend_id');
        $blockedUsers = [];
        if (!empty($userIds)) {
            $blockedUsers = $this->userModel
                ->whereIn('id', $userIds)
                ->select('id, name, username, email, phone, created_at')
                ->findAll();
        }

        return $this->ok(['blocked' => $blockedUsers]);
    }

    /**
     * POST /api/report
     * Body: { "target_type": "user|listing|comment|message", "target_id": 123, "reason": "...", "description": "..." }
     */
    public function report()
    {
        $body     = $this->request->getJSON(true);
        $myId     = $this->uid();
        $targetType  = $body['target_type'] ?? '';
        $targetId    = (int) ($body['target_id'] ?? 0);
        $reason      = $body['reason'] ?? 'other';
        $description = trim((string) ($body['description'] ?? ''));

        $validTypes   = ['user', 'listing', 'comment', 'message'];
        $validReasons = ['spam', 'scam', 'inappropriate', 'harassment', 'fake', 'illegal', 'other'];

        if (!in_array($targetType, $validTypes, true)) {
            return $this->fail('Tipe target tidak valid.', 400);
        }
        if ($targetId <= 0) {
            return $this->fail('Target ID tidak valid.', 400);
        }
        if (!in_array($reason, $validReasons, true)) {
            $reason = 'other';
        }

        if ($targetType === 'user' && $targetId === $myId) {
            return $this->fail('Anda tidak dapat melaporkan diri sendiri.', 400);
        }

        $db = \Config\Database::connect();

        // Duplicate check within 24h by the same reporter
        $existing = $db->table('user_reports')
            ->where('reporter_id', $myId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('status', 'pending')
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
            ->get()
            ->getRowArray();

        if ($existing) {
            return $this->fail('Anda sudah melaporkan konten ini baru-baru ini. Laporan sedang ditinjau.', 400);
        }

        $reportedUserId = ($targetType === 'user') ? $targetId : null;

        $db->table('user_reports')->insert([
            'reporter_id'      => $myId,
            'reported_user_id' => $reportedUserId,
            'target_type'      => $targetType,
            'target_id'        => $targetId,
            'reason'           => $reason,
            'description'      => $description,
            'status'           => 'pending',
        ]);

        return $this->ok([
            'message' => 'Laporan berhasil dikirim. Terima kasih telah membantu menjaga keamanan komunitas DuitKu.',
            'report_id' => $db->insertID(),
        ]);
    }

    /**
     * GET /api/report/my-reports
     */
    public function myReports()
    {
        $myId = $this->uid();
        $db   = \Config\Database::connect();

        $reports = $db->table('user_reports')
            ->where('reporter_id', $myId)
            ->orderBy('created_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        return $this->ok(['reports' => $reports]);
    }
}