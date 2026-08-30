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
        $owned = $this->where('user_id', $userId)
                      ->orderBy('sort_order', 'ASC')
                      ->orderBy('id', 'ASC')
                      ->findAll();

        foreach ($owned as &$w) {
            $w['is_shared'] = false;
            $w['role'] = 'owner';
        }
        unset($w);

        // Fetch shared wallets where user is a collaborator
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRowArray();
        $userEmail = $user['email'] ?? '';

        $sharedRows = $db->table('wallet_members wm')
            ->select('w.*, wm.role, wm.owner_user_id, u.name AS owner_name')
            ->join('wallets w', 'w.id = wm.wallet_id', 'inner')
            ->join('users u', 'u.id = wm.owner_user_id', 'left')
            ->groupStart()
                ->where('wm.member_user_id', $userId)
                ->orWhere('wm.member_email', $userEmail)
            ->groupEnd()
            ->where('wm.status', 'active')
            ->get()
            ->getResultArray();

        foreach ($sharedRows as &$sw) {
            $sw['is_shared'] = true;
            $sw['is_default'] = 0; // shared wallet is never the default personal wallet
        }
        unset($sw);

        return array_merge($owned, $sharedRows);
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

        $walletIds = array_column($wallets, 'id');
        $db = \Config\Database::connect();
        
        $netByWallet = [];
        if (!empty($walletIds)) {
            $rows = $db->table('transactions')
                ->select('wallet_id, SUM(CASE WHEN type="income" THEN amount ELSE 0 END) - SUM(CASE WHEN type="expense" THEN amount ELSE 0 END) AS net')
                ->whereIn('wallet_id', $walletIds)
                ->groupBy('wallet_id')
                ->get()
                ->getResultArray();

            foreach ($rows as $r) {
                $netByWallet[(int)$r['wallet_id']] = (float)$r['net'];
            }
        }

        // Find default wallet ID to attribute legacy unlinked transactions
        $defaultWalletId = null;
        foreach ($wallets as $w) {
            if (!empty($w['is_default'])) {
                $defaultWalletId = (int)$w['id'];
                break;
            }
        }
        if (!$defaultWalletId && !empty($wallets)) {
            $defaultWalletId = (int)$wallets[0]['id'];
        }

        // Include transactions not linked to any wallet (legacy) and associate with default wallet
        $legacy = $db->query("
            SELECT
                SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) -
                SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS net
            FROM transactions WHERE user_id = ? AND wallet_id IS NULL
        ", [$userId])->getRowArray();
        $legacyNet = (float)($legacy['net'] ?? 0);

        $total = 0.0;
        foreach ($wallets as &$w) {
            $net = $netByWallet[(int)$w['id']] ?? 0.0;
            if ((int)$w['id'] === $defaultWalletId) {
                $net += $legacyNet;
            }
            $w['balance'] = (float)$w['initial_balance'] + $net;
            if (empty($w['is_shared'])) {
                $total += $w['balance'];
            }
        }
        unset($w);

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
