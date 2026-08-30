<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\CategoryModel;
use App\Models\SettingModel;
use App\Models\RecurringTransactionModel;
use App\Models\WalletModel;

class TransactionController extends BaseController
{
    protected TransactionModel          $txModel;
    protected CategoryModel             $catModel;
    protected SettingModel              $settingModel;
    protected RecurringTransactionModel $recurringModel;
    protected WalletModel               $walletModel;

    public function __construct()
    {
        $this->txModel        = new TransactionModel();
        $this->catModel       = new CategoryModel();
        $this->settingModel   = new SettingModel();
        $this->recurringModel = new RecurringTransactionModel();
        $this->walletModel    = new WalletModel();
    }

    // -------------------------------------------------------------------------
    // POST /transaction/store
    // -------------------------------------------------------------------------
    public function store()
    {
        $userId = session()->get('user_id');

        $walletId = (int)($this->request->getPost('wallet_id') ?: 0) ?: null;
        if (!$walletId) {
            $walletId = $this->walletModel->getDefaultWalletId($userId);
        }

        $data = [
            'user_id'     => $userId,
            'wallet_id'   => $walletId,
            'category_id' => $this->request->getPost('category_id') ?: null,
            'type'        => $this->request->getPost('type'),
            'amount'      => $this->parseAmount($this->request->getPost('amount')),
            'note'        => $this->request->getPost('note') ?: null,
            'date'        => $this->request->getPost('date') ?: date('Y-m-d'),
        ];

        if (!in_array($data['type'], ['income', 'expense']) || $data['amount'] <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak valid.']);
        }

        // Handle Image Upload with Strict Validation
        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Format file gambar harus JPG, PNG, atau WebP.']);
            }
            if ($file->getSize() > 3 * 1024 * 1024) {
                return $this->response->setJSON(['success' => false, 'message' => 'Ukuran file gambar maksimal 3MB.']);
            }
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/transactions', $newName);
            $data['image'] = $newName;
        } elseif ($this->request->getPost('existing_image')) {
            $existing = basename($this->request->getPost('existing_image'));
            if (file_exists(FCPATH . 'uploads/transactions/' . $existing)) {
                $data['image'] = $existing;
            }
        }

        $id = $this->txModel->insert($data);

        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan.']);
        }

        // Handle recurring
        $isRecurring = $this->request->getPost('is_recurring') === '1';
        if ($isRecurring) {
            $freq     = $this->request->getPost('frequency') ?: 'monthly';
            if (!in_array($freq, ['weekly', 'monthly', 'yearly'])) $freq = 'monthly';
            $nextDate = date('Y-m-d', strtotime($data['date'] . match($freq) {
                'weekly' => ' +1 week',
                'yearly' => ' +1 year',
                default  => ' +1 month',
            }));
            $this->recurringModel->insert([
                'user_id'     => $userId,
                'wallet_id'   => $walletId,
                'category_id' => $data['category_id'],
                'type'        => $data['type'],
                'amount'      => $data['amount'],
                'note'        => $data['note'],
                'next_date'   => $nextDate,
                'frequency'   => $freq,
            ]);
        }

        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    // -------------------------------------------------------------------------
    // POST /transaction/update/{id}
    // -------------------------------------------------------------------------
    public function update(int $id)
    {
        $userId = session()->get('user_id');
        $tx     = $this->txModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$tx) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ditemukan.']);
        }

        $walletId = (int)($this->request->getPost('wallet_id') ?: 0) ?: null;
        if (!$walletId) {
            $walletId = $this->walletModel->getDefaultWalletId($userId);
        }

        $data = [
            'wallet_id'   => $walletId,
            'category_id' => $this->request->getPost('category_id') ?: null,
            'type'        => $this->request->getPost('type'),
            'amount'      => $this->parseAmount($this->request->getPost('amount')),
            'note'        => $this->request->getPost('note') ?: null,
            'date'        => $this->request->getPost('date') ?: date('Y-m-d'),
        ];

        if (!in_array($data['type'], ['income', 'expense']) || $data['amount'] <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak valid.']);
        }

        // Handle Image Upload with Strict Validation
        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Format file gambar harus JPG, PNG, atau WebP.']);
            }
            if ($file->getSize() > 3 * 1024 * 1024) {
                return $this->response->setJSON(['success' => false, 'message' => 'Ukuran file gambar maksimal 3MB.']);
            }
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/transactions', $newName);
            $data['image'] = $newName;

            // Delete old image
            if (!empty($tx['image']) && file_exists(FCPATH . 'uploads/transactions/' . $tx['image'])) {
                unlink(FCPATH . 'uploads/transactions/' . $tx['image']);
            }
        }

        $this->txModel->update($id, $data);
        return $this->response->setJSON(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // POST /transaction/delete/{id}
    // -------------------------------------------------------------------------
    public function delete(int $id)
    {
        $userId = session()->get('user_id');
        $tx     = $this->txModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$tx) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ditemukan.']);
        }

        // Delete image if exists
        if (!empty($tx['image']) && file_exists(FCPATH . 'uploads/transactions/' . $tx['image'])) {
            unlink(FCPATH . 'uploads/transactions/' . $tx['image']);
        }

        $this->txModel->delete($id);
        return $this->response->setJSON(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // GET /transaction/{id} — untuk edit modal
    // -------------------------------------------------------------------------
    public function show(int $id)
    {
        $userId = session()->get('user_id');
        $tx     = $this->txModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$tx) {
            return $this->response->setJSON(['success' => false]);
        }
        return $this->response->setJSON(['success' => true, 'data' => $tx]);
    }

    // -------------------------------------------------------------------------
    // POST /recurring/delete/{id}
    // -------------------------------------------------------------------------
    public function deleteRecurring(int $id)
    {
        $userId = session()->get('user_id');
        $rec    = $this->recurringModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$rec) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ditemukan.']);
        }

        $this->recurringModel->delete($id);
        return $this->response->setJSON(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // POST /transaction/ocr-scan (Smart Receipt Scanner)
    // -------------------------------------------------------------------------
    public function ocrScan()
    {
        $text = trim($this->request->getPost('receipt_text') ?? '');
        $file = $this->request->getFile('receipt_image');

        $ocrService = new \App\Libraries\ReceiptOcrService();
        $parsed = $ocrService->parseReceiptText($text);

        // If an image was uploaded, save it as a pending transaction receipt
        $savedImage = null;
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (in_array($file->getMimeType(), $allowedMimes) && $file->getSize() <= 3 * 1024 * 1024) {
                $savedImage = $file->getRandomName();
                $file->move(FCPATH . 'uploads/transactions', $savedImage);
            }
        }

        return $this->response->setJSON([
            'success'     => true,
            'data'        => $parsed,
            'image'       => $savedImage,
            'image_url'   => $savedImage ? base_url('uploads/transactions/' . $savedImage) : null,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /scan & /scan-ocr — Smart OCR Receipt Scan View
    // -------------------------------------------------------------------------
    public function ocrPage()
    {
        $userId = session()->get('user_id');
        $walletsData = $this->walletModel->getWithBalances($userId);
        $categories  = $this->catModel->getForUser($userId);
        $symbol      = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return view('scan/ocr', [
            'pageTitle'  => 'Scan Struk / Nota (OCR)',
            'wallets'    => $walletsData['wallets'] ?? [],
            'categories' => $categories,
            'symbol'     => $symbol,
        ]);
    }
}

