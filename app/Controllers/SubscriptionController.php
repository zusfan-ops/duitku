<?php

namespace App\Controllers;

use App\Models\SubscriptionModel;
use App\Models\UserModel;

class SubscriptionController extends BaseController
{
    protected SubscriptionModel $subscriptionModel;
    protected UserModel         $userModel;

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        $this->userModel         = new UserModel();
    }

    /**
     * Halaman daftar langganan + ringkasan.
     * GET /subscriptions
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        $status  = trim((string)$this->request->getGet('status'));
        $list    = $this->subscriptionModel->getForUser($userId, $status);
        $summary = $this->subscriptionModel->getSummary($userId);
        $upcoming = $this->subscriptionModel->getUpcoming($userId, 7);

        return view('subscription/index', [
            'pageTitle'   => 'Subscription & Langganan',
            'user'        => $user,
            'subscriptions' => $list,
            'summary'     => $summary,
            'upcoming'    => $upcoming,
            'symbol'      => 'Rp',
        ]);
    }

    /**
     * Simpan langganan baru.
     * POST /subscriptions/store
     */
    public function store()
    {
        $userId = session()->get('user_id');
        $post   = $this->request->getPost();

        $name = trim($post['name'] ?? '');
        if (empty($name)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama langganan wajib diisi.']);
        }

        $amount = $this->parseAmount($post['amount'] ?? '0');
        if ($amount < 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nominal langganan tidak valid.']);
        }

        $cycle = in_array($post['billing_cycle'] ?? '', ['weekly', 'monthly', 'quarterly', 'yearly'], true)
            ? $post['billing_cycle'] : 'monthly';

        $startDate    = $post['start_date'] ?? date('Y-m-d');
        $nextBilling  = $post['next_billing_date'] ?? SubscriptionModel::calculateNextBilling($startDate, $cycle);

        $id = $this->subscriptionModel->insert([
            'user_id'           => $userId,
            'name'              => $name,
            'category'          => trim($post['category'] ?? 'Hiburan'),
            'amount'            => $amount,
            'currency'          => 'IDR',
            'billing_cycle'     => $cycle,
            'start_date'        => $startDate,
            'next_billing_date' => $nextBilling,
            'status'            => 'active',
            'payment_method'    => $post['payment_method'] ?? null,
            'provider_url'      => $post['provider_url'] ?? null,
            'notes'             => trim($post['notes'] ?? ''),
            'icon'              => $post['icon'] ?? null,
            'color'             => $post['color'] ?? null,
            'notify_before_days'=> (int)($post['notify_before_days'] ?? 3),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Langganan berhasil ditambahkan.',
            'id'      => $id,
        ]);
    }

    /**
     * Perbarui langganan.
     * POST /subscriptions/update/(:num)
     */
    public function update(int $id)
    {
        $userId = session()->get('user_id');
        $sub    = $this->subscriptionModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$sub) {
            return $this->response->setJSON(['success' => false, 'message' => 'Langganan tidak ditemukan.']);
        }

        $post = $this->request->getPost();
        $data = ['id' => $id];

        foreach ([
            'name', 'category', 'billing_cycle', 'status', 'payment_method',
            'provider_url', 'notes', 'icon', 'color', 'notify_before_days',
            'next_billing_date',
        ] as $field) {
            if (array_key_exists($field, $post)) {
                $data[$field] = $post[$field];
            }
        }
        if (array_key_exists('amount', $post)) {
            $data['amount'] = $this->parseAmount($post['amount']);
        }
        if (array_key_exists('is_waste', $post)) {
            $data['is_waste'] = $post['is_waste'] ? 1 : 0;
        }

        unset($data['id']);
        $this->subscriptionModel->update($id, $data);

        return $this->response->setJSON(['success' => true, 'message' => 'Langganan berhasil diperbarui.']);
    }

    /**
     * Catat pembayaran & majukan tanggal.
     * POST /subscriptions/pay/(:num)
     */
    public function pay(int $id)
    {
        $userId = session()->get('user_id');
        $sub    = $this->subscriptionModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$sub) {
            return $this->response->setJSON(['success' => false, 'message' => 'Langganan tidak ditemukan.']);
        }

        $nextBilling = SubscriptionModel::calculateNextBilling($sub['next_billing_date'] ?: date('Y-m-d'), $sub['billing_cycle']);
        $this->subscriptionModel->update($id, [
            'next_billing_date' => $nextBilling,
            'status'            => 'active',
            'last_used_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pembayaran langganan dicatat.',
        ]);
    }

    /**
     * Hapus langganan.
     * POST /subscriptions/delete/(:num)
     */
    public function delete(int $id)
    {
        $userId = session()->get('user_id');
        $sub    = $this->subscriptionModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$sub) {
            return $this->response->setJSON(['success' => false, 'message' => 'Langganan tidak ditemukan.']);
        }

        $this->subscriptionModel->delete($id);

        return $this->response->setJSON(['success' => true, 'message' => 'Langganan berhasil dihapus.']);
    }
}
