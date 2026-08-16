<?php

namespace App\Models;

use CodeIgniter\Model;

class PosLoyaltyModel extends Model
{
    protected $table            = 'pos_loyalty_stamps';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id', 'customer_phone', 'customer_name',
        'stamps_count', 'total_claimed', 'last_order_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get or create customer loyalty record by phone
     */
    public function getCustomerStamps(int $userId, string $phone): ?array
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (!$phone) return null;

        return $this->where('user_id', $userId)
                    ->where('customer_phone', $phone)
                    ->first();
    }

    /**
     * Add stamp(s) to customer on completed order
     */
    public function addStamps(int $userId, string $phone, string $customerName, int $stampsToAdd = 1): array
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (!$phone) return ['success' => false];

        $record = $this->where('user_id', $userId)
                       ->where('customer_phone', $phone)
                       ->first();

        if ($record) {
            $newCount = (int)$record['stamps_count'] + $stampsToAdd;
            $this->update($record['id'], [
                'customer_name' => $customerName ?: $record['customer_name'],
                'stamps_count'  => $newCount,
                'last_order_at' => date('Y-m-d H:i:s'),
            ]);
            return ['success' => true, 'stamps_count' => $newCount];
        }

        $id = $this->insert([
            'user_id'        => $userId,
            'customer_phone' => $phone,
            'customer_name'  => $customerName,
            'stamps_count'   => $stampsToAdd,
            'total_claimed'  => 0,
            'last_order_at'  => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'stamps_count' => $stampsToAdd];
    }
}
