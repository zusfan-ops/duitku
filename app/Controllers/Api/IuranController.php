<?php

namespace App\Controllers\Api;

use App\Models\IuranConfigModel;
use App\Models\IuranPaymentModel;
use App\Models\NeighborhoodModel;
use App\Models\UserModel;
use App\Services\NeighborhoodService;

class IuranController extends ApiController
{
    protected IuranConfigModel    $configModel;
    protected IuranPaymentModel   $paymentModel;
    protected NeighborhoodModel   $neighborhoodModel;
    protected UserModel           $userModel;
    protected NeighborhoodService $neighborhoodService;

    public function __construct()
    {
        $this->configModel         = new IuranConfigModel();
        $this->paymentModel        = new IuranPaymentModel();
        $this->neighborhoodModel   = new NeighborhoodModel();
        $this->userModel           = new UserModel();
        $this->neighborhoodService = new NeighborhoodService();
    }

    /**
     * Dapatkan user saat ini + konteks RT.
     */
    private function context(): array
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);
        return [$userId, $user, $neighborhoodId];
    }

    /**
     * Data Iuran RT / Komunitas
     * GET /api/iuran
     */
    public function index()
    {
        [$userId, $user, $neighborhoodId] = $this->context();

        if (!$neighborhoodId) {
            return $this->ok([
                'joined'   => false,
                'config'   => null,
                'summary'  => null,
                'payments' => [],
                'message'  => 'Anda belum terhubung ke komunitas RT mana pun.',
            ]);
        }

        $canManage = $this->neighborhoodService->canManageKasOrAgenda($neighborhoodId, $userId);
        $config    = $this->configModel->getActiveConfig($neighborhoodId);

        if (!$config) {
            return $this->ok([
                'joined'   => true,
                'config'   => null,
                'can_manage' => $canManage,
                'summary'  => null,
                'payments' => [],
            ]);
        }

        $periodMonth = date('Y-m');
        $residents   = $this->neighborhoodModel->getResidents($neighborhoodId, 'verified');
        $residentIds = array_map(fn($r) => (int)$r['id'], $residents);

        // Siapkan row pembayaran per periode saat ini jika belum ada
        if ($canManage && $residentIds) {
            $this->paymentModel->ensurePeriodRows(
                $neighborhoodId,
                $periodMonth,
                $residentIds,
                (int)$config['id'],
                (float)$config['amount']
            );
        }

        $summary  = $this->paymentModel->getSummary($neighborhoodId, $periodMonth, (int)$config['id']);
        $payments = $this->paymentModel->getForPeriod($neighborhoodId, $periodMonth, (int)$config['id']);

        // Data warga untuk pilihan pembayaran manual (dari daftar verified)
        $residentDrop = array_map(fn($r) => [
            'id'   => (int)$r['id'],
            'name' => $r['name'],
            'house_number' => $r['house_number'] ?? null,
        ], $residents);

        // Riwayat pembayaran pribadi warga
        $myHistory = [];
        if (!$canManage) {
            $myHistory = $this->paymentModel->getMyPaymentHistory($userId, 12);
        }

        return $this->ok([
            'joined'          => true,
            'can_manage'      => $canManage,
            'config'          => $config,
            'period_month'    => $periodMonth,
            'summary'         => $summary,
            'payments'        => $payments,
            'residents'       => $residentDrop,
            'my_history'      => $myHistory,
        ]);
    }

    /**
     * Simpan / Perbarui konfigurasi iuran (periode + nominal).
     * POST /api/iuran/config
     */
    public function storeConfig()
    {
        [$userId, $user, $neighborhoodId] = $this->context();

        if (!$neighborhoodId) {
            return $this->fail('Anda belum terhubung ke komunitas RT mana pun.');
        }

        if (!$this->neighborhoodService->canManageKasOrAgenda($neighborhoodId, $userId)) {
            return $this->fail('Hanya Ketua RT atau Bendahara yang dapat mengatur iuran.');
        }

        $json = $this->request->getJSON(true) ?? $this->request->getPost();
        $periodType = in_array($json['period_type'] ?? '', ['monthly', 'weekly', 'yearly'], true)
            ? $json['period_type'] : 'monthly';
        $amount = (float)(str_replace(['.', ','], ['', '.'], (string)($json['amount'] ?? '0')));
        $desc   = trim($json['description'] ?? 'Iuran Warga Bulanan');

        if ($amount <= 0) {
            return $this->fail('Nominal iuran harus lebih dari Rp 0.');
        }

        $id = $this->configModel->setActive($neighborhoodId, [
            'period_type' => $periodType,
            'amount'      => $amount,
            'description' => $desc,
        ]);

        return $this->ok([
            'config_id' => $id,
            'config'    => $this->configModel->find($id),
            'message'   => 'Konfigurasi iuran berhasil diperbarui.',
        ]);
    }

    /**
     * Catat pembayaran iuran seorang warga untuk periode tertentu.
     * POST /api/iuran/pay
     */
    public function pay()
    {
        [$userId, $user, $neighborhoodId] = $this->context();

        if (!$neighborhoodId) {
            return $this->fail('Anda belum terhubung ke komunitas RT mana pun.');
        }

        $json = $this->request->getJSON(true) ?? $this->request->getPost();
        $paymentId = (int)($json['payment_id'] ?? 0);
        $residentName = trim($json['resident_name'] ?? '');

        if (!$paymentId) {
            return $this->fail('Catatan iuran tidak valid.');
        }

        $record = $this->paymentModel->find($paymentId);
        if (!$record || (int)$record['neighborhood_id'] !== $neighborhoodId) {
            return $this->fail('Catatan iuran tidak valid.');
        }

        // Hanya pengurus RT yang bisa mencatat bayar warga lain; warga hanya untuk dirinya sendiri.
        $canManage = $this->neighborhoodService->canManageKasOrAgenda($neighborhoodId, $userId);
        if (!$canManage && (int)$record['resident_user_id'] !== $userId) {
            return $this->fail('Anda hanya dapat membayar iuran Anda sendiri.');
        }

        $res = $this->paymentModel->markPaid($paymentId, $userId, $json);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok([
            'message' => 'Pembayaran iuran berhasil dicatat.',
            'status'  => $res['status'],
            'amount_paid' => $res['amount_paid'],
            'kas_id'  => $res['kas_id'],
        ]);
    }

    /**
     * Histori konfigurasi iuran RT.
     * GET /api/iuran/history
     */
    public function history()
    {
        [$userId, $user, $neighborhoodId] = $this->context();

        if (!$neighborhoodId) {
            return $this->fail('Anda belum terhubung ke komunitas RT mana pun.');
        }

        $history = $this->configModel->getHistory($neighborhoodId);
        return $this->ok(['history' => $history]);
    }
}
