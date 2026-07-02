<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id', 'wallet_id', 'category_id', 'type', 'amount', 'note', 'date', 'image'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // -------------------------------------------------------------------------
    // Summary: total saldo, income, expense bulan ini
    // -------------------------------------------------------------------------
    public function getMonthlySummary(int $userId, string $month): array
    {
        // $month format: 'YYYY-MM'
        $result = $this->db->query("
            SELECT
                SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END) AS total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense
            FROM transactions
            WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
        ", [$userId, $month])->getRowArray();

        $income  = (float)($result['total_income']  ?? 0);
        $expense = (float)($result['total_expense'] ?? 0);

        return [
            'income'  => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ];
    }

    // -------------------------------------------------------------------------
    // Overall balance (all time)
    // -------------------------------------------------------------------------
    public function getTotalBalance(int $userId): float
    {
        $result = $this->db->query("
            SELECT
                SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END) -
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS balance
            FROM transactions WHERE user_id = ?
        ", [$userId])->getRowArray();

        return (float)($result['balance'] ?? 0);
    }

    // -------------------------------------------------------------------------
    // Recent transactions (with category info)
    // -------------------------------------------------------------------------
    public function getRecent(int $userId, int $limit = 10, ?string $type = null, ?int $month = null, ?int $year = null): array
    {
        $builder = $this->db->table('transactions t')
            ->select('t.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color')
            ->join('categories c', 'c.id = t.category_id', 'left')
            ->where('t.user_id', $userId)
            ->orderBy('t.date', 'DESC')
            ->orderBy('t.created_at', 'DESC')
            ->limit($limit);

        if ($type) {
            $builder->where('t.type', $type);
        }
        if ($month && $year) {
            $builder->where('MONTH(t.date)', $month)
                    ->where('YEAR(t.date)', $year);
        }

        return $builder->get()->getResultArray();
    }

    // -------------------------------------------------------------------------
    // Stats: spending by category this month
    // -------------------------------------------------------------------------
    public function getCategoryStats(int $userId, string $month): array
    {
        return $this->db->query("
            SELECT
                c.name AS category, c.color, c.icon,
                SUM(t.amount) AS total,
                t.type
            FROM transactions t
            LEFT JOIN categories c ON c.id = t.category_id
            WHERE t.user_id = ? AND DATE_FORMAT(t.date, '%Y-%m') = ?
            GROUP BY t.category_id, t.type
            ORDER BY total DESC
        ", [$userId, $month])->getResultArray();
    }

    // -------------------------------------------------------------------------
    // Stats: monthly totals (last 6 months)
    // -------------------------------------------------------------------------
    public function getMonthlyTrend(int $userId, int $months = 6): array
    {
        return $this->db->query("
            SELECT
                DATE_FORMAT(date, '%Y-%m') AS month,
                DATE_FORMAT(date, '%b')    AS month_label,
                SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END) AS income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expense
            FROM transactions
            WHERE user_id = ?
              AND date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(date, '%Y-%m')
            ORDER BY month ASC
        ", [$userId, $months])->getResultArray();
    }

    // -------------------------------------------------------------------------
    // Paginated list for Activity page
    // -------------------------------------------------------------------------
    public function getActivity(int $userId, string $type = 'all', int $page = 1, int $perPage = 20, string $search = ''): array
    {
        $builder = $this->db->table('transactions t')
            ->select('t.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color')
            ->join('categories c', 'c.id = t.category_id', 'left')
            ->where('t.user_id', $userId)
            ->orderBy('t.date', 'DESC')
            ->orderBy('t.created_at', 'DESC');

        if ($type !== 'all') {
            $builder->where('t.type', $type);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('t.note', $search)
                ->orLike('c.name', $search)
            ->groupEnd();
        }

        $total  = $builder->countAllResults(false);
        $offset = ($page - 1) * $perPage;
        $data   = $builder->limit($perPage, $offset)->get()->getResultArray();

        return [
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $perPage,
            'totalPages'  => (int)ceil($total / $perPage),
        ];
    }

    // -------------------------------------------------------------------------
    // Daily running balance for a given month (for sparkline chart)
    // -------------------------------------------------------------------------
    public function getDailyBalance(int $userId, string $month): array
    {
        $monthStart = $month . '-01';

        // Sum of wallet initial_balances
        $initRow = $this->db->query("
            SELECT COALESCE(SUM(initial_balance), 0) AS init FROM wallets WHERE user_id = ?
        ", [$userId])->getRowArray();

        // Net transactions before this month
        $prevRow = $this->db->query("
            SELECT COALESCE(SUM(CASE WHEN type='income' THEN amount ELSE -amount END), 0) AS net
            FROM transactions WHERE user_id = ? AND date < ?
        ", [$userId, $monthStart])->getRowArray();

        $running = (float)($initRow['init'] ?? 0) + (float)($prevRow['net'] ?? 0);

        // Daily deltas for this month
        $rows = $this->db->query("
            SELECT date,
                SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS inc,
                SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS exp
            FROM transactions
            WHERE user_id = ? AND date >= ? AND date < DATE_ADD(?, INTERVAL 1 MONTH)
            GROUP BY date ORDER BY date ASC
        ", [$userId, $monthStart, $monthStart])->getResultArray();

        $byDate = [];
        foreach ($rows as $r) {
            $byDate[$r['date']] = (float)$r['inc'] - (float)$r['exp'];
        }

        $today       = date('Y-m-d');
        $daysInMonth = (int)date('t', strtotime($monthStart));
        $result      = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $ds = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
            if ($ds > $today) break;
            $running += $byDate[$ds] ?? 0.0;
            $result[] = ['d' => $d, 'b' => $running];
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // All transactions for a month (export)
    // -------------------------------------------------------------------------
    public function getForExport(int $userId, string $month): array
    {
        return $this->db->query("
            SELECT t.date, t.type, c.name AS category_name, t.note, t.amount
            FROM transactions t
            LEFT JOIN categories c ON c.id = t.category_id
            WHERE t.user_id = ? AND DATE_FORMAT(t.date, '%Y-%m') = ?
            ORDER BY t.date ASC, t.created_at ASC
        ", [$userId, $month])->getResultArray();
    }
}
