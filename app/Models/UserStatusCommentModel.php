<?php

namespace App\Models;

use CodeIgniter\Model;

class UserStatusCommentModel extends Model
{
    protected $table            = 'user_status_comments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'status_id',
        'user_id',
        'comment',
        'created_at',
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
                CREATE TABLE IF NOT EXISTS `user_status_comments` (
                    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `status_id`  INT UNSIGNED NOT NULL,
                    `user_id`    INT UNSIGNED NOT NULL,
                    `comment`    TEXT NOT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    KEY `idx_sc_status` (`status_id`),
                    KEY `idx_sc_user` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (\Throwable $e) {
            log_message('error', 'Auto create user_status_comments table error: ' . $e->getMessage());
        }
    }

    /**
     * Tambahkan komentar pada status
     */
    public function addComment(int $statusId, int $userId, string $comment): array
    {
        $this->ensureTable();
        $createdAt = date('Y-m-d H:i:s');
        $id = $this->insert([
            'status_id'  => $statusId,
            'user_id'    => $userId,
            'comment'    => trim($comment),
            'created_at' => $createdAt,
        ]);

        return $this->find($id);
    }

    /**
     * Dapatkan daftar komentar untuk status
     */
    public function getCommentsForStatus(int $statusId): array
    {
        $this->ensureTable();
        $sql = "
            SELECT 
                sc.id,
                sc.status_id,
                sc.user_id,
                sc.comment,
                sc.created_at,
                u.name AS user_name,
                u.username AS user_username,
                u.avatar AS user_avatar,
                s_img.value AS user_avatar_image
            FROM user_status_comments sc
            JOIN users u ON u.id = sc.user_id
            LEFT JOIN settings s_img ON s_img.user_id = u.id AND s_img.key = 'avatar_image'
            WHERE sc.status_id = ?
            ORDER BY sc.id ASC
        ";

        try {
            return $this->db->query($sql, [$statusId])->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'getCommentsForStatus error: ' . $e->getMessage());
            return [];
        }
    }
}
