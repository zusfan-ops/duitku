<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\RecurringTransactionModel;
use App\Models\SettingModel;
use App\Models\WalletModel;

class RecurringController extends BaseController
{
    protected RecurringTransactionModel $recurringModel;
    protected CategoryModel             $catModel;
    protected WalletModel               $walletModel;
    protected SettingModel              $settingModel;

    public function __construct()
    {
        $this->recurringModel = new RecurringTransactionModel();
        $this->catModel       = new CategoryModel();
        $this->walletModel    = new WalletModel();
        $this->settingModel   = new SettingModel();
    }

    // GET /recurring
    public function index(): string
    {
        $userId    = session()->get('user_id');
        $symbol    = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $recurring = $this->recurringModel->getForUser($userId);
        $categories= $this->catModel->getForUser($userId);
        $wallets   = $this->walletModel->getForUser($userId);

        return view('recurring/index', [
            'pageTitle'  => 'Transaksi Berulang',
            'recurring'  => $recurring,
            'categories' => $categories,
            'wallets'    => $wallets,
            'symbol'     => $symbol,
        ]);
    }

    // POST /recurring/store
    public function store()
    {
        $userId    = session()->get('user_id');
        $amount    = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('amount') ?? '0');
        $frequency = $this->request->getPost('frequency') ?: 'monthly';
        $startDate = $this->request->getPost('start_date') ?: date('Y-m-d');

        if (!in_array($frequency, ['weekly', 'monthly', 'yearly'])) {
            $frequency = 'monthly';
        }

        $data = [
            'user_id'     => $userId,
            'wallet_id'   => (int)($this->request->getPost('wallet_id') ?: 0) ?: null,
            'category_id' => (int)($this->request->getPost('category_id') ?: 0) ?: null,
            'type'        => $this->request->getPost('type') ?: 'expense',
            'amount'      => $amount,
            'note'        => $this->request->getPost('note') ?: null,
            'next_date'   => $startDate,
            'frequency'   => $frequency,
        ];

        if (!in_array($data['type'], ['income', 'expense']) || $amount <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak valid.']);
        }

        $id = $this->recurringModel->insert($data);
        return $this->response->setJSON(['success' => (bool)$id, 'id' => $id]);
    }

    // POST /recurring/process
    public function process()
    {
        $userId = session()->get('user_id');
        $count  = $this->recurringModel->processAll($userId);
        return $this->response->setJSON([
            'success' => true,
            'processed' => $count,
            'message' => $count > 0
                ? "$count transaksi berulang berhasil diproses."
                : 'Tidak ada transaksi yang jatuh tempo.',
        ]);
    }

    // POST /recurring/delete/{id}
    public function delete(int $id)
    {
        $userId = session()->get('user_id');
        $rec    = $this->recurringModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$rec) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ditemukan.']);
        }
        $this->recurringModel->delete($id);
        return $this->response->setJSON(['success' => true]);
    }
}
