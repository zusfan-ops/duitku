<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'app_notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'title',
        'message',
        'type',
        'target',
        'user_id',
        'action_url',
        'is_pinned',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    public function ensureTable(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists($this->table)) {
            $db->query("
                CREATE TABLE IF NOT EXISTS `app_notifications` (
                    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `title`      VARCHAR(255) NOT NULL,
                    `message`    TEXT         NOT NULL,
                    `type`       VARCHAR(50)  NOT NULL DEFAULT 'info',
                    `target`     ENUM('all', 'user') DEFAULT 'all',
                    `user_id`    INT UNSIGNED DEFAULT NULL,
                    `action_url` VARCHAR(255) DEFAULT NULL,
                    `is_pinned`  TINYINT(1)   DEFAULT 0,
                    `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } else {
            // Pastikan kolom type mendukung nilai fleksibel seperti 'update'
            try {
                $db->query("ALTER TABLE `app_notifications` MODIFY COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'info'");
            } catch (\Throwable $e) {
                // Abaikan jika sudah diubah
            }
        }
        if (!$db->tableExists('notification_reads')) {
            $db->query("
                CREATE TABLE IF NOT EXISTS `notification_reads` (
                    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `notification_id` INT UNSIGNED NOT NULL,
                    `user_id`         INT UNSIGNED NOT NULL,
                    `read_at`         DATETIME     DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY `user_notif_read` (`notification_id`, `user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    /**
     * Ambil notifikasi aktif yang relevan untuk user tertentu (broadcast all + target user)
     */
    public function getForUser(?int $userId = null, int $limit = 30): array
    {
        $this->ensureTable();
        $safeUserId = (int)($userId ?? 0);

        $db = \Config\Database::connect();
        $builder = $db->table($this->table . ' n');
        $builder->select('n.*, (CASE WHEN nr.id IS NOT NULL THEN 1 ELSE 0 END) AS is_read, nr.read_at');
        $builder->join('notification_reads nr', "nr.notification_id = n.id AND nr.user_id = {$safeUserId}", 'left');
        $builder->groupStart()
                ->where('n.target', 'all');
        if ($safeUserId > 0) {
            $builder->orWhere('n.user_id', $safeUserId);
        }
        $builder->groupEnd();
        $builder->orderBy('n.is_pinned', 'DESC');
        $builder->orderBy('n.created_at', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    /**
     * Hitung jumlah notifikasi belum dibaca untuk user
     */
    public function getUnreadCount(?int $userId = null): int
    {
        $this->ensureTable();
        $safeUserId = (int)($userId ?? 0);
        if ($safeUserId <= 0) {
            return 0;
        }

        $db = \Config\Database::connect();
        $builder = $db->table($this->table . ' n');
        $builder->join('notification_reads nr', "nr.notification_id = n.id AND nr.user_id = {$safeUserId}", 'left');
        $builder->groupStart()
                ->where('n.target', 'all')
                ->orWhere('n.user_id', $safeUserId)
                ->groupEnd();
        $builder->where('nr.id IS NULL', null, false);

        return $builder->countAllResults();
    }

    /**
     * Tandai notifikasi sebagai telah dibaca
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $this->ensureTable();
        $db = \Config\Database::connect();
        $exists = $db->table('notification_reads')
                     ->where('notification_id', $notificationId)
                     ->where('user_id', $userId)
                     ->countAllResults();

        if ($exists === 0) {
            return $db->table('notification_reads')->insert([
                'notification_id' => $notificationId,
                'user_id'         => $userId,
                'read_at'         => date('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }
}
