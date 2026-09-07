<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table         = 'subscriptions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id',
        'name',
        'category',
        'amount',
        'currency',
        'billing_cycle',
        'start_date',
        'next_billing_date',
        'status',
        'payment_method',
        'provider_url',
        'notes',
        'icon',
        'color',
        'notify_before_days',
        'category_id',
        'wallet_id',
        'is_waste',
        'waste_reason',
        'last_used_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Daftar langganan user dengan info kategori & wallet terkait.
     */
    public function getForUser(int $userId, string $status = ''): array
    {
        $q = $this->db->query("
            SELECT s.*,
                   c.name AS category_name, c.icon AS category_icon,
                   w.name AS wallet_name
            FROM subscriptions s
            LEFT JOIN categories c ON c.id = s.category_id
            LEFT JOIN wallets    w ON w.id = s.wallet_id
            WHERE s.user_id = ?
              " . ($status ? "AND s.status = ?" : "") . "
            ORDER BY
              FIELD(s.status, 'active','expired','paused','cancelled'),
              s.next_billing_date ASC
        ", $status ? [$userId, $status] : [$userId])->getResultArray();

        return $q;
    }

    /**
     * Langganan yang segera jatuh tempo pembayaran (dalam N hari).
     */
    public function getUpcoming(int $userId, int $days = 7): array
    {
        return $this->db->query("
            SELECT s.*, c.name AS category_name
            FROM subscriptions s
            LEFT JOIN categories c ON c.id = s.category_id
            WHERE s.user_id = ?
              AND s.status = 'active'
              AND s.next_billing_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY s.next_billing_date ASC
        ", [$userId, $days])->getResultArray();
    }

    /**
     * Ringkasan agregat langganan: total/bulan, total/tahun, count per status, total mubazir.
     * Menormalkan billing_cycle ke estimasi bulanan.
     */
    public function getSummary(int $userId): array
    {
        $rows = $this->where('user_id', $userId)->findAll();

        $totalMonthly = 0.0;
        $activeCount = 0;
        $pausedCount = 0;
        $cancelledCount = 0;
        $wasteCount = 0;
        $wasteMonthly = 0.0;

        foreach ($rows as $s) {
            $amount = (float)$s['amount'];
            $monthly = match ($s['billing_cycle'] ?? 'monthly') {
                'weekly'     => $amount * (52 / 12),
                'quarterly'  => $amount / 3,
                'yearly'     => $amount / 12,
                default      => $amount,
            };

            if ($s['is_waste']) {
                $wasteCount++;
                $wasteMonthly += $monthly;
            }

            switch ($s['status']) {
                case 'active':   $activeCount++;    $totalMonthly += $monthly; break;
                case 'paused':   $pausedCount++;    break;
                case 'cancelled':$cancelledCount++; break;
            }
        }

        return [
            'total_monthly'     => round($totalMonthly, 2),
            'total_yearly'      => round($totalMonthly * 12, 2),
            'active_count'      => $activeCount,
            'paused_count'      => $pausedCount,
            'cancelled_count'   => $cancelledCount,
            'waste_count'       => $wasteCount,
            'waste_monthly'     => round($wasteMonthly, 2),
            'total_count'       => count($rows),
        ];
    }

    /**
     * Hitung tanggal billing berikutnya dari sebuah siklus.
     */
    public static function calculateNextBilling(string $from, string $cycle): string
    {
        return match ($cycle) {
            'weekly'     => date('Y-m-d', strtotime($from . ' +1 week')),
            'quarterly'  => date('Y-m-d', strtotime($from . ' +3 months')),
            'yearly'     => date('Y-m-d', strtotime($from . ' +1 year')),
            default      => date('Y-m-d', strtotime($from . ' +1 month')),
        };
    }
}
