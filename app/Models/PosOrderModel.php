<?php

namespace App\Models;

use CodeIgniter\Model;

class PosOrderModel extends Model
{
    protected $table            = 'pos_orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id', 'order_number', 'total_amount', 'total_cost',
        'profit', 'payment_method', 'wallet_id', 'cash_received',
        'change_amount', 'customer_name', 'customer_phone',
        'debt_id', 'transaction_id', 'notes', 'date',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get order details with items
     */
    public function getWithItems(int $orderId, int $userId): ?array
    {
        $order = $this->where('id', $orderId)->where('user_id', $userId)->first();
        if (!$order) return null;

        $itemModel = new PosOrderItemModel();
        $order['items'] = $itemModel->where('order_id', $orderId)->findAll();
        return $order;
    }

    /**
     * Get today's summary (Omset, Modal, Laba, Total Transaksi)
     */
    public function getTodaySummary(int $userId): array
    {
        $today = date('Y-m-d');
        $row = $this->db->query("
            SELECT 
                COALESCE(SUM(total_amount), 0) AS total_sales,
                COALESCE(SUM(total_cost), 0)   AS total_cost,
                COALESCE(SUM(profit), 0)       AS total_profit,
                COUNT(id)                      AS total_orders
            FROM pos_orders
            WHERE user_id = ? AND date = ?
        ", [$userId, $today])->getRowArray();

        return [
            'total_sales'  => (float)($row['total_sales'] ?? 0),
            'total_cost'   => (float)($row['total_cost'] ?? 0),
            'total_profit' => (float)($row['total_profit'] ?? 0),
            'total_orders' => (int)($row['total_orders'] ?? 0),
        ];
    }

    /**
     * Get Monthly Report (P&L and Best Seller)
     */
    public function getMonthlyReport(int $userId, string $monthKey): array
    {
        $db = $this->db;

        // P&L summary
        $summary = $db->query("
            SELECT 
                COALESCE(SUM(total_amount), 0) AS total_sales,
                COALESCE(SUM(total_cost), 0)   AS total_cost,
                COALESCE(SUM(profit), 0)       AS total_profit,
                COUNT(id)                      AS total_orders,
                COALESCE(AVG(total_amount), 0) AS avg_order_value
            FROM pos_orders
            WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
        ", [$userId, $monthKey])->getRowArray();

        // Payment method breakdown
        $payments = $db->query("
            SELECT 
                payment_method,
                COUNT(id) AS count,
                SUM(total_amount) AS total
            FROM pos_orders
            WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
            GROUP BY payment_method
        ", [$userId, $monthKey])->getResultArray();

        // Top 5 Best Selling items
        $bestSellers = $db->query("
            SELECT 
                i.product_name,
                SUM(i.qty) AS total_qty,
                SUM(i.subtotal) AS total_revenue
            FROM pos_order_items i
            JOIN pos_orders o ON o.id = i.order_id
            WHERE o.user_id = ? AND DATE_FORMAT(o.date, '%Y-%m') = ?
            GROUP BY i.product_name
            ORDER BY total_qty DESC
            LIMIT 5
        ", [$userId, $monthKey])->getResultArray();

        // Daily trend
        $daily = $db->query("
            SELECT 
                date,
                SUM(total_amount) AS sales,
                SUM(profit) AS profit,
                COUNT(id) AS count
            FROM pos_orders
            WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
            GROUP BY date
            ORDER BY date ASC
        ", [$userId, $monthKey])->getResultArray();

        return [
            'summary'     => [
                'total_sales'     => (float)($summary['total_sales'] ?? 0),
                'total_cost'      => (float)($summary['total_cost'] ?? 0),
                'total_profit'    => (float)($summary['total_profit'] ?? 0),
                'total_orders'    => (int)($summary['total_orders'] ?? 0),
                'avg_order_value' => (float)($summary['avg_order_value'] ?? 0),
            ],
            'payments'    => $payments,
            'bestSellers' => $bestSellers,
            'daily'       => $daily,
        ];
    }

    /**
     * Get recent POS orders
     */
    public function getRecent(int $userId, int $limit = 5): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('id', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}

