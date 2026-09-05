<?php

namespace App\Models;

use CodeIgniter\Model;

class DirectChatModel extends Model
{
    protected $table            = 'direct_chats';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'sender_id',
        'receiver_id',
        'message',
        'is_read',
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
                CREATE TABLE IF NOT EXISTS `direct_chats` (
                    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `sender_id`   INT UNSIGNED NOT NULL,
                    `receiver_id` INT UNSIGNED NOT NULL,
                    `message`     TEXT NOT NULL,
                    `is_read`     TINYINT(1) DEFAULT 0,
                    `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    KEY `idx_dc_pair` (`sender_id`, `receiver_id`),
                    KEY `idx_dc_receiver` (`receiver_id`, `is_read`),
                    KEY `idx_dc_sender` (`sender_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (\Throwable $e) {
            log_message('error', 'Auto create direct_chats table error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim pesan langsung ke teman
     */
    public function sendMessage(int $senderId, int $receiverId, string $message): array
    {
        $this->ensureTable();
        $chatId = $this->insert([
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'message'     => trim($message),
            'is_read'     => 0,
        ]);

        $userModel = new UserModel();
        $sender = $userModel->find($senderId);

        return [
            'id'          => $chatId,
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'sender_name' => $sender['name'] ?? '',
            'message'     => trim($message),
            'is_read'     => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Ambil riwayat chat antara dua pengguna
     */
    public function getMessages(int $user1, int $user2, int $afterId = 0): array
    {
        $this->ensureTable();
        $builder = $this->db->table($this->table . ' dc');
        $builder->select('
            dc.*,
            u.name AS sender_name,
            u.username AS sender_username,
            u.avatar AS sender_avatar
        ');
        $builder->join('users u', 'u.id = dc.sender_id', 'inner');
        $builder->groupStart()
            ->where('dc.sender_id', $user1)->where('dc.receiver_id', $user2)
        ->groupEnd()
        ->orGroupStart()
            ->where('dc.sender_id', $user2)->where('dc.receiver_id', $user1)
        ->groupEnd();

        if ($afterId > 0) {
            $builder->where('dc.id >', $afterId);
        }

        $builder->orderBy('dc.id', 'ASC');
        return $builder->get()->getResultArray();
    }

    /**
     * Tandai pesan dari teman sebagai telah dibaca
     */
    public function markAsRead(int $senderId, int $recipientId): bool
    {
        $this->ensureTable();
        return $this->db->table($this->table)
            ->where('sender_id', $senderId)
            ->where('receiver_id', $recipientId)
            ->where('is_read', 0)
            ->set(['is_read' => 1])
            ->update();
    }

    /**
     * Ambil daftar percakapan langsung aktif untuk pengguna
     */
    public function getConversations(int $userId): array
    {
        $this->ensureTable();
        try {
            $db = $this->db;
            $sql = "
                SELECT 
                    dc.partner_id,
                    u.name AS partner_name,
                    u.username AS partner_username,
                    u.avatar AS partner_avatar,
                    u.phone AS partner_phone,
                    last_m.message AS last_message,
                    last_m.sender_id AS last_sender_id,
                    last_m.created_at AS last_message_time,
                    (
                        SELECT COUNT(*) 
                        FROM direct_chats udc 
                        WHERE udc.sender_id = dc.partner_id 
                          AND udc.receiver_id = ? 
                          AND udc.is_read = 0
                    ) AS unread_count
                FROM (
                    SELECT 
                        CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS partner_id,
                        MAX(id) AS max_id
                    FROM direct_chats
                    WHERE sender_id = ? OR receiver_id = ?
                    GROUP BY partner_id
                ) dc
                JOIN direct_chats last_m ON last_m.id = dc.max_id
                JOIN users u ON u.id = dc.partner_id
                ORDER BY last_m.id DESC
            ";

            return $db->query($sql, [$userId, $userId, $userId, $userId])->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'getConversations direct chat error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Hitung total pesan direct chat yang belum dibaca
     */
    public function getTotalUnreadCount(int $userId): int
    {
        $this->ensureTable();
        try {
            $row = $this->db->query("
                SELECT COUNT(*) AS total
                FROM direct_chats
                WHERE receiver_id = ?
                  AND is_read = 0
            ", [$userId])->getRowArray();
            return (int)($row['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
