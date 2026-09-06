<?php

namespace App\Models;

use CodeIgniter\Model;

class NeighborhoodVouchModel extends Model
{
    protected $table            = 'neighborhood_vouches';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'neighborhood_id',
        'target_user_id',
        'voucher_user_id',
        'status',
        'notes',
    ];

    protected $useTimestamps = false;

    /**
     * Dapatkan daftar penjamin bagi seorang warga baru
     */
    public function getVouchesForUser(int $userId): array
    {
        return $this->select('neighborhood_vouches.*, u.name AS voucher_name, u.house_number AS voucher_house_number, u.phone AS voucher_phone')
            ->join('users u', 'u.id = neighborhood_vouches.voucher_user_id', 'inner')
            ->where('neighborhood_vouches.target_user_id', $userId)
            ->findAll();
    }

    /**
     * Hitung berapa banyak warga terverifikasi yang telah menjamin user ini
     */
    public function countApprovedVouches(int $userId): int
    {
        return $this->where('target_user_id', $userId)
            ->where('status', 'approved')
            ->countAllResults();
    }
}
