<?php

namespace App\Models;

use CodeIgniter\Model;

class WalletModel extends Model
{
    protected $table            = 'wallets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'name', 'type', 'icon', 'color',
        'initial_balance', 'is_default', 'sort_order',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }

    public function getDefaultWallet(int $userId): ?array
    {
        $w = $this->where('user_id', $userId)->where('is_default', 1)->first();
        if (!$w) {
            $w = $this->where('user_id', $userId)->orderBy('id', 'ASC')->first();
        }
        return $w;
    }

    public function getDefaultWalletId(int $userId): ?int
    {
        $w = $this->getDefaultWallet($userId);
        return $w ? (int)$w['id'] : null;
    }

    /**
     * Returns wallets array with computed balance + total across all wallets.
     */
    public function getWithBalances(int $userId): array
    {
        $wallets = $this->getForUser($userId);
        if (empty($wallets)) {
            return ['wallets' => [], 'total' => 0.0];
        }

        $db   = \Config\Database::connect();
        $rows = $db->query("
            SELECT wallet_id,
                SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) -
                SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS net
            FROM transactions
            WHERE user_id = ? AND wallet_id IS NOT NULL
            GROUP BY wallet_id
        ", [$userId])->getResultArray();

        $netByWallet = [];
        foreach ($rows as $r) {
            $netByWallet[(int)$r['wallet_id']] = (float)$r['net'];
        }

        $total = 0.0;
        foreach ($wallets as &$w) {
            $w['balance'] = (float)$w['initial_balance'] + ($netByWallet[(int)$w['id']] ?? 0.0);
            $total += $w['balance'];
        }
        unset($w);

        // Also include transactions not linked to any wallet (legacy)
        $legacy = $db->query("
            SELECT
                SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) -
                SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS net
            FROM transactions WHERE user_id = ? AND wallet_id IS NULL
        ", [$userId])->getRowArray();
        $total += (float)($legacy['net'] ?? 0);

        return ['wallets' => $wallets, 'total' => $total];
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'bank'          => 'Bank',
            'e-wallet'      => 'E-Wallet',
            'savings_home'  => 'Tabungan',
            default         => 'Tunai',
        };
    }
}
