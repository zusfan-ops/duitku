<?php

namespace App\Controllers\Api;

use App\Models\RecurringTransactionModel;
use App\Models\WalletModel;

class RecurringController extends ApiController
{
    protected RecurringTransactionModel $recurringModel;
    protected WalletModel               $walletModel;

    public function __construct()
    {
        $this->recurringModel = new RecurringTransactionModel();
        $this->walletModel    = new WalletModel();
    }

    // GET api/recurring
    public function index()
    {
        $userId    = $this->uid();
        $recurring = $this->recurringModel->getForUser($userId);
        return $this->ok(['recurring' => $recurring]);
    }

    // POST api/recurring/store
    public function store()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];

        $amount    = $this->amount($json['amount'] ?? 0);
        $frequency = $json['frequency'] ?? 'monthly';
        $startDate = $json['start_date'] ?? date('Y-m-d');

        if (!in_array($frequency, ['weekly', 'monthly', 'yearly'])) {
            $frequency = 'monthly';
        }

        $walletId = (int)($json['wallet_id'] ?? 0) ?: null;
        if (!$walletId) {
            $walletId = $this->walletModel->getDefaultWalletId($userId);
        }

        $data = [
            'user_id'     => $userId,
            'wallet_id'   => $walletId,
            'category_id' => ($json['category_id'] ?? null) ?: null,
            'type'        => $json['type'] ?? 'expense',
            'amount'      => $amount,
            'note'        => trim($json['note'] ?? '') ?: null,
            'next_date'   => $startDate,
            'frequency'   => $frequency,
        ];

        if (!in_array($data['type'], ['income', 'expense']) || $amount <= 0) {
            return $this->fail('Data tidak valid.');
        }

        $id = $this->recurringModel->insert($data);
        if (!$id) return $this->fail('Gagal menyimpan.');

        return $this->ok(['id' => $id]);
    }

    // POST api/recurring/process
    public function process()
    {
        $userId = $this->uid();
        $count  = $this->recurringModel->processAll($userId);
        return $this->ok([
            'processed' => $count,
            'message'   => $count > 0
                ? "$count transaksi berulang berhasil diproses."
                : 'Tidak ada transaksi yang jatuh tempo.',
        ]);
    }

    // POST api/recurring/execute/(:num)
    public function execute(int $id)
    {
        $userId = $this->uid();
        $txId   = $this->recurringModel->executeSingle($id, $userId);
        if (!$txId) return $this->fail('Gagal mengeksekusi transaksi.');

        return $this->ok([
            'message' => 'Transaksi berhasil dicatat sebagai pengeluaran/pemasukan!',
            'tx_id'   => $txId,
        ]);
    }
}
