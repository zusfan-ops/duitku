<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketplaceChatModel extends Model
{
    protected $table            = 'marketplace_chats';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'listing_id',
        'buyer_id',
        'seller_id',
        'sender_id',
        'message',
        'is_read',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil riwayat chat untuk percakapan listing_id & buyer_id tertentu
     */
    public function getMessages(int $listingId, int $buyerId, int $afterId = 0): array
    {
        $builder = $this->db->table($this->table . ' mc');
        $builder->select('
            mc.*,
            u.name AS sender_name,
            u.username AS sender_username,
            u.avatar AS sender_avatar
        ');
        $builder->join('users u', 'u.id = mc.sender_id', 'inner');
        $builder->where('mc.listing_id', $listingId);
        $builder->where('mc.buyer_id', $buyerId);

        if ($afterId > 0) {
            $builder->where('mc.id >', $afterId);
        }

        $builder->orderBy('mc.id', 'ASC');
        return $builder->get()->getResultArray();
    }

    /**
     * Tandai pesan sebagai telah dibaca oleh penerima
     */
    public function markAsRead(int $listingId, int $buyerId, int $recipientId): bool
    {
        return $this->db->table($this->table)
            ->where('listing_id', $listingId)
            ->where('buyer_id', $buyerId)
            ->where('sender_id !=', $recipientId)
            ->where('is_read', 0)
            ->set(['is_read' => 1])
            ->update();
    }

    /**
     * Ambil daftar percakapan aktif untuk pengguna (sebagai penjual atau pembeli)
     */
    public function getConversationsForUser(int $userId): array
    {
        $db = $this->db;
        $sql = "
            SELECT 
                mc.listing_id,
                mc.buyer_id,
                mc.seller_id,
                l.title AS listing_title,
                l.price AS listing_price,
                l.status AS listing_status,
                l.type AS listing_type,
                (SELECT image_url FROM marketplace_images mi WHERE mi.listing_id = l.id ORDER BY mi.is_primary DESC, mi.id ASC LIMIT 1) AS listing_image,
                ub.name AS buyer_name,
                ub.phone AS buyer_phone,
                us.name AS seller_name,
                us.phone AS seller_phone,
                last_m.message AS last_message,
                last_m.sender_id AS last_sender_id,
                last_m.created_at AS last_message_time,
                (
                    SELECT COUNT(*) 
                    FROM marketplace_chats uc 
                    WHERE uc.listing_id = mc.listing_id 
                      AND uc.buyer_id = mc.buyer_id 
                      AND uc.sender_id != ? 
                      AND uc.is_read = 0
                ) AS unread_count
            FROM (
                SELECT listing_id, buyer_id, seller_id, MAX(id) AS max_id
                FROM marketplace_chats
                WHERE buyer_id = ? OR seller_id = ?
                GROUP BY listing_id, buyer_id, seller_id
            ) mc
            JOIN marketplace_chats last_m ON last_m.id = mc.max_id
            JOIN marketplace_listings l ON l.id = mc.listing_id
            JOIN users ub ON ub.id = mc.buyer_id
            JOIN users us ON us.id = mc.seller_id
            ORDER BY last_m.id DESC
        ";

        return $db->query($sql, [$userId, $userId, $userId])->getResultArray();
    }
}
