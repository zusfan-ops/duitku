<?php

namespace App\Models;

use CodeIgniter\Model;

class NeighborhoodActivityModel extends Model
{
    protected $table            = 'neighborhood_activities';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'neighborhood_id',
        'created_by',
        'title',
        'category',         // 'Kerja Bakti', 'Rapat Warga', 'Posyandu', 'Ronda Malam', 'Perayaan / PHBN', 'Lainnya'
        'event_date',
        'event_time',
        'location',
        'description',
        'status',           // 'upcoming', 'ongoing', 'completed', 'cancelled'
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
                `title` VARCHAR(200) NOT NULL,
                `category` VARCHAR(100) NOT NULL DEFAULT 'Kerja Bakti',
                `event_date` DATE NOT NULL,
                `event_time` VARCHAR(20) NULL DEFAULT '08:00',
                `location` VARCHAR(255) NULL,
                `description` TEXT NULL,
                `status` ENUM('upcoming', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'upcoming',
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                INDEX `idx_neighborhood_event` (`neighborhood_id`, `event_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            try {
                $db->query($sql);
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Ambil agenda kegiatan RT mendatang
     */
    public function getUpcomingActivities(int $neighborhoodId, int $limit = 10): array
    {
        return $this->select('neighborhood_activities.*, u.name AS creator_name')
            ->join('users u', 'u.id = neighborhood_activities.created_by', 'left')
            ->where('neighborhood_activities.neighborhood_id', $neighborhoodId)
            ->where('neighborhood_activities.status !=', 'cancelled')
            ->where('neighborhood_activities.event_date >=', date('Y-m-d'))
            ->orderBy('neighborhood_activities.event_date', 'ASC')
            ->orderBy('neighborhood_activities.event_time', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Ambil semua kegiatan RT
     */
    public function getAllActivities(int $neighborhoodId, int $limit = 30): array
    {
        return $this->select('neighborhood_activities.*, u.name AS creator_name')
            ->join('users u', 'u.id = neighborhood_activities.created_by', 'left')
            ->where('neighborhood_activities.neighborhood_id', $neighborhoodId)
            ->orderBy('neighborhood_activities.event_date', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
