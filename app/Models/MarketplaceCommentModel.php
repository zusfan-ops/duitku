<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketplaceCommentModel extends Model
{
    protected $table            = 'marketplace_comments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'listing_id',
        'user_id',
        'comment',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function getForListing(int $listingId): array
    {
        $builder = $this->db->table($this->table . ' mc');
        $builder->select('
            mc.*,
            u.name AS user_name,
            u.username AS user_username,
            u.avatar AS user_avatar
        ');
        $builder->join('users u', 'u.id = mc.user_id', 'inner');
        $builder->where('mc.listing_id', $listingId);
        $builder->orderBy('mc.created_at', 'DESC');
        return $builder->get()->getResultArray();
    }
}
