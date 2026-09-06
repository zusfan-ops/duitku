<?php

namespace App\Services;

use App\Models\ErrandItemModel;
use App\Models\ErrandModel;
use App\Models\NeighborhoodModel;
use App\Models\TransactionModel;
use App\Models\UserModel;
use App\Models\WalletModel;
use App\Models\NotificationModel;

class ErrandService
{
    protected ErrandModel       $errandModel;
    protected ErrandItemModel   $itemModel;
    protected NeighborhoodModel $neighborhoodModel;
    protected UserModel         $userModel;
    protected TransactionModel  $txModel;
    protected WalletModel       $walletModel;
    protected NotificationModel $notificationModel;
    protected FcmService        $fcmService;

    public function __construct()
    {
        $this->errandModel       = new ErrandModel();
        $this->itemModel         = new ErrandItemModel();
        $this->neighborhoodModel = new NeighborhoodModel();
        $this->userModel         = new UserModel();
        $this->txModel           = new TransactionModel();
        $this->walletModel       = new WalletModel();
        $this->notificationModel = new NotificationModel();
        $this->fcmService        = new FcmService();
    }

    /**
     * Buka Sesi Titip Belanja Baru oleh Warga
     */
    public function createErrand(int $organizerUserId, array $data): array
    {
        $user = $this->userModel->find($organizerUserId);
        if (!$user || ($user['rt_verification_status'] ?? '') !== 'verified') {
            return ['success' => false, 'message' => 'Anda harus menjadi warga terverifikasi untuk membuka sesi titip belanja.'];
        }

        $neighborhoodId = (int)$user['neighborhood_id'];
        $cutoffTime = $data['cutoff_time'] ?? date('Y-m-d H:i:s', strtotime('+2 hours'));

        $errandId = $this->errandModel->insert([
            'neighborhood_id'   => $neighborhoodId,
            'organizer_user_id' => $organizerUserId,
            'destination_store' => trim($data['destination_store'] ?? 'Pasar / Supermarket'),
            'description'       => trim($data['description'] ?? ''),
            'cutoff_time'       => $cutoffTime,
            'est_delivery_time' => !empty($data['est_delivery_time']) ? $data['est_delivery_time'] : null,
            'max_requesters'    => (int)($data['max_requesters'] ?? 5),
            'status'            => 'open',
        ]);

        return [
            'success'   => true,
            'errand_id' => $errandId,
            'message'   => 'Sesi titip belanja berhasil dibuka! Warga satu RT sekarang dapat menitipkan barang belanjaan.',
        ];
    }

    /**
     * Warga Mengajukan Item Titipan Belanja
     */
    public function submitItem(int $requesterUserId, int $errandId, array $data): array
    {
        $errand = $this->errandModel->find($errandId);
        if (!$errand || $errand['status'] !== 'open') {
            return ['success' => false, 'message' => 'Sesi titip belanja ini sudah ditutup atau tidak menerima pesanan lagi.'];
        }

        if (strtotime($errand['cutoff_time']) <= time()) {
            return ['success' => false, 'message' => 'Waktu batas titipan (cutoff time) telah berakhir.'];
        }

        $user = $this->userModel->find($requesterUserId);
        if (!$user || (int)$user['neighborhood_id'] !== (int)$errand['neighborhood_id']) {
            return ['success' => false, 'message' => 'Anda harus berada di lingkungan RT yang sama untuk menitip belanja.'];
        }

        $handoverToken = strtoupper(bin2hex(random_bytes(3))); // Misal: 9B2C4F

        $itemId = $this->itemModel->insert([
            'errand_id'         => $errandId,
            'requester_user_id' => $requesterUserId,
            'item_name'         => trim($data['item_name'] ?? ''),
            'quantity'          => (float)($data['quantity'] ?? 1),
            'unit'              => trim($data['unit'] ?? 'pcs'),
            'estimated_price'   => (float)($data['estimated_price'] ?? 0),
            'service_fee'       => (float)($data['service_fee'] ?? 5000),
            'status'            => 'pending',
            'handover_token'    => $handoverToken,
            'notes'             => trim($data['notes'] ?? ''),
        ]);

        // Notifikasi ke Organizer
        $this->notificationModel->insert([
            'title'      => '🛍️ Titipan Belanja Baru',
            'message'    => "{$user['name']} menitip {$data['item_name']} untuk dibeli di {$errand['destination_store']}.",
            'type'       => 'info',
            'target'     => 'user',
            'user_id'    => (int)$errand['organizer_user_id'],
            'action_url' => '/neighborhood/errands/' . $errandId,
        ]);

        return [
            'success'        => true,
            'item_id'        => $itemId,
            'handover_token' => $handoverToken,
            'message'        => 'Titipan berhasil dicatat! Simpan token serah terima untuk diberikan kepada pembelanja saat barang tiba.',
        ];
    }

