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

        $this->rentalModel->ensureTable();
        $this->neighborhoodModel->ensureTable();

        $days = max(1, min($rentalDays, (int)($tool['max_rent_days'] ?: 7)));
        $startDate = date('Y-m-d');
        $dueDate   = date('Y-m-d', strtotime("+{$days} days"));

        // Biaya Administrasi Kas RT (Bisa diatur Admin RT, default Rp 2.000)
        $rtFeeAmount = (float)($neighborhood['tool_rental_fee'] ?? 2000.00);

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
            'rt_fee_amount'    => $rtFeeAmount,
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
                'message'    => "{$user['name']} mengajukan pinjam {$tool['name']} ({$days} hari). Biaya Kas RT: Rp " . number_format($rtFeeAmount, 0, ',', '.') . " dibayar tunai ke Pengurus/Ketua RT.",
                'type'       => 'info',
                'target'     => 'user',
                'user_id'    => $ownerId,
                'action_url' => '/neighborhood/tools',
            ]);
        }

        return [
            'success'        => true,
            'rental_id'      => $rentalId,
            'rt_fee_amount'  => $rtFeeAmount,
            'handover_token' => $handoverToken,
            'return_token'   => $returnToken,
            'message'        => 'Pengajuan pinjam alat berhasil dibuat! Biaya Kas RT sebesar Rp ' . number_format($rtFeeAmount, 0, ',', '.') . ' dibayarkan tunai ke Bendahara/Ketua RT untuk dikelola bersama demi kemaslahatan warga.',
        ];
    }

    /**
     * Konfirmasi Serah Terima Barang (Handover via Token / QR Code)
     * Langsung mencatat Biaya Sewa, Uang Jaminan, dan Biaya Kas RT ke Ledger Keuangan
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
        $borrower = $this->userModel->find($borrowerId);
        $borrowerName = $borrower['name'] ?? 'Warga';
        $neighborhood = $this->neighborhoodModel->find($rental['neighborhood_id']);
        $today = date('Y-m-d');

        // INTEGRASI LEDGER KEUANGAN (TRANSACTIONS)
        $feeTxId   = null;
        $depositTxId = null;
        $rtKasTxId = null;

        $borrowerWalletId = $this->walletModel->getDefaultWalletId($borrowerId);

        // 1. Catat Biaya Sewa Milik Pemilik Alat (jika ada)
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

        // 2. Catat Uang Jaminan / Deposit Sementara
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

        // 3. PENCATATAN OTOMATIS KE BUKU KAS RT DARI PEMINJAMAN ALAT (INCOME KAS RT)
        $rtFeeAmount = (float)($rental['rt_fee_amount'] ?? ($neighborhood['tool_rental_fee'] ?? 2000.00));
        $rtWalletId  = (int)($neighborhood['bank_wallet_id'] ?? 0);
        $rtAdminId   = (int)($neighborhood['admin_user_id'] ?? $actorUserId);

        if ($rtFeeAmount > 0 && $rtWalletId > 0) {
            try {
                $rtKasTxId = $this->txModel->insert([
                    'user_id'   => $rtAdminId,
                    'wallet_id' => $rtWalletId,
                    'type'      => 'income',
                    'amount'    => $rtFeeAmount,
                    'note'      => 'Kas RT: Biaya Peminjaman Alat [' . ($tool['name'] ?? 'Alat Warga') . '] oleh ' . $borrowerName . ' (Tunai/Cash)',
                    'date'      => $today,
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Ledger Kas RT Tool Fee Error: ' . $e->getMessage());
            }
        }

        // Update status rental & alat
        $this->rentalModel->update($rentalId, [
            'status'                 => 'borrowed',
            'handover_confirmed_by'  => $actorUserId,
            'fee_transaction_id'     => $feeTxId,
            'deposit_transaction_id' => $depositTxId,
            'rt_fee_transaction_id'  => $rtKasTxId,
        ]);

        $this->toolModel->update($rental['tool_id'], ['status' => 'borrowed']);

        $this->notificationModel->insert([
            'title'      => '📦 Barang Telah Diserahterimakan',
            'message'    => "Peminjaman {$tool['name']} aktif. Biaya kas RT Rp " . number_format($rtFeeAmount, 0, ',', '.') . " telah dibukukan ke Kas RT. Harap kembalikan barang sebelum {$rental['due_date']}.",
            'type'       => 'info',
            'target'     => 'user',
            'user_id'    => $borrowerId,
            'action_url' => '/neighborhood/tools',
        ]);

        return [
            'success' => true,
            'message' => 'Serah terima barang berhasil divalidasi! Status alat kini aktif dipinjam dan iuran Kas RT Rp ' . number_format($rtFeeAmount, 0, ',', '.') . ' otomatis masuk ke pembukuan Kas RT.',
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
            'action_url' => '/neighborhood/tools',
        ]);

        return [
            'success' => true,
            'message' => 'Pengembalian barang berhasil divalidasi! Uang deposit telah dikembalikan ke pencatatan saldo peminjam.',
        ];
    }

    /**
     * Dapatkan Laporan & Rekap Kas RT dari Peminjaman Alat
     */
    public function getNeighborhoodToolKasRecords(int $neighborhoodId): array
    {
        $this->neighborhoodModel->ensureTable();
        $this->rentalModel->ensureTable();

        $neighborhood = $this->neighborhoodModel->find($neighborhoodId);
        $currentFee = (float)($neighborhood['tool_rental_fee'] ?? 2000.00);

        // Ambil semua rental yang sudah diserahterimakan (borrowed atau returned)
        $rentals = $this->rentalModel->select('tool_rentals.*, ct.name AS tool_name, ct.photo AS tool_photo, u.name AS borrower_name, u.house_number AS borrower_house, admin.name AS handover_by_name')
            ->join('community_tools ct', 'ct.id = tool_rentals.tool_id', 'inner')
            ->join('users u', 'u.id = tool_rentals.borrower_user_id', 'inner')
            ->join('users admin', 'admin.id = tool_rentals.handover_confirmed_by', 'left')
            ->where('tool_rentals.neighborhood_id', $neighborhoodId)
            ->whereIn('tool_rentals.status', ['borrowed', 'returned'])
            ->orderBy('tool_rentals.created_at', 'DESC')
            ->findAll();

        $totalKas = 0.0;
        foreach ($rentals as $r) {
            $totalKas += (float)($r['rt_fee_amount'] ?? $currentFee);
        }

        return [
            'current_fee'         => $currentFee,
            'total_kas_collected' => $totalKas,
            'total_rentals_count' => count($rentals),
            'records'             => $rentals,
            'kas_info_note'       => 'Biaya sewa alat sepenuhnya masuk ke Kas RT untuk dikelola bersama demi kemaslahatan dan kepentingan seluruh warga RT.',
        ];
    }

    /**
     * Ubah Pengaturan Tarif Biaya Kas RT per Peminjaman Alat oleh Ketua/Admin RT
     */
    public function updateToolRentalFee(int $actorUserId, int $neighborhoodId, float $newFee): array
    {
        $this->neighborhoodModel->ensureTable();
        $neighborhood = $this->neighborhoodModel->find($neighborhoodId);

        if (!$neighborhood) {
            return ['success' => false, 'message' => 'Data RT tidak ditemukan.'];
        }

        if ((int)$neighborhood['admin_user_id'] !== $actorUserId) {
            $user = $this->userModel->find($actorUserId);
            $role = strtolower(trim((string)($user['role'] ?? '')));
            if (!in_array($role, ['rt_admin', 'admin', 'administrator'], true)) {
                return ['success' => false, 'message' => 'Hanya Ketua RT atau Admin yang dapat mengubah tarif Kas RT.'];
            }
        }

        $cleanFee = max(0, $newFee);
        $this->neighborhoodModel->update($neighborhoodId, [
            'tool_rental_fee' => $cleanFee,
        ]);

        return [
            'success'     => true,
            'new_fee'     => $cleanFee,
            'message'     => 'Tarif Kas RT dari peminjaman alat berhasil diperbarui menjadi Rp ' . number_format($cleanFee, 0, ',', '.') . ' per peminjaman.',
        ];
    }
}
