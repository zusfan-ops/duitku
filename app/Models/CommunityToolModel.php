<?php

namespace App\Models;

use CodeIgniter\Model;

class CommunityToolModel extends Model
{
    protected $table            = 'community_tools';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'neighborhood_id',
        'owner_user_id',
        'name',
        'category',
        'description',
        'photo',
        'status',
        'rental_fee',
        'deposit_amount',
        'max_rent_days',
        'condition_note',
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
                `owner_user_id` INT UNSIGNED NULL DEFAULT NULL,
                `name` VARCHAR(150) NOT NULL,
                `category` VARCHAR(100) NOT NULL DEFAULT 'Pertukangan',
                `description` TEXT NULL,
                `photo` VARCHAR(255) NULL,
                `status` ENUM('available', 'rented', 'maintenance', 'retired') NOT NULL DEFAULT 'available',
                `rental_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `deposit_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `max_rent_days` INT NOT NULL DEFAULT 3,
                `condition_note` VARCHAR(255) NULL DEFAULT 'Baik & Siap Pakai',
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                INDEX `idx_neighborhood_status` (`neighborhood_id`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            try {
                $db->query($sql);
            } catch (\Throwable $e) {}
        }
    }

    public static function getCategories(): array
    {
        return [
            'Pertukangan'    => '🪚 Pertukangan & Bangunan',
            'Kebersihan'     => '🧹 Kebersihan Lingkungan',
            'Pertamanan'     => '🌱 Pertamanan & Pemotong Rumput',
            'Acara'          => '🎪 Tenda & Perlengkapan Acara',
            'Otomotif'       => '🚗 Perkakas Otomotif / Cuci Mobil',
            'Elektronik'     => '⚡ Elektronik & Kabel Genset',
            'Medis / Darurat'=> '🩺 Medis / Tabung Oksigen / Darurat',
            'Lainnya'        => '📦 Lainnya',
        ];
    }

    /**
     * Ambil daftar alat di RT dengan filter kategori, status, pencarian
     */
    public function getTools(int $neighborhoodId, array $filters = []): array
    {
        $builder = $this->select('community_tools.*, u.name AS owner_name, u.phone AS owner_phone')
            ->join('users u', 'u.id = community_tools.owner_user_id', 'left')
            ->where('community_tools.neighborhood_id', $neighborhoodId)
            ->where('community_tools.status !=', 'retired')
            ->orderBy('community_tools.created_at', 'DESC');

        if (!empty($filters['status'])) {
            $builder->where('community_tools.status', $filters['status']);
        }
        if (!empty($filters['category']) && $filters['category'] !== 'Semua') {
            $builder->where('community_tools.category', $filters['category']);
        }
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $builder->groupStart()
                ->like('community_tools.name', $s)
                ->orLike('community_tools.description', $s)
                ->orLike('community_tools.condition_note', $s)
            ->groupEnd();
        }

        return $builder->findAll();
    }
}
