<?php

namespace App\Controllers\Api;

use App\Models\CategoryModel;
use App\Models\RecurringTransactionModel;
use App\Models\TransactionModel;
use App\Models\WalletModel;

class TransactionController extends ApiController
{
    protected TransactionModel          $txModel;
    protected CategoryModel             $catModel;
    protected RecurringTransactionModel $recurringModel;
    protected WalletModel               $walletModel;

    public function __construct()
    {
        $this->txModel        = new TransactionModel();
        $this->catModel       = new CategoryModel();
        $this->recurringModel = new RecurringTransactionModel();
        $this->walletModel    = new WalletModel();
    }

    public function store()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];

        $walletId = (int) ($json['wallet_id'] ?? 0) ?: null;
        if (!$walletId) {
            $walletId = $this->walletModel->getDefaultWalletId($userId);
        }

        $data = [
            'user_id'     => $userId,
            'wallet_id'   => $walletId,
            'category_id' => ($json['category_id'] ?? null) ?: null,
            'type'        => $json['type'] ?? 'expense',
            'amount'      => $this->amount($json['amount'] ?? 0),
            'note'        => trim($json['note'] ?? '') ?: null,
            'date'        => $json['date'] ?? date('Y-m-d'),
        ];

        if (!in_array($data['type'], ['income', 'expense']) || $data['amount'] <= 0) {
            return $this->fail('Data tidak valid.');
        }

        // Base64 image upload (mobile)
        if (!empty($json['image_base64'])) {
            $uploaded = $this->saveBase64Image($json['image_base64']);
            if ($uploaded) {
                $data['image'] = $uploaded;
            }
        }

        $id = $this->txModel->insert($data);
        if (!$id) {
            return $this->fail('Gagal menyimpan.');
        }

        if (!empty($json['is_recurring'])) {
            $nextDate = date('Y-m-d', strtotime($data['date'] . ' +1 month'));
            $this->recurringModel->insert([
                'user_id'     => $userId,
                'category_id' => $data['category_id'],
                'type'        => $data['type'],
                'amount'      => $data['amount'],
                'note'        => $data['note'],
                'next_date'   => $nextDate,
            ]);
        }

        return $this->ok(['id' => $id]);
    }

    public function update(int $id)
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $tx     = $this->txModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$tx) {
            return $this->fail('Tidak ditemukan.');
        }

        $walletId = (int) ($json['wallet_id'] ?? 0) ?: null;
        if (!$walletId) {
            $walletId = $this->walletModel->getDefaultWalletId($userId);
        }

        $data = [
            'wallet_id'   => $walletId,
            'category_id' => ($json['category_id'] ?? null) ?: null,
            'type'        => $json['type'] ?? 'expense',
            'amount'      => $this->amount($json['amount'] ?? 0),
            'note'        => trim($json['note'] ?? '') ?: null,
            'date'        => $json['date'] ?? date('Y-m-d'),
        ];

        if (!in_array($data['type'], ['income', 'expense']) || $data['amount'] <= 0) {
            return $this->fail('Data tidak valid.');
        }

        if (!empty($json['image_base64'])) {
            $uploaded = $this->saveBase64Image($json['image_base64']);
            if ($uploaded) {
                $data['image'] = $uploaded;
                if (!empty($tx['image']) && file_exists(FCPATH . 'uploads/transactions/' . $tx['image'])) {
                    unlink(FCPATH . 'uploads/transactions/' . $tx['image']);
                }
            }
        } elseif (isset($json['remove_image']) && $json['remove_image']) {
            if (!empty($tx['image']) && file_exists(FCPATH . 'uploads/transactions/' . $tx['image'])) {
                unlink(FCPATH . 'uploads/transactions/' . $tx['image']);
            }
            $data['image'] = null;
        }

        $this->txModel->update($id, $data);
        return $this->ok();
    }

    public function delete(int $id)
    {
        $userId = $this->uid();
        $tx     = $this->txModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$tx) {
            return $this->fail('Tidak ditemukan.');
        }

        if (!empty($tx['image']) && file_exists(FCPATH . 'uploads/transactions/' . $tx['image'])) {
            unlink(FCPATH . 'uploads/transactions/' . $tx['image']);
        }

        $this->txModel->delete($id);
        return $this->ok();
    }

    public function show(int $id)
    {
        $userId = $this->uid();
        $tx     = $this->txModel
            ->select('t.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color, w.name AS wallet_name')
            ->join('categories c', 'c.id = t.category_id', 'left')
            ->join('wallets w', 'w.id = t.wallet_id', 'left')
            ->where('t.id', $id)
            ->where('t.user_id', $userId)
            ->first();

        if (!$tx) {
            return $this->fail('Tidak ditemukan.');
        }
        return $this->ok(['transaction' => $tx]);
    }

    public function deleteRecurring(int $id)
    {
        $userId = $this->uid();
        $rec    = $this->recurringModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$rec) {
            return $this->fail('Tidak ditemukan.');
        }
        $this->recurringModel->delete($id);
        return $this->ok();
    }

    private function saveBase64Image(string $base64): ?string
    {
        if (!preg_match('/^data:image\/(png|jpe?g|webp|gif);base64,(.+)$/i', $base64, $m)) {
            return null;
        }
        $mime  = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
        $data  = base64_decode($m[2], true);
        if ($data === false || strlen($data) > (5 * 1024 * 1024)) {
            return null;
        }

        $dir = FCPATH . 'uploads/transactions';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $name = 'tx_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $mime;
        file_put_contents($dir . '/' . $name, $data);
        return $name;
    }
}
