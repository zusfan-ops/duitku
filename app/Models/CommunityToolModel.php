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
