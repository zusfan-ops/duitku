<?php

namespace App\Models;

use CodeIgniter\Model;

class RecurringTransactionModel extends Model
{
    protected $table         = 'recurring_transactions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id', 'wallet_id', 'category_id', 'type',
        'amount', 'note', 'next_date', 'frequency',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get all recurring transactions for a user with category info.
     */
    public function getForUser(int $userId): array
    {
        return $this->db->query("
            SELECT r.*, 
                   c.name  AS category_name,
                   c.icon  AS category_icon,
                   c.color AS category_color,
                   w.name  AS wallet_name
            FROM recurring_transactions r
            LEFT JOIN categories c ON c.id = r.category_id
            LEFT JOIN wallets    w ON w.id = r.wallet_id
            WHERE r.user_id = ?
            ORDER BY r.next_date ASC
        ", [$userId])->getResultArray();
    }

    /**
     * Get recurring transactions that are due (next_date <= today).
     */
    public function getDue(int $userId): array
    {
        return $this->db->query("
            SELECT r.*, 
                   c.name  AS category_name,
                   c.icon  AS category_icon,
                   c.color AS category_color
            FROM recurring_transactions r
            LEFT JOIN categories c ON c.id = r.category_id
            WHERE r.user_id = ? AND r.next_date <= CURDATE()
            ORDER BY r.next_date ASC
        ", [$userId])->getResultArray();
    }

    /**
     * Process all due recurring transactions for a user.
     * Creates real transactions and advances next_date.
     *
     * @return int Number of transactions created
     */
    public function processAll(int $userId): int
    {
        $due     = $this->getDue($userId);
        $txModel = new TransactionModel();
        $count   = 0;

        foreach ($due as $r) {
            // Create the actual transaction
            $txModel->insert([
                'user_id'     => $userId,
                'wallet_id'   => $r['wallet_id'] ?? null,
                'category_id' => $r['category_id'] ?? null,
                'type'        => $r['type'],
                'amount'      => $r['amount'],
                'note'        => ($r['note'] ? $r['note'] . ' (otomatis)' : 'Transaksi berulang otomatis'),
                'date'        => date('Y-m-d'),
            ]);

            // Advance next_date based on frequency
            $nextDate = $this->advanceDate($r['next_date'], $r['frequency'] ?? 'monthly');
            $this->update($r['id'], ['next_date' => $nextDate]);
            $count++;
        }

        return $count;
    }

    /**
     * Calculate the next date based on frequency.
     */
    private function advanceDate(string $from, string $frequency): string
    {
        return match ($frequency) {
            'weekly'  => date('Y-m-d', strtotime($from . ' +1 week')),
            'yearly'  => date('Y-m-d', strtotime($from . ' +1 year')),
            default   => date('Y-m-d', strtotime($from . ' +1 month')),
        };
    }
}
