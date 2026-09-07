<?php

namespace App\Models;

use CodeIgniter\Model;

class NeighborhoodKasModel extends Model
{
    protected $table            = 'neighborhood_kas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'neighborhood_id',
        'created_by',
        'type',             // 'in' (pemasukan), 'out' (pengeluaran)
        'category',         // 'Iuran Warga', 'Sewa Alat', 'Donasi', 'Kerja Bakti', 'Perbaikan Fasilitas', 'Konsumsi / Rapat', 'Lainnya'
        'amount',
        'date',
        'description',
        'receipt_photo',
        'reference_type',   // 'tool_rental', 'manual', 'monthly_dues', etc.
        'reference_id',
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
                `type` ENUM('in', 'out') NOT NULL DEFAULT 'in',
                `category` VARCHAR(100) NOT NULL DEFAULT 'Iuran Warga',
                `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `date` DATE NOT NULL,
                `description` TEXT NULL,
                `receipt_photo` VARCHAR(255) NULL,
                `reference_type` VARCHAR(50) NULL DEFAULT 'manual',
                `reference_id` INT UNSIGNED NULL DEFAULT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                INDEX `idx_neighborhood_date` (`neighborhood_id`, `date`),
                INDEX `idx_type` (`type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            try {
                $db->query($sql);
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Ambil riwayat kas RT dengan informasi pencatat
     */
    public function getLedger(int $neighborhoodId, int $limit = 50): array
    {
        return $this->select('neighborhood_kas.*, u.name AS recorded_by_name, u.role AS recorded_by_role')
            ->join('users u', 'u.id = neighborhood_kas.created_by', 'left')
            ->where('neighborhood_kas.neighborhood_id', $neighborhoodId)
            ->orderBy('neighborhood_kas.date', 'DESC')
            ->orderBy('neighborhood_kas.id', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Ringkasan Total Pemasukan, Pengeluaran, Saldo Kas RT
     */
    public function getSummary(int $neighborhoodId): array
    {
        $rows = $this->select('type, SUM(amount) AS total_amount, COUNT(id) AS tx_count')
            ->where('neighborhood_id', $neighborhoodId)
            ->groupBy('type')
            ->findAll();

        $totalIn = 0.0;
        $totalOut = 0.0;
        $countIn = 0;
        $countOut = 0;

        foreach ($rows as $r) {
            if ($r['type'] === 'in') {
                $totalIn = (float)($r['total_amount'] ?? 0);
                $countIn = (int)($r['tx_count'] ?? 0);
            } elseif ($r['type'] === 'out') {
                $totalOut = (float)($r['total_amount'] ?? 0);
                $countOut = (int)($r['tx_count'] ?? 0);
            }
        }

        $balance = $totalIn - $totalOut;

        return [
            'total_in'       => $totalIn,
            'total_out'      => $totalOut,
            'balance'        => $balance,
            'count_in'       => $countIn,
            'count_out'      => $countOut,
            'total_tx'       => $countIn + $countOut,
        ];
    }
}
