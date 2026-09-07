<?php

namespace App\Models;

use CodeIgniter\Model;

class NeighborhoodDiscussionModel extends Model
{
    protected $table            = 'neighborhood_discussions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'neighborhood_id',
        'user_id',
        'title',
        'content',
        'category',         // 'Umum', 'Usul & Saran', 'Keamanan', 'Kebersihan', 'Info Warga', 'Kehilangan'
        'photo',
        'likes_count',
        'comments_count',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    public function ensureTable(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists($this->table)) {
            $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `neighborhood_id` INT UNSIGNED NOT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                `title` VARCHAR(255) NULL,
                `content` TEXT NOT NULL,
                `category` VARCHAR(100) NOT NULL DEFAULT 'Umum',
                `photo` VARCHAR(255) NULL,
                `likes_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `comments_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                INDEX `idx_neighborhood_disc` (`neighborhood_id`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            try {
                $db->query($sql);
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Ambil feed postingan forum diskusi RT
     */
    public function getDiscussions(int $neighborhoodId, int $limit = 50): array
    {
        return $this->select('neighborhood_discussions.*, u.name AS author_name, u.role AS author_role, u.house_number, u.residence_status')
            ->join('users u', 'u.id = neighborhood_discussions.user_id', 'left')
            ->where('neighborhood_discussions.neighborhood_id', $neighborhoodId)
            ->orderBy('neighborhood_discussions.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Ambil detail 1 diskusi
     */
    public function getDiscussionDetail(int $id): ?array
    {
        return $this->select('neighborhood_discussions.*, u.name AS author_name, u.role AS author_role, u.house_number, u.residence_status')
            ->join('users u', 'u.id = neighborhood_discussions.user_id', 'left')
            ->where('neighborhood_discussions.id', $id)
            ->first();
    }
}
