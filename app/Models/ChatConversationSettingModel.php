<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatConversationSettingModel extends Model
{
    protected $table            = 'chat_conversation_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'chat_type',
        'target_id',
        'target_sub_id',
        'is_pinned',
        'pinned_at',
        'is_archived',
        'archived_at',
        'cleared_at',
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
                CREATE TABLE IF NOT EXISTS `chat_conversation_settings` (
                    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `user_id`        INT UNSIGNED NOT NULL,
                    `chat_type`      ENUM('direct', 'marketplace') NOT NULL,
                    `target_id`      INT UNSIGNED NOT NULL,
                    `target_sub_id`  INT UNSIGNED DEFAULT 0,
                    `is_pinned`      TINYINT(1) DEFAULT 0,
                    `pinned_at`      DATETIME NULL,
                    `is_archived`    TINYINT(1) DEFAULT 0,
                    `archived_at`    DATETIME NULL,
                    `cleared_at`     DATETIME NULL,
                    `created_at`     DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `uk_user_chat_thread` (`user_id`, `chat_type`, `target_id`, `target_sub_id`),
                    KEY `idx_ucs_user` (`user_id`, `is_pinned`, `is_archived`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (\Throwable $e) {
            log_message('error', 'Auto create chat_conversation_settings table error: ' . $e->getMessage());
        }
    }

    /**
     * Ambil seluruh setting percakapan untuk user tertentu
     * Return array dengan key format: "{type}_{targetId}_{targetSubId}"
     */
    public function getSettingsForUser(int $userId): array
    {
        $this->ensureTable();
        try {
            $rows = $this->where('user_id', $userId)->findAll();
            $map = [];
            foreach ($rows as $r) {
                $key = $r['chat_type'] . '_' . $r['target_id'] . '_' . (int)($r['target_sub_id'] ?? 0);
                $map[$key] = [
                    'is_pinned'   => (int)($r['is_pinned'] ?? 0) === 1,
                    'pinned_at'   => $r['pinned_at'],
                    'is_archived' => (int)($r['is_archived'] ?? 0) === 1,
                    'archived_at' => $r['archived_at'],
                    'cleared_at'  => $r['cleared_at'],
                ];
            }
            return $map;
        } catch (\Throwable $e) {
            log_message('error', 'getSettingsForUser error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Toggle status pin untuk percakapan
     */
    public function togglePin(int $userId, string $chatType, int $targetId, int $targetSubId = 0): array
    {
        $this->ensureTable();
        $existing = $this->where([
            'user_id'       => $userId,
            'chat_type'     => $chatType,
            'target_id'     => $targetId,
            'target_sub_id' => $targetSubId,
        ])->first();

        $now = date('Y-m-d H:i:s');

        if ($existing) {
            $newPinned = (int)($existing['is_pinned'] ?? 0) === 1 ? 0 : 1;
            $this->update($existing['id'], [
                'is_pinned'   => $newPinned,
                'pinned_at'   => $newPinned ? $now : null,
                'is_archived' => 0, // Unarchive jika di-pin
                'archived_at' => null,
            ]);
            return [
                'is_pinned'   => (bool)$newPinned,
                'is_archived' => false,
            ];
        } else {
            $this->insert([
                'user_id'       => $userId,
                'chat_type'     => $chatType,
                'target_id'     => $targetId,
                'target_sub_id' => $targetSubId,
                'is_pinned'     => 1,
                'pinned_at'     => $now,
                'is_archived'   => 0,
            ]);
            return [
                'is_pinned'   => true,
                'is_archived' => false,
            ];
        }
    }

    /**
     * Toggle status archive untuk percakapan
     */
    public function toggleArchive(int $userId, string $chatType, int $targetId, int $targetSubId = 0): array
    {
        $this->ensureTable();
        $existing = $this->where([
            'user_id'       => $userId,
            'chat_type'     => $chatType,
            'target_id'     => $targetId,
            'target_sub_id' => $targetSubId,
        ])->first();

        $now = date('Y-m-d H:i:s');

        if ($existing) {
            $newArchived = (int)($existing['is_archived'] ?? 0) === 1 ? 0 : 1;
            $this->update($existing['id'], [
                'is_archived' => $newArchived,
                'archived_at' => $newArchived ? $now : null,
                'is_pinned'   => 0, // Unpin jika diarsipkan
                'pinned_at'   => null,
            ]);
            return [
                'is_archived' => (bool)$newArchived,
                'is_pinned'   => false,
            ];
        } else {
            $this->insert([
                'user_id'       => $userId,
                'chat_type'     => $chatType,
                'target_id'     => $targetId,
                'target_sub_id' => $targetSubId,
                'is_archived'   => 1,
                'archived_at'   => $now,
                'is_pinned'     => 0,
            ]);
            return [
                'is_archived' => true,
                'is_pinned'   => false,
            ];
        }
    }

    /**
     * Hapus percakapan (tandai cleared_at)
     */
    public function deleteChat(int $userId, string $chatType, int $targetId, int $targetSubId = 0): bool
    {
        $this->ensureTable();
        $now = date('Y-m-d H:i:s');

        $existing = $this->where([
            'user_id'       => $userId,
            'chat_type'     => $chatType,
            'target_id'     => $targetId,
            'target_sub_id' => $targetSubId,
        ])->first();

        if ($existing) {
            $this->update($existing['id'], [
                'cleared_at'  => $now,
                'is_pinned'   => 0,
                'is_archived' => 0,
                'pinned_at'   => null,
                'archived_at' => null,
            ]);
        } else {
            $this->insert([
                'user_id'       => $userId,
                'chat_type'     => $chatType,
                'target_id'     => $targetId,
                'target_sub_id' => $targetSubId,
                'cleared_at'    => $now,
                'is_pinned'     => 0,
                'is_archived'   => 0,
            ]);
        }

        // Hapus juga pesan secara permanen jika diinginkan
        if ($chatType === 'direct') {
            $this->db->table('direct_chats')
                ->groupStart()
                    ->where('sender_id', $userId)->where('receiver_id', $targetId)
                ->groupEnd()
                ->orGroupStart()
                    ->where('sender_id', $targetId)->where('receiver_id', $userId)
                ->groupEnd()
                ->delete();
        } elseif ($chatType === 'marketplace') {
            $this->db->table('marketplace_chats')
                ->where('listing_id', $targetId)
                ->where('buyer_id', $targetSubId)
                ->delete();
        }

        return true;
    }
}
