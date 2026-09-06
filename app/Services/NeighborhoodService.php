<?php

namespace App\Services;

use App\Models\NeighborhoodModel;
use App\Models\NeighborhoodVouchModel;
use App\Models\UserModel;
use App\Models\WalletModel;
use App\Models\NotificationModel;

class NeighborhoodService
{
    protected NeighborhoodModel      $neighborhoodModel;
    protected NeighborhoodVouchModel $vouchModel;
    protected UserModel              $userModel;
    protected WalletModel           $walletModel;
    protected NotificationModel      $notificationModel;
    protected FcmService             $fcmService;

    public function __construct()
    {
        $this->neighborhoodModel = new NeighborhoodModel();
        $this->vouchModel        = new NeighborhoodVouchModel();
        $this->userModel         = new UserModel();
        $this->walletModel       = new WalletModel();
        $this->notificationModel = new NotificationModel();
        $this->fcmService        = new FcmService();
    }

    /**
     * Daftarkan RT Baru (Bisa oleh Platform Admin atau Calon Ketua RT)
     */
    public function registerNeighborhood(array $data, int $adminUserId): int
    {
        $uniqueCode = !empty($data['unique_code'])
            ? strtoupper(trim($data['unique_code']))
            : NeighborhoodModel::generateUniqueCode($data['rt'] ?? '01', $data['rw'] ?? '01', $data['subdistrict'] ?? 'Wilayah');

        $qrJoinToken = bin2hex(random_bytes(16));

        // Buat Dompet Kas RT jika belum ada
        $walletId = null;
        try {
            $walletId = $this->walletModel->insert([
                'user_id'         => $adminUserId,
                'name'            => 'Kas RT ' . ($data['rt'] ?? '') . ' / RW ' . ($data['rw'] ?? ''),
                'type'            => 'cash',
                'icon'            => '🏛️',
                'color'           => '#0F766E',
                'initial_balance' => 0,
                'is_default'      => 0,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error creating RT wallet: ' . $e->getMessage());
        }

        $neighborhoodId = $this->neighborhoodModel->insert([
            'name'                      => $data['name'] ?? ('RT ' . ($data['rt'] ?? '') . ' RW ' . ($data['rw'] ?? '')),
            'province'                  => $data['province'],
            'city'                      => $data['city'],
            'district'                  => $data['district'],
            'subdistrict'               => $data['subdistrict'],
            'rw'                        => $data['rw'],
            'rt'                        => $data['rt'],
            'unique_code'               => $uniqueCode,
            'qr_join_token'             => $qrJoinToken,
            'admin_user_id'             => $adminUserId,
            'bank_wallet_id'            => $walletId,
            'address_note'              => $data['address_note'] ?? null,
            'auto_approval'             => !empty($data['auto_approval']) ? 1 : 0,
            'max_borrow_limit_domisili' => (float)($data['max_borrow_limit_domisili'] ?? 250000),
        ]);

        // Berikan role rt_admin kepada pengguna pembuat/ketua RT
        $this->userModel->update($adminUserId, [
            'neighborhood_id'        => $neighborhoodId,
            'role'                   => 'rt_admin',
            'residence_status'       => 'permanent',
            'rt_verification_status' => 'verified',
            'rt_verified_at'         => date('Y-m-d H:i:s'),
            'rt_verified_by'         => $adminUserId,
        ]);

        return $neighborhoodId;
    }

    /**
     * Warga Mengajukan Gabung ke RT (via Kode Unik RT atau Pilihan Wilayah)
     */
    public function joinNeighborhood(int $userId, string $uniqueCode, string $residenceStatus = 'permanent', ?string $houseNumber = null): array
    {
        $neighborhood = $this->neighborhoodModel->findByUniqueCode($uniqueCode);
        if (!$neighborhood) {
            return ['success' => false, 'message' => 'Kode unik RT tidak ditemukan. Silakan pastikan kode yang diberikan oleh Ketua RT sudah benar.'];
        }

        $resStatus = in_array($residenceStatus, ['permanent', 'temporary']) ? $residenceStatus : 'permanent';

        // Jika auto_approval aktif di RT tersebut, langsung verified
        $verificationStatus = (!empty($neighborhood['auto_approval'])) ? 'verified' : 'pending';
        $verifiedAt = ($verificationStatus === 'verified') ? date('Y-m-d H:i:s') : null;

        $this->userModel->update($userId, [
            'neighborhood_id'        => $neighborhood['id'],
            'residence_status'       => $resStatus,
            'rt_verification_status' => $verificationStatus,
            'house_number'           => trim((string)$houseNumber) ?: null,
            'rt_verified_at'         => $verifiedAt,
            'rt_verified_by'         => ($verificationStatus === 'verified') ? $neighborhood['admin_user_id'] : null,
        ]);

        $user = $this->userModel->find($userId);
        $adminId = (int)$neighborhood['admin_user_id'];

        // Jika butuh approval, kirim notifikasi ke Ketua RT
        if ($verificationStatus === 'pending' && $adminId) {
            $residentTypeLabel = ($resStatus === 'permanent') ? 'Warga Tetap (KTP)' : 'Warga Domisili/Kontrak';
            $houseLabel = $houseNumber ? " di rumah No. {$houseNumber}" : '';

            $this->notificationModel->insert([
                'title'      => '📋 Pengajuan Warga Baru: ' . ($user['name'] ?? 'Warga'),
                'message'    => "{$user['name']} mengajukan bergabung ke {$neighborhood['name']} sebagai {$residentTypeLabel}{$houseLabel}.",
                'type'       => 'info',
                'target'     => 'user',
                'user_id'    => $adminId,
                'action_url' => '/neighborhood/approval-queue',
            ]);

            try {
                if ($this->fcmService->isConfigured()) {
                    $this->fcmService->sendToTopic(
                        "user_{$adminId}",
                        '📋 Pengajuan Warga Baru: ' . ($user['name'] ?? 'Warga'),
                        "{$user['name']} mendaftar sebagai {$residentTypeLabel}{$houseLabel}. Buka antrean persetujuan RT.",
                        ['type' => 'rt_approval_queue', 'neighborhood_id' => (string)$neighborhood['id']]
                    );
                }
            } catch (\Throwable $e) {
                // Ignore FCM non-fatal error
            }
        }

        return [
            'success'                => true,
            'status'                 => $verificationStatus,
            'neighborhood'           => $neighborhood,
            'message'                => ($verificationStatus === 'verified')
                ? 'Selamat! Anda telah langsung terhubung dengan komunitas RT ' . $neighborhood['name'] . '.'
                : 'Pengajuan Anda telah dikirim ke Ketua RT. Mohon menunggu konfirmasi persetujuan dari pengurus RT.',
        ];
    }

    /**
     * Ketua RT Menyetujui atau Menolak Warga di Antrean
     */
    public function verifyResident(int $adminUserId, int $targetUserId, string $action, ?string $notes = null): array
    {
        $admin = $this->userModel->find($adminUserId);
        $adminRole = strtolower(trim((string)($admin['role'] ?? 'user')));
        if (!in_array($adminRole, ['rt_admin', 'admin', 'administrator'])) {
            return ['success' => false, 'message' => 'Anda tidak memiliki hak akses verifikasi RT.'];
        }

        $targetUser = $this->userModel->find($targetUserId);
        if (!$targetUser || (int)$targetUser['neighborhood_id'] !== (int)$admin['neighborhood_id']) {
            return ['success' => false, 'message' => 'Data warga tidak ditemukan di lingkungan RT Anda.'];
        }

        if ($action === 'approve') {
            $this->userModel->update($targetUserId, [
                'rt_verification_status' => 'verified',
                'rt_verified_at'         => date('Y-m-d H:i:s'),
                'rt_verified_by'         => $adminUserId,
            ]);

            $this->notificationModel->insert([
                'title'      => '✅ Akun Warga RT Disetujui!',
                'message'    => 'Selamat! Ketua RT telah memverifikasi akun Anda. Sekarang Anda dapat meminjam alat warga dan menggunakan layanan titip belanja.',
                'type'       => 'info',
                'target'     => 'user',
                'user_id'    => $targetUserId,
                'action_url' => '/neighborhood',
            ]);

            return ['success' => true, 'message' => 'Warga berhasil diverifikasi dan disetujui.'];
        } else {
            $this->userModel->update($targetUserId, [
                'rt_verification_status' => 'rejected',
            ]);

            $this->notificationModel->insert([
                'title'      => '❌ Pengajuan Bergabung RT Ditolak',
                'message'    => 'Pengajuan bergabung RT Anda belum disetujui. Alasan/Catatan: ' . ($notes ?: 'Data tidak sesuai.'),
                'type'       => 'warning',
                'target'     => 'user',
                'user_id'    => $targetUserId,
                'action_url' => '/neighborhood/join',
            ]);

            return ['success' => true, 'message' => 'Pengajuan warga telah ditolak.'];
        }
    }

    /**
     * Sistem Penjamin (Vouching) oleh Sesama Warga Terverifikasi
     */
    public function vouchForNeighbor(int $voucherUserId, int $targetUserId, ?string $notes = null): array
    {
        $voucher = $this->userModel->find($voucherUserId);
        $target  = $this->userModel->find($targetUserId);

        if (!$voucher || ($voucher['rt_verification_status'] ?? '') !== 'verified') {
            return ['success' => false, 'message' => 'Hanya warga yang sudah terverifikasi yang dapat menjadi penjamin tetangga.'];
        }

        if (!$target || (int)$target['neighborhood_id'] !== (int)$voucher['neighborhood_id']) {
            return ['success' => false, 'message' => 'Warga yang dijamin harus berada di lingkungan RT yang sama.'];
        }

        if ((int)$voucherUserId === (int)$targetUserId) {
            return ['success' => false, 'message' => 'Anda tidak dapat menjamin akun Anda sendiri.'];
        }

        // Cek apakah sudah pernah vouch
        $existing = $this->vouchModel->where('target_user_id', $targetUserId)
                                     ->where('voucher_user_id', $voucherUserId)
                                     ->first();
        if ($existing) {
            return ['success' => false, 'message' => 'Anda sudah memberikan jaminan untuk warga ini sebelumnya.'];
        }

        $this->vouchModel->insert([
            'neighborhood_id' => $voucher['neighborhood_id'],
            'target_user_id'  => $targetUserId,
            'voucher_user_id' => $voucherUserId,
            'status'          => 'approved',
            'notes'           => $notes,
        ]);

        // Cek apakah sudah mencapai kuota vouching otomatis (minimal 2 penjamin terverifikasi)
        $approvedCount = $this->vouchModel->countApprovedVouches($targetUserId);
        if ($approvedCount >= 2 && ($target['rt_verification_status'] ?? '') !== 'verified') {
            $this->userModel->update($targetUserId, [
                'rt_verification_status' => 'verified',
                'rt_verified_at'         => date('Y-m-d H:i:s'),
                'rt_verified_by'         => $voucherUserId,
            ]);

            $this->notificationModel->insert([
                'title'      => '🎉 Akun Aktif via Jaminan Tetangga!',
                'message'    => 'Akun Anda telah diverifikasi otomatis berkat jaminan dari 2 tetangga di lingkungan RT Anda.',
                'type'       => 'info',
                'target'     => 'user',
                'user_id'    => $targetUserId,
                'action_url' => '/neighborhood',
            ]);
        }

        return ['success' => true, 'message' => 'Jaminan untuk tetangga berhasil disimpan. Total jaminan: ' . $approvedCount];
    }
}
