<?php

namespace App\Models;

use CodeIgniter\Model;

class UserStatusModel extends Model
{
    protected $table            = 'user_statuses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'media_type',
        'media_url',
        'caption',
        'background_color',
        'created_at',
        'expires_at',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    public function ensureTable(): void
    {
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `user_statuses` (
                    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `user_id`          INT UNSIGNED NOT NULL,
                    `media_type`       ENUM('text', 'image') DEFAULT 'text',
                    `media_url`        VARCHAR(255) NULL,
                    `caption`          TEXT NULL,
                    `background_color` VARCHAR(20) DEFAULT '#2563EB',
                    `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `expires_at`       DATETIME NOT NULL,
                    KEY `idx_status_user_expires` (`user_id`, `expires_at`),
                    KEY `idx_status_expires` (`expires_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (\Throwable $e) {
            log_message('error', 'Auto create user_statuses table error: ' . $e->getMessage());
        }
    }

    /**
     * Buat status baru (aktif selama 24 jam)
     */
    public function createStatus(int $userId, string $mediaType, ?string $mediaUrl, ?string $caption, string $bgColor = '#2563EB'): array
    {
        $this->ensureTable();
        $createdAt = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $id = $this->insert([
            'user_id'          => $userId,
            'media_type'       => $mediaType,
            'media_url'        => $mediaUrl,
            'caption'          => $caption,
            'background_color' => $bgColor ?: '#2563EB',
            'created_at'       => $createdAt,
            'expires_at'       => $expiresAt,
        ]);

        return $this->find($id);
    }

    /**
     * Ambil feed status aktif yang hanya bisa dilihat oleh teman (mutual accepted) + status milik sendiri
     */
    public function getFriendStatuses(int $myId): array
    {
        $this->ensureTable();
        $now = date('Y-m-d H:i:s');

        $sql = "
            SELECT 
                s.id,
                s.user_id,
                s.media_type,
                s.media_url,
                s.caption,
                s.background_color,
                s.created_at,
                s.expires_at,
                u.name AS author_name,
                u.username AS author_username,
                u.avatar AS author_avatar,
                s_img.value AS author_avatar_image,
                (
                    SELECT COUNT(*) 
                    FROM user_status_comments sc 
                    WHERE sc.status_id = s.id
                ) AS comment_count
            FROM user_statuses s
            JOIN users u ON u.id = s.user_id
            LEFT JOIN settings s_img ON s_img.user_id = u.id AND s_img.key = 'avatar_image'
            WHERE s.expires_at > ?
              AND (
                  s.user_id = ?
                  OR s.user_id IN (
                      SELECT CASE WHEN uf.user_id = ? THEN uf.friend_id ELSE uf.user_id END
                      FROM user_friends uf
                      WHERE (uf.user_id = ? OR uf.friend_id = ?)
                        AND uf.status = 'accepted'
                  )
              )
            ORDER BY s.id DESC
        ";

        try {
            return $this->db->query($sql, [$now, $myId, $myId, $myId, $myId])->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'getFriendStatuses error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Dapatkan detail status dengan verifikasi hak akses pertemanan
     */
    public function getStatusIfAllowed(int $statusId, int $myId): ?array
    {
        $this->ensureTable();
        $status = $this->find($statusId);
        if (!$status) return null;

        $authorId = (int)$status['user_id'];
        if ($authorId === $myId) {
            return $status;
        }

        // Cek apakah berteman
        $friendModel = new UserFriendModel();
        if ($friendModel->isFriend($myId, $authorId)) {
            return $status;
        }

        return null;
    }
}
