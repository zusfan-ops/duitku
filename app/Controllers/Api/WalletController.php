<?php

namespace App\Controllers\Api;

use App\Models\SettingModel;
use App\Models\WalletModel;

class WalletController extends ApiController
{
    protected WalletModel  $walletModel;
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->walletModel  = new WalletModel();
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        $userId = $this->uid();
        $data   = $this->walletModel->getWithBalances($userId);

        return $this->ok([
            'wallets' => $data['wallets'],
            'total'   => $data['total'],
            'symbol'  => $this->settingModel->get($userId, 'currency_symbol', 'Rp'),
        ]);
    }

    public function store()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $id     = (int) ($json['id'] ?? 0);
        $name   = trim($json['name'] ?? '');
        $type   = $json['type'] ?? 'cash';
        $icon   = trim($json['icon'] ?? '') ?: '💵';
        $color  = trim($json['color'] ?? '') ?: '#0AA956';
        $initial = $this->amount($json['initial_balance'] ?? 0);

        if (!$name || !in_array($type, ['bank', 'e-wallet', 'cash', 'savings_home'])) {
            return $this->fail('Data tidak lengkap.');
        }

        $payload = [
            'name'            => $name,
            'type'            => $type,
            'icon'            => $icon,
            'color'           => $color,
            'initial_balance' => $initial,
        ];

        if ($id > 0) {
            $wallet = $this->walletModel->where('id', $id)->where('user_id', $userId)->first();
            if (!$wallet) {
                return $this->fail('Tidak ditemukan.');
            }
            $this->walletModel->update($id, $payload);
        } else {
            $count   = $this->walletModel->where('user_id', $userId)->countAllResults();
            $payload = array_merge($payload, [
                'user_id'    => $userId,
                'is_default' => $count === 0 ? 1 : 0,
                'sort_order' => $count,
            ]);
            $id = $this->walletModel->insert($payload);
        }

        return $this->ok(['id' => $id]);
    }

    public function delete(int $id)
    {
        $userId = $this->uid();
        $wallet = $this->walletModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$wallet) {
            return $this->fail('Tidak ditemukan.');
        }

        $count = $this->walletModel->where('user_id', $userId)->countAllResults();
        if ($count <= 1) {
            return $this->fail('Tidak bisa menghapus dompet satu-satunya.');
        }

        if ($wallet['is_default']) {
            $other = $this->walletModel
                         ->where('user_id', $userId)
                         ->where('id !=', $id)
                         ->orderBy('id', 'ASC')
                         ->first();
            if ($other) {
                $this->walletModel->update($other['id'], ['is_default' => 1]);
            }
        }

        $this->walletModel->delete($id);
        return $this->ok();
    }

    public function setDefault(int $id)
    {
        $userId = $this->uid();
        $wallet = $this->walletModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$wallet) {
            return $this->fail('Tidak ditemukan.');
        }

        \Config\Database::connect()
            ->table('wallets')
            ->where('user_id', $userId)
            ->update(['is_default' => 0]);

        $this->walletModel->update($id, ['is_default' => 1]);
        return $this->ok();
    }

    public function transfer()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $fromId = (int) ($json['from_wallet_id'] ?? 0);
        $toId   = (int) ($json['to_wallet_id'] ?? 0);
        $amount = $this->amount($json['amount'] ?? 0);
        $note   = trim($json['note'] ?? '');
        $date   = $json['date'] ?? date('Y-m-d');

        if (!$fromId || !$toId || $fromId === $toId || $amount <= 0) {
            return $this->fail('Data tidak valid.');
        }

        $from = $this->walletModel->where('id', $fromId)->where('user_id', $userId)->first();
        $to   = $this->walletModel->where('id', $toId)->where('user_id', $userId)->first();
        if (!$from || !$to) {
            return $this->fail('Dompet tidak ditemukan.');
        }

        $catId    = $this->ensureTransferCategory($userId);
        $db       = \Config\Database::connect();
        $noteFrom = $note ?: "Transfer ke {$to['name']}";
        $noteTo   = $note ?: "Transfer dari {$from['name']}";
        $now      = date('Y-m-d H:i:s');

        $db->table('transactions')->insert([
            'user_id'     => $userId,
            'wallet_id'   => $fromId,
            'category_id' => $catId,
            'type'        => 'expense',
            'amount'      => $amount,
            'note'        => $noteFrom,
            'date'        => $date,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
        $db->table('transactions')->insert([
            'user_id'     => $userId,
            'wallet_id'   => $toId,
            'category_id' => $catId,
            'type'        => 'income',
            'amount'      => $amount,
            'note'        => $noteTo,
            'date'        => $date,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        return $this->ok();
    }

    private function ensureTransferCategory(int $userId): int
    {
        $db  = \Config\Database::connect();
        $cat = $db->table('categories')
                  ->where('user_id', $userId)
                  ->where('name', 'Transfer')
                  ->where('type', 'expense')
                  ->get()->getRowArray();

        if ($cat) {
            return (int) $cat['id'];
        }

        $db->table('categories')->insert([
            'user_id'    => $userId,
            'name'       => 'Transfer',
            'type'       => 'expense',
            'icon'       => 'other',
            'color'      => '#6366F1',
            'is_default' => 0,
            'sort_order' => 98,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $db->insertID();
    }
}
