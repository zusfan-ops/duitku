<?php

namespace App\Services;

use App\Models\CommunityToolModel;
use App\Models\NeighborhoodModel;
use App\Models\ToolRentalModel;
use App\Models\TransactionModel;
use App\Models\UserModel;
use App\Models\WalletModel;
use App\Models\NotificationModel;

class ToolRentalService
{
    protected CommunityToolModel $toolModel;
    protected ToolRentalModel    $rentalModel;
    protected NeighborhoodModel  $neighborhoodModel;
    protected UserModel          $userModel;
    protected TransactionModel   $txModel;
    protected WalletModel        $walletModel;
    protected NotificationModel  $notificationModel;
    protected FcmService         $fcmService;

    public function __construct()
    {
        $this->toolModel         = new CommunityToolModel();
        $this->rentalModel       = new ToolRentalModel();
        $this->neighborhoodModel = new NeighborhoodModel();
        $this->userModel         = new UserModel();
        $this->txModel           = new TransactionModel();
        $this->walletModel       = new WalletModel();
        $this->notificationModel = new NotificationModel();
        $this->fcmService        = new FcmService();
    }

    /**
     * Ajukan Peminjaman Alat
     */
    public function requestRental(int $borrowerUserId, int $toolId, int $rentalDays, ?string $borrowerNote = null): array
    {
        $user = $this->userModel->find($borrowerUserId);
        $tool = $this->toolModel->find($toolId);

        if (!$tool || $tool['status'] !== 'available') {
            return ['success' => false, 'message' => 'Alat tidak tersedia untuk dipinjam saat ini.'];
        }

        if (!$user || (int)$user['neighborhood_id'] !== (int)$tool['neighborhood_id']) {
            return ['success' => false, 'message' => 'Hanya warga di lingkungan RT yang sama yang dapat meminjam alat ini.'];
        }

        if (($user['rt_verification_status'] ?? '') !== 'verified') {
            return ['success' => false, 'message' => 'Akun Anda belum terverifikasi oleh Ketua RT.'];
        }

        $neighborhood = $this->neighborhoodModel->find($tool['neighborhood_id']);

        // Cek batasan nilai barang jika warga berstatus Domisili / Kontrak
        if (($user['residence_status'] ?? 'permanent') === 'temporary') {
            $limit = (float)($neighborhood['max_borrow_limit_domisili'] ?? 250000);
            $itemValue = (float)$tool['deposit_amount'] + (float)$tool['rental_fee'];
            if ($itemValue > $limit && $limit > 0) {
                return [
                    'success' => false,
                    'message' => "Nilai alat/deposit (Rp " . number_format($itemValue, 0, ',', '.') . ") melebihi batas pinjam warga domisili (Rp " . number_format($limit, 0, ',', '.') . "). Hubungi Ketua RT untuk izin khusus."
                ];
            }
        }

        $days = max(1, min($rentalDays, (int)($tool['max_rent_days'] ?: 7)));
        $startDate = date('Y-m-d');
        $dueDate   = date('Y-m-d', strtotime("+{$days} days"));

        // Generate token rahasia untuk serah terima dan pengembalian
        $handoverToken = strtoupper(bin2hex(random_bytes(3))); // Contoh: 7A9B3F
        $returnToken   = strtoupper(bin2hex(random_bytes(3)));

        $rentalId = $this->rentalModel->insert([
            'neighborhood_id'  => $tool['neighborhood_id'],
            'tool_id'          => $toolId,
            'borrower_user_id' => $borrowerUserId,
            'status'           => 'requested',
            'rental_fee'       => $tool['rental_fee'],
            'deposit_amount'   => $tool['deposit_amount'],
            'start_date'       => $startDate,
            'due_date'         => $dueDate,
            'handover_token'   => $handoverToken,
            'return_token'     => $returnToken,
            'borrower_note'    => $borrowerNote,
        ]);

        // Notifikasi ke Pengurus RT / Pemilik Alat
        $ownerId = (int)($tool['owner_user_id'] ?: $neighborhood['admin_user_id']);
        if ($ownerId) {
            $this->notificationModel->insert([
                'title'      => '🔨 Pengajuan Pinjam Alat: ' . $tool['name'],
                'message'    => "{$user['name']} mengajukan pinjam {$tool['name']} selama {$days} hari.",
                'type'       => 'info',
                'target'     => 'user',
                'user_id'    => $ownerId,
                'action_url' => '/neighborhood/rentals',
            ]);
        }

        return [
            'success'        => true,
            'rental_id'      => $rentalId,
            'handover_token' => $handoverToken,
            'return_token'   => $returnToken,
            'message'        => 'Pengajuan pinjam alat berhasil dibuat! Tunjukkan token atau QR Serah Terima kepada petugas/pemilik saat mengambil barang.',
        ];
    }

