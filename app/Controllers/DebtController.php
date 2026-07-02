<?php

namespace App\Controllers;

use App\Models\DebtModel;
use App\Models\SettingModel;

class DebtController extends BaseController
{
    protected DebtModel    $debtModel;
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->debtModel    = new DebtModel();
        $this->settingModel = new SettingModel();
    }

    // GET /hutang
    public function index(): string
    {
        $userId  = session()->get('user_id');
        $symbol  = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $status  = $this->request->getGet('status') ?: 'active';
        if (!in_array($status, ['active', 'settled', 'all'])) $status = 'active';

        $debts   = $this->debtModel->getForUser($userId, $status);
        $summary = $this->debtModel->getSummary($userId);
        $upcoming= $this->debtModel->getUpcoming($userId, 7);

        return view('debt/index', [
            'pageTitle' => 'Hutang & Piutang',
            'debts'     => $debts,
            'summary'   => $summary,
            'upcoming'  => $upcoming,
            'symbol'    => $symbol,
            'status'    => $status,
        ]);
    }

    // POST /hutang/store
    public function store()
    {
        $userId = session()->get('user_id');
        $type   = $this->request->getPost('type');
        $person = trim($this->request->getPost('person') ?? '');
        $amount = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('amount') ?? '0');
        $desc   = trim($this->request->getPost('description') ?? '');
        $due    = $this->request->getPost('due_date') ?: null;
        $isPast = (bool)(int)($this->request->getPost('is_past') ?? 0);

        if (!in_array($type, ['hutang', 'piutang']) || !$person || $amount <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak lengkap.']);
        }
        if ($due && !strtotime($due)) $due = null;

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

        // Only create a transaction if this is a NEW (non-historical) debt
        if (!$isPast) {
            // Hutang = saya terima uang (income), Piutang = saya kasih uang (expense)
            $transType = $type === 'hutang' ? 'income' : 'expense';
            $note = $type === 'hutang'
                ? "Pinjaman dari {$person}"
                : "Dipinjamkan ke {$person}";
            $this->createDebtTransaction($userId, $transType, $amount, $note, date('Y-m-d'));
        }

        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    // POST /hutang/pay/{id}  — partial payment
    public function pay(int $id)
    {
        $userId = session()->get('user_id');
        $debt   = $this->debtModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$debt) return $this->response->setJSON(['success' => false, 'message' => 'Tidak ditemukan.']);

        $payAmt = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('pay_amount') ?? '0');
        if ($payAmt <= 0) return $this->response->setJSON(['success' => false, 'message' => 'Nominal tidak valid.']);

        $newPaid = min((float)$debt['paid'] + $payAmt, (float)$debt['amount']);
        $actualPay = $newPaid - (float)$debt['paid'];
        $status  = $newPaid >= (float)$debt['amount'] ? 'settled' : 'active';

        $this->debtModel->update($id, ['paid' => $newPaid, 'status' => $status]);

        // Payment always affects balance regardless of is_past
        // Hutang dibayar = saldo turun (expense), Piutang diterima = saldo naik (income)
        $transType = $debt['type'] === 'hutang' ? 'expense' : 'income';
        $note = $debt['type'] === 'hutang'
            ? "Bayar hutang ke {$debt['person']}"
            : "Terima piutang dari {$debt['person']}";
        $this->createDebtTransaction($userId, $transType, $actualPay, $note, date('Y-m-d'));

        return $this->response->setJSON(['success' => true, 'settled' => $status === 'settled']);
    }

    // POST /hutang/settle/{id}
    public function settle(int $id)
    {
        $userId = session()->get('user_id');
        $debt   = $this->debtModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$debt) return $this->response->setJSON(['success' => false, 'message' => 'Tidak ditemukan.']);

        $remaining = (float)$debt['amount'] - (float)$debt['paid'];
        $this->debtModel->update($id, ['paid' => $debt['amount'], 'status' => 'settled']);

        // Create transaction for the remaining unpaid amount (always affects balance)
        if ($remaining > 0) {
            $transType = $debt['type'] === 'hutang' ? 'expense' : 'income';
            $note = $debt['type'] === 'hutang'
                ? "Bayar hutang ke {$debt['person']}"
                : "Terima piutang dari {$debt['person']}";
            $this->createDebtTransaction($userId, $transType, $remaining, $note, date('Y-m-d'));
        }

        return $this->response->setJSON(['success' => true]);
    }

    // POST /hutang/delete/{id}
    public function delete(int $id)
    {
        $userId  = session()->get('user_id');
        $deleted = $this->debtModel->where('id', $id)->where('user_id', $userId)->delete();
        return $this->response->setJSON(['success' => (bool)$deleted]);
    }

    // ── Helpers ───────────────────────────────────────────────────────

    private function ensureDebtCategory(int $userId, string $transType): int
    {
        $db  = \Config\Database::connect();
        $cat = $db->table('categories')
                  ->where('user_id', $userId)
                  ->where('name', 'Hutang & Piutang')
                  ->where('type', $transType)
                  ->get()->getRowArray();

        if ($cat) return (int)$cat['id'];

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
        return (int)$db->insertID();
    }

    private function createDebtTransaction(int $userId, string $transType, float $amount, string $note, string $date): void
    {
        $catId = $this->ensureDebtCategory($userId, $transType);
        $db    = \Config\Database::connect();
        $db->table('transactions')->insert([
            'user_id'     => $userId,
            'category_id' => $catId,
            'type'        => $transType,
            'amount'      => $amount,
            'note'        => $note,
            'date'        => $date,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
