<?php

namespace App\Models;

use CodeIgniter\Model;

class TvChannelModel extends Model
{
    protected $table            = 'tv_channels';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'name',
        'category',
        'stream_url',
        'logo_url',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil daftar channel aktif, opsi filter kategori
     */
    public function getActiveChannels(?string $category = null): array
    {
        $builder = $this->where('is_active', 1);
        if ($category && strtolower($category) !== 'semua') {
            $builder->where('category', $category);
        }
        return $builder->orderBy('sort_order', 'ASC')
                       ->orderBy('name', 'ASC')
                       ->findAll();
    }

    /**
     * Ambil daftar unik semua kategori
     */
    public function getCategories(): array
    {
        $res = $this->select('category')
                    ->where('is_active', 1)
                    ->where('category IS NOT NULL', null, false)
                    ->groupBy('category')
                    ->orderBy('category', 'ASC')
                    ->findAll();

        return array_values(array_filter(array_column($res, 'category')));
    }
}
