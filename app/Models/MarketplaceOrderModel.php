<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketplaceOrderModel extends Model
{
    protected $table            = 'marketplace_orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'listing_id',
        'buyer_id',
        'seller_id',
        'order_type', // 'buy', 'rent'
        'price',
        'notes',
        'status',     // 'pending', 'contacted', 'completed', 'cancelled'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getOrdersForSeller(int $sellerId): array
    {
        $builder = $this->db->table($this->table . ' mo');
        $builder->select('
            mo.*,
            l.title AS listing_title,
            l.type AS listing_type,
            (SELECT image_url FROM marketplace_images mi WHERE mi.listing_id = l.id ORDER BY mi.is_primary DESC, mi.id ASC LIMIT 1) AS listing_image,
            u.name AS buyer_name,
            u.phone AS buyer_phone,
            u.email AS buyer_email
        ', false);
        $builder->join('marketplace_listings l', 'l.id = mo.listing_id', 'inner');
        $builder->join('users u', 'u.id = mo.buyer_id', 'inner');
        $builder->where('mo.seller_id', $sellerId);
        $builder->orderBy('mo.created_at', 'DESC');
        return $builder->get()->getResultArray();
    }

    public function getOrdersForBuyer(int $buyerId): array
    {
        $builder = $this->db->table($this->table . ' mo');
        $builder->select('
            mo.*,
            l.title AS listing_title,
            l.type AS listing_type,
            (SELECT image_url FROM marketplace_images mi WHERE mi.listing_id = l.id ORDER BY mi.is_primary DESC, mi.id ASC LIMIT 1) AS listing_image,
            u.name AS seller_name,
            u.phone AS seller_phone
        ', false);
        $builder->join('marketplace_listings l', 'l.id = mo.listing_id', 'inner');
        $builder->join('users u', 'u.id = mo.seller_id', 'inner');
        $builder->where('mo.buyer_id', $buyerId);
        $builder->orderBy('mo.created_at', 'DESC');
        return $builder->get()->getResultArray();
    }
}
