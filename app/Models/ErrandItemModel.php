<?php

namespace App\Models;

use CodeIgniter\Model;

class ErrandItemModel extends Model
{
    protected $table            = 'errand_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'errand_id',
        'requester_user_id',
        'item_name',
        'quantity',
        'unit',
        'estimated_price',
        'actual_price',
        'service_fee',
        'status',
        'handover_token',
        'notes',
        'receipt_photo',
        'transaction_id',
        'organizer_tx_id',
        'delivered_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Dapatkan daftar barang titipan untuk suatu sesi errand
     */
    public function getItemsForErrand(int $errandId): array
    {
        return $this->select('errand_items.*, u.name AS requester_name, u.phone AS requester_phone, u.house_number AS requester_house')
            ->join('users u', 'u.id = errand_items.requester_user_id', 'inner')
            ->where('errand_items.errand_id', $errandId)
            ->orderBy('errand_items.created_at', 'ASC')
            ->findAll();
    }

    /**
     * Dapatkan daftar titipan belanja milik requester (warga)
     */
    public function getItemsForRequester(int $userId): array
    {
        return $this->select('errand_items.*, e.destination_store, e.status AS errand_status, e.cutoff_time, e.est_delivery_time,
                ou.name AS organizer_name, ou.phone AS organizer_phone')
            ->join('errands e', 'e.id = errand_items.errand_id', 'inner')
            ->join('users ou', 'ou.id = e.organizer_user_id', 'inner')
            ->where('errand_items.requester_user_id', $userId)
            ->orderBy('errand_items.created_at', 'DESC')
            ->findAll();
    }
}
