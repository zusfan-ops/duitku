<?php

namespace App\Controllers\Api;

use App\Models\SettingModel;

class BillController extends ApiController
{
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        $bills = $this->getBills($this->uid());
        return $this->ok(['bills' => $bills]);
    }

    public function store()
    {
        $json   = $this->request->getJSON(true) ?? [];
        $userId = $this->uid();
        $id     = trim($json['id'] ?? '');
        $name   = trim($json['name'] ?? '');
        $amount = (float) ($json['amount'] ?? 0);
        $dueDay = (int) ($json['due_day'] ?? 0);
        $notes  = trim($json['notes'] ?? '');

        if (!$name || $dueDay < 1 || $dueDay > 31) {
            return $this->fail('Nama dan tanggal jatuh tempo wajib diisi.');
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
                return $this->fail('Tagihan tidak ditemukan.');
            }
        } else {
            $id      = uniqid('b', true);
            $bills[] = ['id' => $id, 'name' => $name, 'amount' => $amount, 'dueDay' => $dueDay, 'notes' => $notes];
        }

        $this->saveBills($userId, $bills);
        return $this->ok(['id' => $id]);
    }

    public function delete(string $id)
    {
        $userId = $this->uid();
        $bills  = $this->getBills($userId);
        $bills  = array_values(array_filter($bills, fn ($b) => $b['id'] !== $id));
        $this->saveBills($userId, $bills);
        return $this->ok();
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
}
