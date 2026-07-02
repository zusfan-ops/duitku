<?php

namespace App\Controllers;

use App\Models\DebtModel;
use App\Models\SettingModel;

class DebtController extends BaseController
{
    protected DebtModel   $debtModel;
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
        ]);

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
        $status  = $newPaid >= (float)$debt['amount'] ? 'settled' : 'active';

        $this->debtModel->update($id, ['paid' => $newPaid, 'status' => $status]);
        return $this->response->setJSON(['success' => true, 'settled' => $status === 'settled']);
    }

    // POST /hutang/settle/{id}
    public function settle(int $id)
    {
        $userId = session()->get('user_id');
        $debt   = $this->debtModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$debt) return $this->response->setJSON(['success' => false, 'message' => 'Tidak ditemukan.']);

        $this->debtModel->update($id, ['paid' => $debt['amount'], 'status' => 'settled']);
        return $this->response->setJSON(['success' => true]);
    }

    // POST /hutang/delete/{id}
    public function delete(int $id)
    {
        $userId  = session()->get('user_id');
        $deleted = $this->debtModel->where('id', $id)->where('user_id', $userId)->delete();
        return $this->response->setJSON(['success' => (bool)$deleted]);
    }
}
