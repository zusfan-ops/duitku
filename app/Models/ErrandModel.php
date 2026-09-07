<?php

namespace App\Models;

use CodeIgniter\Model;

class ErrandModel extends Model
{
    protected $table            = 'errands';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'neighborhood_id',
        'organizer_user_id',
        'destination_store',
        'description',
        'cutoff_time',
        'est_delivery_time',
        'max_requesters',
        'status',
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
                `organizer_user_id` INT UNSIGNED NOT NULL,
                `destination_store` VARCHAR(150) NOT NULL,
                `description` TEXT NULL,
                `cutoff_time` DATETIME NOT NULL,
                `est_delivery_time` DATETIME NULL,
                `max_requesters` INT NOT NULL DEFAULT 5,
                `status` ENUM('open', 'shopping', 'delivering', 'completed', 'cancelled') NOT NULL DEFAULT 'open',
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                INDEX `idx_neighborhood_status` (`neighborhood_id`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            try {
                $db->query($sql);
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Dapatkan daftar sesi titip belanja di RT
     */
    public function getErrands(int $neighborhoodId, array $filters = []): array
    {
        $builder = $this->select('errands.*, u.name AS organizer_name, u.phone AS organizer_phone, u.house_number AS organizer_house')
            ->join('users u', 'u.id = errands.organizer_user_id', 'inner')
            ->where('errands.neighborhood_id', $neighborhoodId)
            ->orderBy('errands.cutoff_time', 'ASC');

        if (!empty($filters['status'])) {
            $builder->where('errands.status', $filters['status']);
        }

        $list = $builder->findAll();

        $db = \Config\Database::connect();
        foreach ($list as &$item) {
            // Hitung jumlah item titipan & total pemesan
            $itemCount = $db->table('errand_items')
                ->where('errand_id', $item['id'])
                ->where('status !=', 'cancelled')
                ->countAllResults();

            $uniqueRequesters = $db->table('errand_items')
                ->select('COUNT(DISTINCT requester_user_id) AS total_requesters')
                ->where('errand_id', $item['id'])
                ->where('status !=', 'cancelled')
                ->get()
                ->getRowArray();

            $item['total_items'] = $itemCount;
            $item['total_requesters'] = (int)($uniqueRequesters['total_requesters'] ?? 0);
        }
        unset($item);

        return $list;
    }

    /**
     * Dapatkan detail errand beserta semua item titipan warga
     */
    public function getErrandWithItems(int $errandId): ?array
    {
        $errand = $this->select('errands.*, u.name AS organizer_name, u.phone AS organizer_phone, u.house_number AS organizer_house')
            ->join('users u', 'u.id = errands.organizer_user_id', 'inner')
            ->where('errands.id', $errandId)
            ->first();

        if (!$errand) {
            return null;
        }

        $itemModel = new ErrandItemModel();
        $errand['items'] = $itemModel->getItemsForErrand($errandId);

        return $errand;
    }
}
