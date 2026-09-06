<?php

namespace App\Models;

use CodeIgniter\Model;

class ToolRentalModel extends Model
{
    protected $table            = 'tool_rentals';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'neighborhood_id',
        'tool_id',
        'borrower_user_id',
        'status',
        'rental_fee',
        'deposit_amount',
        'start_date',
        'due_date',
        'actual_return_date',
        'handover_token',
        'return_token',
        'handover_confirmed_by',
        'return_confirmed_by',
        'borrower_note',
        'admin_note',
        'dispute_reason',
        'fee_transaction_id',
        'deposit_transaction_id',
        'refund_transaction_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Dapatkan detail peminjaman lengkap beserta data alat dan peminjam
     */
    public function getRentalDetail(int $rentalId): ?array
    {
        return $this->select('tool_rentals.*, 
                ct.name AS tool_name, ct.photo AS tool_photo, ct.category AS tool_category, ct.condition_note AS tool_condition,
                ct.owner_user_id,
                bu.name AS borrower_name, bu.phone AS borrower_phone, bu.house_number AS borrower_house, bu.residence_status AS borrower_residence_status,
                ou.name AS tool_owner_name, ou.phone AS tool_owner_phone,
                hcu.name AS handover_admin_name, rcu.name AS return_admin_name')
            ->join('community_tools ct', 'ct.id = tool_rentals.tool_id', 'inner')
            ->join('users bu', 'bu.id = tool_rentals.borrower_user_id', 'inner')
            ->join('users ou', 'ou.id = ct.owner_user_id', 'left')
            ->join('users hcu', 'hcu.id = tool_rentals.handover_confirmed_by', 'left')
            ->join('users rcu', 'rcu.id = tool_rentals.return_confirmed_by', 'left')
            ->where('tool_rentals.id', $rentalId)
            ->first();
    }

    /**
     * Ambil daftar rental untuk user (peminjam)
     */
    public function getRentalsForBorrower(int $userId): array
    {
        return $this->select('tool_rentals.*, ct.name AS tool_name, ct.photo AS tool_photo, ct.category AS tool_category')
            ->join('community_tools ct', 'ct.id = tool_rentals.tool_id', 'inner')
            ->where('tool_rentals.borrower_user_id', $userId)
            ->orderBy('tool_rentals.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Ambil daftar rental untuk pengelola RT
     */
    public function getRentalsForNeighborhood(int $neighborhoodId, ?string $status = null): array
    {
        $builder = $this->select('tool_rentals.*, 
                ct.name AS tool_name, ct.photo AS tool_photo, 
                u.name AS borrower_name, u.house_number AS borrower_house, u.phone AS borrower_phone')
            ->join('community_tools ct', 'ct.id = tool_rentals.tool_id', 'inner')
            ->join('users u', 'u.id = tool_rentals.borrower_user_id', 'inner')
            ->where('tool_rentals.neighborhood_id', $neighborhoodId)
            ->orderBy('tool_rentals.created_at', 'DESC');

        if (!empty($status)) {
            $builder->where('tool_rentals.status', $status);
        }

        return $builder->findAll();
    }
}
