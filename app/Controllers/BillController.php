<?php

namespace App\Controllers;

use App\Models\SettingModel;
use App\Models\WalletModel;
use App\Models\CategoryModel;

class BillController extends BaseController
{
    protected SettingModel  $settingModel;
    protected WalletModel   $walletModel;
    protected CategoryModel $catModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
        $this->walletModel  = new WalletModel();
        $this->catModel     = new CategoryModel();
    }

    private function getBills(int $userId): array
    {
        $raw = $this->settingModel->get($userId, 'bills', '[]');
        return json_decode($raw, true) ?: [];
    }

    private function saveBills(int $userId, array $bills): void
    {
        $this->settingModel->setPref($userId, 'bills', json_encode(array_values($bills)));
    }

    // GET /bills
    public function index()
    {
        $userId = session()->get('user_id');
        $bills  = $this->getBills($userId);

        // If AJAX request or expects JSON, return JSON
        if ($this->request->isAJAX() || $this->request->header('Accept')?->getValue() === 'application/json') {
            return $this->response->setJSON(['success' => true, 'bills' => $bills]);
        }

        $symbol     = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $wallets    = $this->walletModel->getForUser($userId);
        $categories = $this->catModel->getForUser($userId);

        return view('bills/index', [
            'pageTitle'  => 'Daftar Tagihan',
            'bills'      => $bills,
            'wallets'    => $wallets,
            'categories' => $categories,
            'symbol'     => $symbol,
        ]);
    }

    // POST /bills/store
    public function store()
    {
        $userId = session()->get('user_id');
        $id     = trim($this->request->getPost('id') ?? '');
        $name   = trim($this->request->getPost('name') ?? '');
        $amount = (float)($this->request->getPost('amount') ?? 0);
        $dueDay = (int)($this->request->getPost('due_day') ?? 0);
        $notes  = trim($this->request->getPost('notes') ?? '');

        if (!$name || $dueDay < 1 || $dueDay > 31) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama dan tanggal jatuh tempo wajib diisi.']);
        }

        $bills = $this->getBills($userId);

        if ($id) {
            $found = false;
            foreach ($bills as &$b) {
                if ($b['id'] === $id) {
                    $b = ['id' => $id, 'name' => $name, 'amount' => $amount, 'dueDay' => $dueDay, 'notes' => $notes];
                    $found = true;
                    break;
                }
            }
            unset($b);
            if (!$found) {
                return $this->response->setJSON(['success' => false, 'message' => 'Tagihan tidak ditemukan.']);
            }
        } else {
            $id      = uniqid('b', true);
            $bills[] = ['id' => $id, 'name' => $name, 'amount' => $amount, 'dueDay' => $dueDay, 'notes' => $notes];
        }

        $this->saveBills($userId, $bills);
        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    // POST /bills/delete/{id}
    public function delete(string $id)
    {
        $userId = session()->get('user_id');
        $bills  = $this->getBills($userId);
        $bills  = array_filter($bills, fn($b) => $b['id'] !== $id);
        $this->saveBills($userId, $bills);
        return $this->response->setJSON(['success' => true]);
    }
}
