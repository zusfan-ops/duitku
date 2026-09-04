<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketplaceImageModel extends Model
{
    protected $table            = 'marketplace_images';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'listing_id',
        'image_url',
        'is_primary',
        'sort_order',
    ];

    protected $useTimestamps = false;

    public function getForListing(int $listingId): array
    {
        return $this->where('listing_id', $listingId)
                    ->orderBy('is_primary', 'DESC')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
}
