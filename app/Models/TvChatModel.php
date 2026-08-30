<?php

namespace App\Models;

use CodeIgniter\Model;

class TvChatModel extends Model
{
    protected $table            = 'tv_chats';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'user_name',
        'message',
        'created_at',
    ];

    protected $useTimestamps = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    public function ensureTable(): void
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists($this->table)) {
                $db->query("
                    CREATE TABLE IF NOT EXISTS `tv_chats` (
                        `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        `user_id`    INT UNSIGNED NOT NULL,
                        `user_name`  VARCHAR(150) NOT NULL,
                        `message`    TEXT         NOT NULL,
                        `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
                        INDEX `idx_tv_chats_created` (`created_at`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            }
        } catch (\Throwable $e) {
            // Silently continue if db not reachable yet
        }
    }

    /**
     * Ambil pesan chat terbaru (misal 50 pesan terakhir)
     */
    public function getRecentChats(int $limit = 50, int $afterId = 0): array
    {
        $this->ensureTable();
        $builder = $this->builder();
        if ($afterId > 0) {
            $builder->where('id >', $afterId);
            $builder->orderBy('id', 'ASC');
            $builder->limit($limit);
            return $builder->get()->getResultArray();
        }

        $builder->orderBy('id', 'DESC');
        $builder->limit($limit);
        $rows = $builder->get()->getResultArray();
        return array_reverse($rows);
    }

    /**
     * Kirim pesan baru
     */
    public function postMessage(int $userId, string $userName, string $message): array
    {
        $this->ensureTable();
        $cleanMessage = trim(strip_tags($message));
        if ($cleanMessage === '') {
            throw new \InvalidArgumentException('Pesan chat tidak boleh kosong.');
        }

        $cleanName = trim(strip_tags($userName));
        if ($cleanName === '') {
            $cleanName = 'Pengguna TV';
        }

        $data = [
            'user_id'    => $userId,
            'user_name'  => $cleanName,
            'message'    => mb_substr($cleanMessage, 0, 500),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $id = $this->insert($data, true);
        $data['id'] = (int)$id;
        return $data;
    }
}
