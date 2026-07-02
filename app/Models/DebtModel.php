<?php

namespace App\Models;

use CodeIgniter\Model;

class DebtModel extends Model
{
    protected $table      = 'debts';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'type', 'person', 'amount', 'paid',
        'description', 'due_date', 'status', 'is_past',
    ];
    protected $useTimestamps = true;

    public function getForUser(int $userId, string $status = 'active'): array
    {
        $q = $this->where('user_id', $userId);
        if ($status !== 'all') {
            $q->where('status', $status);
        }
        return $q->orderBy('due_date', 'ASC')->orderBy('created_at', 'DESC')->findAll();
    }

    public function getSummary(int $userId): array
    {
        $db  = \Config\Database::connect();
        $row = $db->query("
            SELECT
                SUM(CASE WHEN type='hutang'  AND status='active' THEN amount - paid ELSE 0 END) AS total_hutang,
                SUM(CASE WHEN type='piutang' AND status='active' THEN amount - paid ELSE 0 END) AS total_piutang,
                COUNT(CASE WHEN status='active' THEN 1 END) AS active_count
            FROM debts WHERE user_id = ?
        ", [$userId])->getRowArray();

        return [
            'total_hutang'  => (float)($row['total_hutang']  ?? 0),
            'total_piutang' => (float)($row['total_piutang'] ?? 0),
            'active_count'  => (int)($row['active_count']    ?? 0),
        ];
    }

    public function getUpcoming(int $userId, int $days = 7): array
    {
        return $this->where('user_id', $userId)
                    ->where('status', 'active')
                    ->where('due_date IS NOT NULL')
                    ->where('due_date <=', date('Y-m-d', strtotime("+{$days} days")))
                    ->where('due_date >=', date('Y-m-d'))
                    ->orderBy('due_date', 'ASC')
                    ->findAll();
    }
}
