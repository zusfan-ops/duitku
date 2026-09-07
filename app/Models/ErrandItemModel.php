<?php

namespace App\Models;

use CodeIgniter\Model;

class ErrandItemModel extends Model
{
    protected $table            = 'errand_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'errand_id',
        'requester_user_id',
        'item_name',
        'quantity',
        'unit',
        'estimated_price',
        'actual_price',
        'service_fee',
        'status',
        'handover_token',
        'notes',
        'receipt_photo',
        'transaction_id',
        'organizer_tx_id',
        'delivered_at',
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
                `errand_id` INT UNSIGNED NOT NULL,
                `requester_user_id` INT UNSIGNED NOT NULL,
                `item_name` VARCHAR(150) NOT NULL,
                `quantity` INT NOT NULL DEFAULT 1,
                `unit` VARCHAR(50) NOT NULL DEFAULT 'pcs',
                `estimated_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `actual_price` DECIMAL(12,2) NULL,
                `service_fee` DECIMAL(12,2) NOT NULL DEFAULT 2000.00,
                `status` ENUM('requested', 'accepted', 'purchased', 'delivering', 'delivered', 'cancelled') NOT NULL DEFAULT 'requested',
                `handover_token` VARCHAR(32) NULL,
                `notes` TEXT NULL,
                `receipt_photo` VARCHAR(255) NULL,
                `transaction_id` INT UNSIGNED NULL,
                `organizer_tx_id` INT UNSIGNED NULL,
                `delivered_at` DATETIME NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                INDEX `idx_errand_status` (`errand_id`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            try {
                $db->query($sql);
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Dapatkan daftar barang titipan untuk suatu sesi errand
     */
    public function getItemsForErrand(int $errandId): array
    {
        return $this->select('errand_items.*, u.name AS requester_name, u.phone AS requester_phone, u.house_number AS requester_house')
            ->join('users u', 'u.id = errand_items.requester_user_id', 'inner')
            ->where('errand_items.errand_id', $errandId)
            ->orderBy('errand_items.created_at', 'ASC')
            ->findAll();
    }

    /**
     * Dapatkan daftar titipan belanja milik requester (warga)
     */
    public function getItemsForRequester(int $userId): array
    {
        return $this->select('errand_items.*, e.destination_store, e.status AS errand_status, e.cutoff_time, e.est_delivery_time,
                ou.name AS organizer_name, ou.phone AS organizer_phone')
            ->join('errands e', 'e.id = errand_items.errand_id', 'inner')
            ->join('users ou', 'ou.id = e.organizer_user_id', 'inner')
            ->where('errand_items.requester_user_id', $userId)
            ->orderBy('errand_items.created_at', 'DESC')
            ->findAll();
    }
}