    /**
     * Konfirmasi Serah Terima Belanjaan & Pencatatan Transaksi Keuangan
     */
    public function deliverItem(int $organizerUserId, int $itemId, string $token, float $actualPrice, ?string $receiptPhoto = null): array
    {
        $item = $this->itemModel->find($itemId);
        if (!$item) {
            return ['success' => false, 'message' => 'Item titipan tidak ditemukan.'];
        }

        $errand = $this->errandModel->find($item['errand_id']);
        if (!$errand || (int)$errand['organizer_user_id'] !== $organizerUserId) {
            return ['success' => false, 'message' => 'Hanya pembelanja (organizer) yang dapat mengonfirmasi serah terima barang ini.'];
        }

        if (strtoupper(trim($token)) !== strtoupper(trim($item['handover_token']))) {
            return ['success' => false, 'message' => 'Token serah terima salah atau tidak cocok dengan kode pemesan.'];
        }

        $requesterId = (int)$item['requester_user_id'];
        $totalCost = $actualPrice + (float)$item['service_fee'];
        $today = date('Y-m-d');

        $requesterTxId = null;
        $organizerTxId = null;

        // INTEGRASI LEDGER KEUANGAN (TRANSACTIONS)
        try {
            $reqWalletId = $this->walletModel->getDefaultWalletId($requesterId);
            $orgWalletId = $this->walletModel->getDefaultWalletId($organizerUserId);

            // 1. Pengeluaran bagi Requester (Harga Barang + Fee Jasa)
            $requesterTxId = $this->txModel->insert([
                'user_id'   => $requesterId,
                'wallet_id' => $reqWalletId,
                'type'      => 'expense',
                'amount'    => $totalCost,
                'note'      => 'Titip Belanja: ' . $item['item_name'] . ' di ' . $errand['destination_store'] . ' (Barang: Rp ' . number_format($actualPrice, 0, ',', '.') . ' + Jasa: Rp ' . number_format($item['service_fee'], 0, ',', '.') . ')',
                'date'      => $today,
                'image'     => $receiptPhoto,
            ]);

            // 2. Pemasukan / Reimbursement bagi Organizer
            $organizerTxId = $this->txModel->insert([
                'user_id'   => $organizerUserId,
                'wallet_id' => $orgWalletId,
                'type'      => 'income',
                'amount'    => $totalCost,
                'note'      => 'Reimbursement Belanja + Jasa: ' . $item['item_name'] . ' untuk tetangga',
                'date'      => $today,
                'image'     => $receiptPhoto,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Ledger Errand Error: ' . $e->getMessage());
        }

        $this->itemModel->update($itemId, [
            'status'          => 'delivered',
            'actual_price'    => $actualPrice,
            'receipt_photo'   => $receiptPhoto,
            'transaction_id'  => $requesterTxId,
            'organizer_tx_id' => $organizerTxId,
            'delivered_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->notificationModel->insert([
            'title'      => '🎁 Titipan Belanja Selesai Diterima',
            'message'    => "Barang {$item['item_name']} telah diserahterimakan. Total pembayaran Rp " . number_format($totalCost, 0, ',', '.') . " otomatis dicatat ke buku keuangan Anda.",
            'type'       => 'info',
            'target'     => 'user',
            'user_id'    => $requesterId,
            'action_url' => '/neighborhood/errands/' . $errand['id'],
        ]);

        return [
            'success'    => true,
            'total_cost' => $totalCost,
            'message'    => 'Serah terima belanjaan berhasil divalidasi dan dicatat ke sistem keuangan kedua pihak!',
        ];
    }
}
