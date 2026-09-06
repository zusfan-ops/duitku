<?php

namespace App\Models;

use CodeIgniter\Model;

class NeighborhoodModel extends Model
{
    protected $table            = 'neighborhoods';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'name',
        'province',
        'city',
        'district',
        'subdistrict',
        'rw',
        'rt',
        'unique_code',
        'qr_join_token',
        'admin_user_id',
        'bank_wallet_id',
        'address_note',
        'auto_approval',
        'max_borrow_limit_domisili',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Cari RT berdasarkan kode unik (case insensitive)
     */
    public function findByUniqueCode(string $code): ?array
    {
        return $this->where('LOWER(unique_code)', strtolower(trim($code)))->first();
    }

    /**
     * Generate Kode Unik RT Otomatis jika belum ada
     * Contoh: RT04-RW02-GRIYA-2026
     */
    public static function generateUniqueCode(string $rt, string $rw, string $subdistrict): string
    {
        $cleanSub = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', substr($subdistrict, 0, 6)));
        $rtNum = str_pad(preg_replace('/[^0-9]/', '', $rt) ?: '01', 2, '0', STR_PAD_LEFT);
        $rwNum = str_pad(preg_replace('/[^0-9]/', '', $rw) ?: '01', 2, '0', STR_PAD_LEFT);
        $random = strtoupper(bin2hex(random_bytes(2)));
        return "RT{$rtNum}-RW{$rwNum}-{$cleanSub}-{$random}";
    }

    /**
     * Ambil detail RT beserta info Ketua RT & statistik ringkas
     */
    public function getWithDetails(int $neighborhoodId): ?array
    {
        $rt = $this->select('neighborhoods.*, u.name AS admin_name, u.phone AS admin_phone, u.email AS admin_email')
            ->join('users u', 'u.id = neighborhoods.admin_user_id', 'left')
            ->where('neighborhoods.id', $neighborhoodId)
            ->first();

        if (!$rt) {
            return null;
        }

        $db = \Config\Database::connect();

        // Hitung jumlah warga terverifikasi & pending
        $totalVerified = $db->table('users')
            ->where('neighborhood_id', $neighborhoodId)
            ->where('rt_verification_status', 'verified')
            ->countAllResults();

        $totalPending = $db->table('users')
            ->where('neighborhood_id', $neighborhoodId)
            ->where('rt_verification_status', 'pending')
            ->countAllResults();

        // Hitung total alat bersama
        $totalTools = $db->table('community_tools')
            ->where('neighborhood_id', $neighborhoodId)
            ->where('status !=', 'retired')
            ->countAllResults();

        // Hitung errand aktif
        $activeErrands = $db->table('errands')
            ->where('neighborhood_id', $neighborhoodId)
            ->whereIn('status', ['open', 'shopping', 'delivering'])
            ->countAllResults();

        $rt['total_verified_residents'] = $totalVerified;
        $rt['total_pending_residents']  = $totalPending;
        $rt['total_community_tools']    = $totalTools;
        $rt['active_errands_count']     = $activeErrands;

        return $rt;
    }

    /**
     * Ambil daftar warga di RT tersebut
     */
    public function getResidents(int $neighborhoodId, ?string $status = 'verified'): array
    {
        $builder = $this->db->table('users')
            ->select('id, name, username, email, phone, role, residence_status, rt_verification_status, house_number, rt_verified_at, avatar, created_at')
            ->where('neighborhood_id', $neighborhoodId)
            ->orderBy('house_number', 'ASC')
            ->orderBy('name', 'ASC');

        if ($status !== null) {
            $builder->where('rt_verification_status', $status);
        }

        return $builder->get()->getResultArray();
    }
}
