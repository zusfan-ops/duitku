<?php

namespace App\Controllers;

use App\Models\WalletModel;
use App\Models\SettingModel;

class WalletController extends BaseController
{
    protected WalletModel  $walletModel;
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->walletModel  = new WalletModel();
        $this->settingModel = new SettingModel();
    }

    // GET /wallets
    public function index(): string
    {
        $userId = session()->get('user_id');
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $data   = $this->walletModel->getWithBalances($userId);

        return view('wallets/index', [
            'pageTitle' => 'Dompet & Rekening',
            'wallets'   => $data['wallets'],
            'total'     => $data['total'],
            'symbol'    => $symbol,
        ]);
    }

    // POST /wallets/store  (create or update)
    public function store()
    {
        $userId  = session()->get('user_id');
        $id      = (int)($this->request->getPost('id') ?: 0);
        $name    = trim($this->request->getPost('name') ?? '');
        $type    = $this->request->getPost('type') ?: 'cash';
        $icon    = $this->request->getPost('icon') ?: '💵';
        $color   = $this->request->getPost('color') ?: '#0AA956';
        $initial = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('initial_balance') ?? '0');

        if (!$name || !in_array($type, ['bank', 'e-wallet', 'cash', 'savings_home'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak lengkap.']);
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
                return $this->response->setJSON(['success' => false, 'message' => 'Tidak ditemukan.']);
            }
            $this->walletModel->update($id, $payload);
        } else {
            $count    = $this->walletModel->where('user_id', $userId)->countAllResults();
            $isFirst  = $count === 0;
            $payload  = array_merge($payload, [
                'user_id'    => $userId,
                'is_default' => $isFirst ? 1 : 0,
                'sort_order' => $count,
            ]);
            $id = $this->walletModel->insert($payload);
        }

        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    // POST /wallets/delete/{id}
    public function delete(int $id)
    {
        $userId = session()->get('user_id');
        $wallet = $this->walletModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$wallet) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ditemukan.']);
        }

        $count = $this->walletModel->where('user_id', $userId)->countAllResults();
        if ($count <= 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak bisa menghapus dompet satu-satunya.']);
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
        return $this->response->setJSON(['success' => true]);
    }

    // POST /wallets/default/{id}
    public function setDefault(int $id)
    {
        $userId = session()->get('user_id');
        $wallet = $this->walletModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$wallet) {
            return $this->response->setJSON(['success' => false]);
        }

        \Config\Database::connect()
            ->table('wallets')
            ->where('user_id', $userId)
            ->update(['is_default' => 0]);

        $this->walletModel->update($id, ['is_default' => 1]);
        return $this->response->setJSON(['success' => true]);
    }

    // POST /wallets/transfer
    public function transfer()
    {
        $userId  = session()->get('user_id');
        $fromId  = (int)($this->request->getPost('from_wallet_id') ?: 0);
        $toId    = (int)($this->request->getPost('to_wallet_id')   ?: 0);
        $amount  = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('amount') ?? '0');
        $note    = trim($this->request->getPost('note') ?? '');
        $date    = $this->request->getPost('date') ?: date('Y-m-d');

        if (!$fromId || !$toId || $fromId === $toId || $amount <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak valid.']);
        }

        $from = $this->walletModel->where('id', $fromId)->where('user_id', $userId)->first();
        $to   = $this->walletModel->where('id', $toId)->where('user_id', $userId)->first();
        if (!$from || !$to) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dompet tidak ditemukan.']);
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

        return $this->response->setJSON(['success' => true]);
    }

    private function ensureTransferCategory(int $userId): int
    {
        $db  = \Config\Database::connect();
        $cat = $db->table('categories')
                  ->where('user_id', $userId)
                  ->where('name', 'Transfer')
                  ->where('type', 'expense')
                  ->get()->getRowArray();

        if ($cat) return (int)$cat['id'];

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
        return (int)$db->insertID();
    }
}
