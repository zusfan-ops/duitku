<?php

namespace App\Controllers;

use App\Models\SettingModel;

class BillController extends BaseController
{
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
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
        return $this->response->setJSON(['success' => true, 'bills' => $this->getBills($userId)]);
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