    /**
     * Konfirmasi Serah Terima Barang (Handover via Token / QR Code)
     * Langsung mencatat Biaya Sewa dan Uang Jaminan ke Ledger Keuangan
     */
    public function confirmHandover(int $actorUserId, int $rentalId, string $token): array
    {
        $rental = $this->rentalModel->find($rentalId);
        if (!$rental) {
            return ['success' => false, 'message' => 'Data peminjaman tidak ditemukan.'];
        }

        if (!in_array($rental['status'], ['requested', 'approved'])) {
            return ['success' => false, 'message' => 'Status peminjaman saat ini tidak dapat diserahterimakan (' . $rental['status'] . ').'];
        }

        if (strtoupper(trim($token)) !== strtoupper(trim($rental['handover_token']))) {
            return ['success' => false, 'message' => 'Token serah terima tidak valid atau salah kode.'];
        }

        $tool = $this->toolModel->find($rental['tool_id']);
        $borrowerId = (int)$rental['borrower_user_id'];
        $today = date('Y-m-d');

        // INTEGRASI LEDGER KEUANGAN (TRANSACTIONS)
        $feeTxId = null;
        $depositTxId = null;

        $borrowerWalletId = $this->walletModel->getDefaultWalletId($borrowerId);

        // 1. Catat Biaya Sewa (Expense bagi peminjam)
        if ((float)$rental['rental_fee'] > 0) {
            try {
                $feeTxId = $this->txModel->insert([
                    'user_id'     => $borrowerId,
                    'wallet_id'   => $borrowerWalletId,
                    'type'        => 'expense',
                    'amount'      => (float)$rental['rental_fee'],
                    'note'        => 'Sewa Alat RT: ' . ($tool['name'] ?? 'Alat Bersama'),
                    'date'        => $today,
                ]);

                // Jika milik warga pribadi, catat income untuk pemilik alat
                if (!empty($tool['owner_user_id']) && (int)$tool['owner_user_id'] !== $borrowerId) {
                    $ownerWalletId = $this->walletModel->getDefaultWalletId((int)$tool['owner_user_id']);
                    $this->txModel->insert([
                        'user_id'   => (int)$tool['owner_user_id'],
                        'wallet_id' => $ownerWalletId,
                        'type'      => 'income',
                        'amount'    => (float)$rental['rental_fee'],
                        'note'      => 'Pendapatan Sewa Alat: ' . ($tool['name'] ?? 'Alat Pribadi'),
                        'date'      => $today,
                    ]);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Ledger Rental Fee Error: ' . $e->getMessage());
            }
        }

        // 2. Catat Uang Jaminan / Deposit Sementara (Expense/Escrow bagi peminjam)
        if ((float)$rental['deposit_amount'] > 0) {
            try {
                $depositTxId = $this->txModel->insert([
                    'user_id'     => $borrowerId,
                    'wallet_id'   => $borrowerWalletId,
                    'type'        => 'expense',
                    'amount'      => (float)$rental['deposit_amount'],
                    'note'        => 'Deposit / Jaminan Pinjam Alat: ' . ($tool['name'] ?? 'Alat Bersama') . ' (Akan dikembalikan saat alat kembali utuh)',
                    'date'        => $today,
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Ledger Deposit Error: ' . $e->getMessage());
            }
        }

        // Update status rental & alat
        $this->rentalModel->update($rentalId, [
            'status'                 => 'borrowed',
            'handover_confirmed_by'  => $actorUserId,
            'fee_transaction_id'     => $feeTxId,
            'deposit_transaction_id' => $depositTxId,
        ]);

        $this->toolModel->update($rental['tool_id'], ['status' => 'borrowed']);

        $this->notificationModel->insert([
            'title'      => '📦 Barang Telah Diserahterimakan',
            'message'    => "Peminjaman {$tool['name']} aktif. Harap mengembalikan barang tepat waktu sebelum {$rental['due_date']}.",
            'type'       => 'info',
            'target'     => 'user',
            'user_id'    => $borrowerId,
            'action_url' => '/neighborhood/rentals',
        ]);

        return [
            'success' => true,
            'message' => 'Serah terima barang berhasil divalidasi! Status alat kini aktif dipinjam dan transaksi keuangan telah dicatat.',
        ];
    }

    /**
     * Konfirmasi Pengembalian Barang (Return via Token / QR Code)
     * Mengembalikan Deposit Uang Jaminan ke Ledger Peminjam secara Otomatis
     */
    public function confirmReturn(int $actorUserId, int $rentalId, string $token, ?string $conditionNote = null): array
    {
        $rental = $this->rentalModel->find($rentalId);
        if (!$rental) {
            return ['success' => false, 'message' => 'Data peminjaman tidak ditemukan.'];
        }

        if ($rental['status'] !== 'borrowed') {
            return ['success' => false, 'message' => 'Status peminjaman bukan sedang dipinjam.'];
        }

        if (strtoupper(trim($token)) !== strtoupper(trim($rental['return_token']))) {
            return ['success' => false, 'message' => 'Token pengembalian tidak valid atau salah kode.'];
        }

        $tool = $this->toolModel->find($rental['tool_id']);
        $borrowerId = (int)$rental['borrower_user_id'];
        $today = date('Y-m-d');
        $refundTxId = null;

        // PENGEMBALIAN DEPOSIT KE KEUANGAN USER (INCOME REFUND)
        if ((float)$rental['deposit_amount'] > 0) {
            try {
                $borrowerWalletId = $this->walletModel->getDefaultWalletId($borrowerId);
                $refundTxId = $this->txModel->insert([
                    'user_id'     => $borrowerId,
                    'wallet_id'   => $borrowerWalletId,
                    'type'        => 'income',
                    'amount'      => (float)$rental['deposit_amount'],
                    'note'        => 'Pengembalian Dana Deposit: Pinjam ' . ($tool['name'] ?? 'Alat Bersama') . ' Selesai',
                    'date'        => $today,
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Ledger Refund Deposit Error: ' . $e->getMessage());
            }
        }

        // Update status rental & alat
        $this->rentalModel->update($rentalId, [
            'status'                => 'returned',
            'actual_return_date'    => date('Y-m-d H:i:s'),
            'return_confirmed_by'   => $actorUserId,
            'refund_transaction_id' => $refundTxId,
            'admin_note'            => $conditionNote,
        ]);

        $this->toolModel->update($rental['tool_id'], [
            'status'         => 'available',
            'condition_note' => $conditionNote ?: ($tool['condition_note'] ?? 'Baik & Berfungsi Normal'),
        ]);

        $this->notificationModel->insert([
            'title'      => '✅ Pengembalian Alat Selesai',
            'message'    => "Terima kasih telah mengembalikan {$tool['name']} dalam kondisi baik. Uang deposit sebesar Rp " . number_format($rental['deposit_amount'], 0, ',', '.') . " telah dicatat kembali ke dompet Anda.",
            'type'       => 'info',
            'target'     => 'user',
            'user_id'    => $borrowerId,
            'action_url' => '/neighborhood/rentals',
        ]);

        return [
            'success' => true,
            'message' => 'Pengembalian barang berhasil divalidasi! Uang deposit telah dikembalikan ke pencatatan saldo peminjam.',
        ];
    }
}
