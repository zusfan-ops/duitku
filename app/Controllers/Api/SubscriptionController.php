<?php

namespace App\Controllers\Api;

use App\Models\SubscriptionModel;

class SubscriptionController extends ApiController
{
    protected SubscriptionModel $subscriptionModel;

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
    }

    /**
     * Daftar langganan + ringkasan.
     * GET /api/subscriptions?status=active
     */
    public function index()
    {
        $userId = $this->uid();
        $status = trim((string)$this->request->getGet('status'));

        $list    = $this->subscriptionModel->getForUser($userId, $status);
        $summary = $this->subscriptionModel->getSummary($userId);
        $upcoming = $this->subscriptionModel->getUpcoming($userId, 7);

        return $this->ok([
            'subscriptions' => $list,
            'summary'       => $summary,
            'upcoming'      => $upcoming,
        ]);
    }

    /**
     * Simpan langganan baru.
     * POST /api/subscriptions/store
     */
    public function store()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? $this->request->getPost();

        $name = trim($json['name'] ?? '');
        if (empty($name)) {
            return $this->fail('Nama langganan wajib diisi.');
        }

        $amount = (float)(str_replace(['.', ','], ['', '.'], (string)($json['amount'] ?? '0')));
        if ($amount < 0) {
            return $this->fail('Nominal langganan tidak valid.');
        }

        $cycle = in_array($json['billing_cycle'] ?? '', ['weekly', 'monthly', 'quarterly', 'yearly'], true)
            ? $json['billing_cycle'] : 'monthly';

        $startDate = $json['start_date'] ?? date('Y-m-d');
        $nextBilling = $json['next_billing_date'] ?? SubscriptionModel::calculateNextBilling($startDate, $cycle);

        $id = $this->subscriptionModel->insert([
            'user_id'           => $userId,
            'name'              => $name,
            'category'          => trim($json['category'] ?? 'Hiburan'),
            'amount'            => $amount,
            'currency'          => $json['currency'] ?? 'IDR',
            'billing_cycle'     => $cycle,
            'start_date'        => $startDate,
            'next_billing_date' => $nextBilling,
            'status'            => $json['status'] ?? 'active',
            'payment_method'    => $json['payment_method'] ?? null,
            'provider_url'      => $json['provider_url'] ?? null,
            'notes'             => trim($json['notes'] ?? ''),
            'icon'              => $json['icon'] ?? null,
            'color'             => $json['color'] ?? null,
            'notify_before_days'=> (int)($json['notify_before_days'] ?? 3),
            'category_id'       => !empty($json['category_id']) ? (int)$json['category_id'] : null,
            'wallet_id'         => !empty($json['wallet_id']) ? (int)$json['wallet_id'] : null,
            'is_waste'          => !empty($json['is_waste']) ? 1 : 0,
            'waste_reason'      => $json['waste_reason'] ?? null,
        ]);

        return $this->ok([
            'subscription_id' => $id,
            'message' => 'Langganan berhasil ditambahkan.',
            'subscription' => $this->subscriptionModel->find($id),
        ]);
    }

    /**
     * Perbarui langganan (termasuk tandai sebagai mubazir / ubah status).
     * POST /api/subscriptions/update/(:num)
     */
    public function update(int $id)
    {
        $userId = $this->uid();
        $sub    = $this->subscriptionModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$sub) {
            return $this->fail('Langganan tidak ditemukan.');
        }

        $json = $this->request->getJSON(true) ?? $this->request->getPost();

        $data = [];
        foreach ([
            'name', 'category', 'amount', 'currency', 'billing_cycle',
            'start_date', 'next_billing_date', 'status', 'payment_method',
            'provider_url', 'notes', 'icon', 'color', 'notify_before_days',
            'is_waste', 'waste_reason',
        ] as $field) {
            if (array_key_exists($field, $json)) {
                $data[$field] = $json[$field];
            }
        }

        if (array_key_exists('amount', $data) && is_string($data['amount'])) {
            $data['amount'] = (float)str_replace(['.', ','], ['', '.'], $data['amount']);
        }
        if (array_key_exists('is_waste', $data)) {
            $data['is_waste'] = $data['is_waste'] ? 1 : 0;
        }

        $this->subscriptionModel->update($id, $data);

        return $this->ok([
            'message' => 'Langganan berhasil diperbarui.',
            'subscription' => $this->subscriptionModel->find($id),
        ]);
    }

    /**
     * Tandai langganan sudah dibayar & majukan tanggal berikutnya.
     * POST /api/subscriptions/pay/(:num)
     */
    public function pay(int $id)
    {
        $userId = $this->uid();
        $sub    = $this->subscriptionModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$sub) {
            return $this->fail('Langganan tidak ditemukan.');
        }

        $nextBilling = SubscriptionModel::calculateNextBilling($sub['next_billing_date'] ?: date('Y-m-d'), $sub['billing_cycle']);
        $this->subscriptionModel->update($id, [
            'next_billing_date' => $nextBilling,
            'status'            => 'active',
            'last_used_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->ok([
            'message' => 'Pembayaran langganan dicatat.',
            'next_billing_date' => $nextBilling,
        ]);
    }

    /**
     * Hapus langganan.
     * POST /api/subscriptions/delete/(:num)
     */
    public function delete(int $id)
    {
        $userId = $this->uid();
        $sub    = $this->subscriptionModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$sub) {
            return $this->fail('Langganan tidak ditemukan.');
        }

        $this->subscriptionModel->delete($id);

        return $this->ok(['message' => 'Langganan berhasil dihapus.']);
    }
}
