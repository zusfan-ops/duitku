<?php

namespace App\Controllers\Api;

use App\Models\DebtModel;
use App\Models\SettingModel;

class DebtController extends ApiController
{
    protected DebtModel    $debtModel;
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->debtModel    = new DebtModel();
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        $userId = $this->uid();
        $status = $this->request->getGet('status') ?: 'active';
        if (!in_array($status, ['active', 'settled', 'all'])) {
            $status = 'active';
        }

        return $this->ok([
            'debts'   => $this->debtModel->getForUser($userId, $status),
            'summary' => $this->debtModel->getSummary($userId),
            'upcoming'=> $this->debtModel->getUpcoming($userId, 7),
            'symbol'  => $this->settingModel->get($userId, 'currency_symbol', 'Rp'),
        ]);
    }

    public function store()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $type   = $json['type'] ?? '';
        $person = trim($json['person'] ?? '');
        $amount = $this->amount($json['amount'] ?? 0);
        $desc   = trim($json['description'] ?? '');
        $due    = $json['due_date'] ?? null;
        $isPast = !empty($json['is_past']);

        if (!in_array($type, ['hutang', 'piutang']) || !$person || $amount <= 0) {
            return $this->fail('Data tidak lengkap.');
        }
        if ($due && !strtotime($due)) {
            $due = null;
        }

        $id = $this->debtModel->insert([
            'user_id'     => $userId,
            'type'        => $type,
            'person'      => $person,
            'amount'      => $amount,
            'paid'        => 0,
            'description' => $desc ?: null,
            'due_date'    => $due,
            'status'      => 'active',
            'is_past'     => $isPast ? 1 : 0,
        ]);

        if (!$isPast) {
            $transType = $type === 'hutang' ? 'income' : 'expense';
            $note = $type === 'hutang'
                ? "Pinjaman dari {$person}"
                : "Dipinjamkan ke {$person}";
            $this->createDebtTransaction($userId, $transType, $amount, $note, date('Y-m-d'));
        }

        return $this->ok(['id' => $id]);
    }

    public function pay(int $id)
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $debt   = $this->debtModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$debt) {
            return $this->fail('Tidak ditemukan.');
        }

        $payAmt = $this->amount($json['pay_amount'] ?? 0);
        if ($payAmt <= 0) {
            return $this->fail('Nominal tidak valid.');
        }

        $newPaid    = min((float) $debt['paid'] + $payAmt, (float) $debt['amount']);
        $actualPay  = $newPaid - (float) $debt['paid'];
        $status     = $newPaid >= (float) $debt['amount'] ? 'settled' : 'active';

        $this->debtModel->update($id, ['paid' => $newPaid, 'status' => $status]);

        $transType = $debt['type'] === 'hutang' ? 'expense' : 'income';
        $note = $debt['type'] === 'hutang'
            ? "Bayar hutang ke {$debt['person']}"
            : "Terima piutang dari {$debt['person']}";
        $this->createDebtTransaction($userId, $transType, $actualPay, $note, date('Y-m-d'));

        return $this->ok(['settled' => $status === 'settled']);
    }

    public function settle(int $id)
    {
        $userId = $this->uid();
        $debt   = $this->debtModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$debt) {
            return $this->fail('Tidak ditemukan.');
        }

        $remaining = (float) $debt['amount'] - (float) $debt['paid'];
        $this->debtModel->update($id, ['paid' => $debt['amount'], 'status' => 'settled']);

        if ($remaining > 0) {
            $transType = $debt['type'] === 'hutang' ? 'expense' : 'income';
            $note = $debt['type'] === 'hutang'
                ? "Bayar hutang ke {$debt['person']}"
                : "Terima piutang dari {$debt['person']}";
            $this->createDebtTransaction($userId, $transType, $remaining, $note, date('Y-m-d'));
        }

        return $this->ok();
    }

    public function delete(int $id)
    {
        $userId  = $this->uid();
        $deleted = $this->debtModel->where('id', $id)->where('user_id', $userId)->delete();
        return $this->ok(['deleted' => (bool) $deleted]);
    }

    private function createDebtTransaction(int $userId, string $transType, float $amount, string $note, string $date): void
    {
        $db       = \Config\Database::connect();
        $catId    = $this->ensureDebtCategory($userId, $transType);
        $walletId = $this->defaultWalletId($userId);
        $db->table('transactions')->insert([
            'user_id'     => $userId,
            'wallet_id'   => $walletId,
            'category_id' => $catId,
            'type'        => $transType,
            'amount'      => $amount,
            'note'        => $note,
            'date'        => $date,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    private function ensureDebtCategory(int $userId, string $transType): int
    {
        $db  = \Config\Database::connect();
        $cat = $db->table('categories')
                  ->where('user_id', $userId)
                  ->where('name', 'Hutang & Piutang')
                  ->where('type', $transType)
                  ->get()->getRowArray();

        if ($cat) {
            return (int) $cat['id'];
        }

        $db->table('categories')->insert([
            'user_id'    => $userId,
            'name'       => 'Hutang & Piutang',
            'type'       => $transType,
            'icon'       => 'other',
            'color'      => '#8B5CF6',
            'is_default' => 0,
            'sort_order' => 99,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $db->insertID();
    }

    private function defaultWalletId(int $userId): ?int
    {
        $db = \Config\Database::connect();
        $w  = $db->table('wallets')->where('user_id', $userId)->where('is_default', 1)->get()->getRowArray();
        if ($w) {
            return (int) $w['id'];
        }
        $w = $db->table('wallets')->where('user_id', $userId)->orderBy('id', 'ASC')->get()->getRowArray();
        return $w ? (int) $w['id'] : null;
    }
}
