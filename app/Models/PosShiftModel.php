<?php

namespace App\Models;

use CodeIgniter\Model;

class PosShiftModel extends Model
{
    protected $table            = 'pos_shifts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id', 'cashier_name', 'starting_cash', 'expected_cash',
        'actual_cash', 'difference', 'total_sales', 'total_transactions',
        'status', 'notes', 'opened_at', 'closed_at'
    ];
    protected $useTimestamps    = true;

    /**
     * Get active (open) shift for a user
     */
    public function getActiveShift(int $userId): ?array
    {
        return $this->where('user_id', $userId)
                    ->where('status', 'open')
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    /**
     * Open a new shift
     */
    public function openShift(int $userId, string $cashierName, float $startingCash, ?string $notes = null): int
    {
        // Close any previously open shift first to prevent duplicates
        $this->where('user_id', $userId)
             ->where('status', 'open')
             ->set(['status' => 'closed', 'closed_at' => date('Y-m-d H:i:s')])
             ->update();

        return (int) $this->insert([
            'user_id'            => $userId,
            'cashier_name'       => $cashierName ?: 'Kasir',
            'starting_cash'      => $startingCash,
            'expected_cash'      => $startingCash,
            'actual_cash'        => null,
            'difference'         => 0.00,
            'total_sales'        => 0.00,
            'total_transactions' => 0,
            'status'             => 'open',
            'notes'              => $notes,
            'opened_at'          => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Close a shift and calculate cash drawer reconciliation
     */
    public function closeShift(int $shiftId, int $userId, float $actualCash, ?string $notes = null): bool
    {
        $shift = $this->where('id', $shiftId)->where('user_id', $userId)->first();
        if (!$shift || $shift['status'] === 'closed') return false;

        $db = \Config\Database::connect();
        
        // Calculate total cash transactions during this shift's opened_at
        $salesRow = $db->query("
            SELECT 
                COUNT(*) AS total_trx,
                COALESCE(SUM(total_amount), 0) AS total_sales,
                COALESCE(SUM(CASE WHEN payment_method = 'cash' OR payment_method = 'cod' THEN total_amount ELSE 0 END), 0) AS total_cash
            FROM pos_orders
            WHERE user_id = ? 
              AND status = 'paid'
              AND created_at >= ?
        ", [$userId, $shift['opened_at']])->getRowArray();

        $totalSales = (float)($salesRow['total_sales'] ?? 0);
        $totalCashSales = (float)($salesRow['total_cash'] ?? 0);
        $totalTrx = (int)($salesRow['total_trx'] ?? 0);

        $expectedCash = (float)$shift['starting_cash'] + $totalCashSales;
        $diff = $actualCash - $expectedCash;

        return $this->update($shiftId, [
            'expected_cash'      => $expectedCash,
            'actual_cash'        => $actualCash,
            'difference'         => $diff,
            'total_sales'        => $totalSales,
            'total_transactions' => $totalTrx,
            'status'             => 'closed',
            'notes'              => $notes,
            'closed_at'          => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get shift history
     */
    public function getShiftHistory(int $userId, int $limit = 20): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('id', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}
