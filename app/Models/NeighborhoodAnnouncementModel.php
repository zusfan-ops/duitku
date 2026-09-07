<?php

namespace App\Models;

use CodeIgniter\Model;

class NeighborhoodAnnouncementModel extends Model
{
    protected $table            = 'neighborhood_announcements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'neighborhood_id',
        'created_by',
        'title',
        'content',
        'badge',            // 'Penting', 'Info', 'Darurat', 'Kegiatan', 'Umum'
        'is_pinned',        // 0 or 1
        'attachment_photo', // image url if any
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
                `created_by` INT UNSIGNED NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `content` TEXT NOT NULL,
                `badge` VARCHAR(50) NOT NULL DEFAULT 'Info',
                `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
                `attachment_photo` VARCHAR(255) NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                INDEX `idx_neighborhood_ann` (`neighborhood_id`, `created_at`),
                INDEX `idx_pinned` (`neighborhood_id`, `is_pinned`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            try {
                $db->query($sql);
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Ambil daftar pengumuman RT terbaru
     */
    public function getAnnouncements(int $neighborhoodId, int $limit = 20): array
    {
        return $this->select('neighborhood_announcements.*, u.name AS author_name, u.role AS author_role')
            ->join('users u', 'u.id = neighborhood_announcements.created_by', 'left')
            ->where('neighborhood_announcements.neighborhood_id', $neighborhoodId)
            ->orderBy('neighborhood_announcements.is_pinned', 'DESC')
            ->orderBy('neighborhood_announcements.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
