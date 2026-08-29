<?php

namespace App\Models;

use CodeIgniter\Model;

class TvChannelModel extends Model
{
    protected $table            = 'tv_channels';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'name',
        'category',
        'stream_url',
        'logo_url',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    public function ensureTable(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists($this->table)) {
            $db->query("
                CREATE TABLE IF NOT EXISTS `tv_channels` (
                    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `name`        VARCHAR(150) NOT NULL,
                    `category`    VARCHAR(100) DEFAULT 'Nasional',
                    `stream_url`  TEXT         NOT NULL,
                    `logo_url`    TEXT         DEFAULT NULL,
                    `description` TEXT         DEFAULT NULL,
                    `is_active`   TINYINT(1)   DEFAULT 1,
                    `sort_order`  INT          DEFAULT 0,
                    `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    /**
     * Ambil daftar channel aktif, opsi filter kategori
     */
    public function getActiveChannels(?string $category = null): array
    {
        $this->ensureTable();
        $builder = $this->where('is_active', 1);
        if ($category && strtolower($category) !== 'semua') {
            $builder->where('category', $category);
        }
        return $builder->orderBy('sort_order', 'ASC')
                       ->orderBy('name', 'ASC')
                       ->findAll();
    }

    /**
     * Ambil daftar unik semua kategori
     */
    public function getCategories(): array
    {
        $res = $this->select('category')
                    ->where('is_active', 1)
                    ->where('category IS NOT NULL', null, false)
                    ->groupBy('category')
                    ->orderBy('category', 'ASC')
                    ->findAll();

        return array_values(array_filter(array_column($res, 'category')));
    }
}
