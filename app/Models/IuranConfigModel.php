<?php

namespace App\Models;

use CodeIgniter\Model;

class IuranConfigModel extends Model
{
    protected $table         = 'neighborhood_iuran_config';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'neighborhood_id',
        'period_type',
        'amount',
        'description',
        'is_active',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil konfigurasi iuran aktif untuk sebuah neighborhood.
     */
    public function getActiveConfig(int $neighborhoodId): ?array
    {
        return $this->where('neighborhood_id', $neighborhoodId)
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Tandai semua konfigurasi neighborhood menjadi non-aktif,
     * lalu masukkan konfigurasi baru.
     */
    public function setActive(int $neighborhoodId, array $data): int
    {
        $this->where('neighborhood_id', $neighborhoodId)->update(null, ['is_active' => 0]);

        return $this->insert([
            'neighborhood_id' => $neighborhoodId,
            'period_type'     => $data['period_type'] ?? 'monthly',
            'amount'          => $data['amount'] ?? 0,
            'description'     => $data['description'] ?? 'Iuran Warga Bulanan',
            'is_active'       => 1,
        ]);
    }

    public function getHistory(int $neighborhoodId, int $limit = 20): array
    {
        return $this->where('neighborhood_id', $neighborhoodId)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
