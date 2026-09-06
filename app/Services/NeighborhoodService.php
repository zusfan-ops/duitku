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
    protected WalletModel            $walletModel;
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
     * Daftarkan / Ajukan RT Baru Dilengkapi Nomor SK & Dokumen Pengesahan dari RW/Kelurahan
     */
    public function registerNeighborhood(array $data, int $applicantUserId, ?string $skDocumentPath = null): int
    {
        $this->neighborhoodModel->ensureTable();

        $uniqueCode = !empty($data['unique_code'])
            ? strtoupper(trim($data['unique_code']))
            : NeighborhoodModel::generateUniqueCode($data['rt'] ?? '01', $data['rw'] ?? '01', $data['subdistrict'] ?? 'Wilayah');

        $qrJoinToken = bin2hex(random_bytes(16));

        // Buat Dompet Kas RT default untuk RT
        $walletId = null;
        try {
            $walletId = $this->walletModel->insert([
                'user_id'         => $applicantUserId,
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

        $rtName = !empty($data['name']) ? trim($data['name']) : ('RT ' . ($data['rt'] ?? '') . ' RW ' . ($data['rw'] ?? ''));

        // Status awal pengajuan RT adalah PENDING menunggu approval superadmin
        $neighborhoodId = (int)$this->neighborhoodModel->insert([
            'name'                      => $rtName,
            'province'                  => $data['province'] ?? '',
            'city'                      => $data['city'] ?? '',
            'district'                  => $data['district'] ?? '',
            'subdistrict'               => $data['subdistrict'] ?? '',
            'rw'                        => $data['rw'] ?? '',
            'rt'                        => $data['rt'] ?? '',
            'unique_code'               => $uniqueCode,
            'status'                    => 'pending',
            'sk_number'                 => !empty($data['sk_number']) ? trim($data['sk_number']) : null,
            'sk_document_path'          => $skDocumentPath,
            'qr_join_token'             => $qrJoinToken,
            'admin_user_id'             => $applicantUserId,
            'bank_wallet_id'            => $walletId,
            'address_note'              => $data['address_note'] ?? null,
            'auto_approval'             => !empty($data['auto_approval']) ? 1 : 0,
            'max_borrow_limit_domisili' => (float)($data['max_borrow_limit_domisili'] ?? 250000),
        ]);

        // Tandai pengguna pemohon sebagai pending RT admin
        $this->userModel->update($applicantUserId, [
            'neighborhood_id'        => $neighborhoodId,
            'residence_status'       => 'permanent',
            'rt_verification_status' => 'pending',
        ]);

        // Kirim Notifikasi ke Admin Master bahwa ada pengajuan RT baru masuk
        $applicant = $this->userModel->find($applicantUserId);
        $applicantName = $applicant['name'] ?? 'Pengguna';

        try {
            $this->notificationModel->insert([
                'title'      => 'Pengajuan RT Baru Menunggu Approval 🏛️',
                'message'    => "Pemohon {$applicantName} mengajukan pendaftaran {$rtName} (SK: " . ($data['sk_number'] ?? 'Terlampir') . "). Silakan verifikasi berkas di Admin Panel.",
                'type'       => 'system',
                'target'     => 'all',
                'action_url' => '/admin/neighborhoods',
                'is_pinned'  => 0,
            ]);
        } catch (\Throwable $e) {}

        return $neighborhoodId;
    }

    /**
     * Persetujuan / Approval RT Baru oleh Superadmin Aplikasi
     */
    public function approveNeighborhoodBySuperadmin(int $superadminId, int $neighborhoodId): array
    {
        $this->neighborhoodModel->ensureTable();
        $rt = $this->neighborhoodModel->find($neighborhoodId);
        if (!$rt) {
            return ['success' => false, 'message' => 'Data RT tidak ditemukan.'];
        }

        $now = date('Y-m-d H:i:s');

        $this->neighborhoodModel->update($neighborhoodId, [
            'status'           => 'verified',
            'verified_at'      => $now,
            'verified_by'      => $superadminId,
            'rejection_reason' => null,
        ]);

        // Angkat pemohon menjadi rt_admin resmi
        $adminUserId = (int)$rt['admin_user_id'];
        if ($adminUserId > 0) {
            $this->userModel->update($adminUserId, [
                'role'                   => 'rt_admin',
                'neighborhood_id'        => $neighborhoodId,
                'rt_verification_status' => 'verified',
                'rt_verified_at'         => $now,
                'rt_verified_by'         => $superadminId,
            ]);

            // Kirim notifikasi selamat ke Ketua RT
            try {
                $this->notificationModel->insert([
                    'title'      => 'Selamat! Pengajuan RT Anda Telah Disetujui 👑',
                    'message'    => "Pengajuan lingkungan {$rt['name']} telah disetujui Superadmin. Kode Unik RT: {$rt['unique_code']}. Anda dapat mulai mengundang warga dan mengelola lingkungan RT.",
                    'type'       => 'neighborhood_verified',
                    'target'     => 'user',
                    'user_id'    => $adminUserId,
                    'action_url' => '/neighborhood',
                    'is_pinned'  => 1,
                ]);
            } catch (\Throwable $e) {}
        }

        return ['success' => true, 'message' => "RT {$rt['name']} berhasil disetujui dan diaktifkan."];
    }

    /**
     * Penolakan RT Baru oleh Superadmin Aplikasi
     */
    public function rejectNeighborhoodBySuperadmin(int $superadminId, int $neighborhoodId, ?string $reason = null): array
    {
        $this->neighborhoodModel->ensureTable();
        $rt = $this->neighborhoodModel->find($neighborhoodId);
        if (!$rt) {
            return ['success' => false, 'message' => 'Data RT tidak ditemukan.'];
        }

        $this->neighborhoodModel->update($neighborhoodId, [
            'status'           => 'rejected',
            'rejection_reason' => $reason ?: 'Dokumen SK / bukti penunjukan RT tidak valid.',
        ]);

        $adminUserId = (int)$rt['admin_user_id'];
        if ($adminUserId > 0) {
            $this->userModel->update($adminUserId, [
                'rt_verification_status' => 'rejected',
            ]);

            try {
                $this->notificationModel->insert([
                    'title'      => 'Pengajuan RT Ditolak ⚠️',
                    'message'    => "Mohon maaf, pengajuan RT {$rt['name']} ditolak oleh Admin. Alasan: " . ($reason ?: 'Dokumen penunjukan tidak valid.'),
                    'type'       => 'neighborhood_rejected',
                    'target'     => 'user',
                    'user_id'    => $adminUserId,
                    'action_url' => '/neighborhood/create',
                    'is_pinned'  => 0,
                ]);
            } catch (\Throwable $e) {}
        }

        return ['success' => true, 'message' => "Pengajuan RT {$rt['name']} telah ditolak."];
    }

    /**
     * Warga Mengajukan Gabung ke Kawasan RT
     */
    public function joinNeighborhood(int $userId, string $uniqueCode, string $residenceStatus = 'permanent', ?string $houseNumber = null): array
    {
        $this->neighborhoodModel->ensureTable();

        $neighborhood = $this->neighborhoodModel->findByUniqueCode($uniqueCode);
        if (!$neighborhood) {
            return ['success' => false, 'message' => 'Kode unik RT tidak ditemukan. Silakan periksa kembali kode dari Ketua RT Anda.'];
        }

        // Cek status RT
        if (($neighborhood['status'] ?? 'pending') !== 'verified') {
            return ['success' => false, 'message' => 'Lingkungan RT ini belum aktif atau masih dalam proses peninjauan SK oleh Admin Master.'];
        }

        $resStatus = in_array($residenceStatus, ['permanent', 'temporary']) ? $residenceStatus : 'permanent';

        // Jika auto_approval aktif di RT tersebut, langsung verified
        $verificationStatus = (!empty($neighborhood['auto_approval'])) ? 'verified' : 'pending';

        $this->userModel->update($userId, [
            'neighborhood_id'        => (int)$neighborhood['id'],
            'residence_status'       => $resStatus,
            'rt_verification_status' => $verificationStatus,
            'house_number'           => $houseNumber,
            'rt_verified_at'         => ($verificationStatus === 'verified') ? date('Y-m-d H:i:s') : null,
            'rt_verified_by'         => ($verificationStatus === 'verified') ? (int)($neighborhood['admin_user_id'] ?? 0) : null,
        ]);

        $user = $this->userModel->find($userId);
        $userName = $user['name'] ?? 'Warga';

        // JIKA BUTUH APPROVAL: Kirim Notifikasi langsung ke aplikasi Ketua RT
        if ($verificationStatus === 'pending') {
            $rtAdminId = (int)($neighborhood['admin_user_id'] ?? 0);
            if ($rtAdminId > 0) {
                try {
                    $this->notificationModel->insert([
                        'title'      => 'Permintaan Warga Baru 👥',
                        'message'    => "{$userName} (No. Rumah: " . ($houseNumber ?: '-') . ", Status: " . ($resStatus === 'permanent' ? 'Tetap' : 'Domisili') . ") mengajukan diri untuk bergabung ke RT Anda. Ketuk untuk meninjau dan menyetujui.",
                        'type'       => 'rt_resident_request',
                        'target'     => 'user',
                        'user_id'    => $rtAdminId,
                        'action_url' => '/neighborhood',
                        'is_pinned'  => 0,
                    ]);
                } catch (\Throwable $e) {}
            }

            return [
                'success' => true,
                'status'  => 'pending',
                'message' => 'Pengajuan berhasil dikirim! Notifikasi telah diteruskan ke Ketua RT Anda untuk persetujuan.',
            ];
        }

        return [
            'success' => true,
            'status'  => 'verified',
            'message' => 'Selamat, Anda telah resmi bergabung ke ' . $neighborhood['name'] . '!',
        ];
    }

    /**
     * Verifikasi Permintaan Warga oleh Ketua RT (Approve / Reject)
     */
    public function verifyResident(int $rtAdminUserId, int $targetUserId, string $action, ?string $notes = null): array
    {
        $admin = $this->userModel->find($rtAdminUserId);
        $targetUser = $this->userModel->find($targetUserId);

        if (!$admin || !$targetUser) {
            return ['success' => false, 'message' => 'Pengguna tidak ditemukan.'];
        }

        if ((int)$admin['neighborhood_id'] !== (int)$targetUser['neighborhood_id']) {
            return ['success' => false, 'message' => 'Anda tidak memiliki hak akses verifikasi untuk warga di luar RT Anda.'];
        }

        $now = date('Y-m-d H:i:s');
        $rt = $this->neighborhoodModel->find($admin['neighborhood_id']);
        $rtName = $rt['name'] ?? 'Komunitas RT';

        if ($action === 'approve') {
            $this->userModel->update($targetUserId, [
                'rt_verification_status' => 'verified',
                'rt_verified_at'         => $now,
                'rt_verified_by'         => $rtAdminUserId,
            ]);

            try {
                $this->notificationModel->insert([
                    'title'      => 'Pendaftaran Warga Disetujui 🏠',
                    'message'    => "Selamat! Ketua RT telah menyetujui akun Anda sebagai warga di {$rtName}. Anda sekarang dapat meminjam alat dan mengikuti kegiatan warga.",
                    'type'       => 'resident_approved',
                    'target'     => 'user',
                    'user_id'    => $targetUserId,
                    'action_url' => '/neighborhood',
                    'is_pinned'  => 0,
                ]);
            } catch (\Throwable $e) {}

            return ['success' => true, 'message' => 'Warga berhasil disetujui dan diaktifkan.'];
        }

        if ($action === 'reject') {
            $this->userModel->update($targetUserId, [
                'rt_verification_status' => 'rejected',
                'neighborhood_id'        => null,
            ]);

            try {
                $this->notificationModel->insert([
                    'title'      => 'Pengajuan Warga Ditolak ⚠️',
                    'message'    => "Pengajuan Anda bergabung ke {$rtName} ditolak oleh Ketua RT." . ($notes ? " Alasan: {$notes}" : ""),
                    'type'       => 'resident_rejected',
                    'target'     => 'user',
                    'user_id'    => $targetUserId,
                    'action_url' => '/neighborhood/join',
                    'is_pinned'  => 0,
                ]);
            } catch (\Throwable $e) {}

            return ['success' => true, 'message' => 'Pengajuan warga telah ditolak.'];
        }

        return ['success' => false, 'message' => 'Aksi verifikasi tidak valid.'];
    }

    /**
     * Vouching / Jaminan Tetangga
     */
    public function vouchForNeighbor(int $voucherUserId, int $targetUserId, ?string $notes = null): array
    {
        $voucher = $this->userModel->find($voucherUserId);
        $target  = $this->userModel->find($targetUserId);

        if (!$voucher || !$target) {
            return ['success' => false, 'message' => 'Pengguna tidak ditemukan.'];
        }

        if ($voucher['rt_verification_status'] !== 'verified') {
            return ['success' => false, 'message' => 'Hanya warga yang sudah terverifikasi yang dapat menjadi penjamin tetangga.'];
        }

        if ((int)$voucher['neighborhood_id'] !== (int)$target['neighborhood_id']) {
            return ['success' => false, 'message' => 'Anda hanya dapat menjamin tetangga di satu RT yang sama.'];
        }

        $neighborhoodId = (int)$voucher['neighborhood_id'];

        $existing = $this->vouchModel->where([
            'target_user_id'  => $targetUserId,
            'voucher_user_id' => $voucherUserId,
        ])->first();

        if ($existing) {
            return ['success' => false, 'message' => 'Anda sudah pernah memberikan jaminan untuk tetangga ini.'];
        }

        $this->vouchModel->insert([
            'neighborhood_id' => $neighborhoodId,
            'target_user_id'  => $targetUserId,
            'voucher_user_id' => $voucherUserId,
            'status'          => 'approved',
            'notes'           => $notes,
        ]);

        // Cek jika sudah mencapai batas minimal vouching (misal: 2 warga)
        $vouchCount = $this->vouchModel->where('target_user_id', $targetUserId)->where('status', 'approved')->countAllResults();
        if ($vouchCount >= 2 && $target['rt_verification_status'] === 'pending') {
            $this->userModel->update($targetUserId, [
                'rt_verification_status' => 'verified',
                'rt_verified_at'         => date('Y-m-d H:i:s'),
                'rt_verified_by'         => $voucherUserId,
            ]);

            return [
                'success' => true,
                'message' => 'Jaminan berhasil diberikan! Target warga kini telah aktif otomatis karena telah dijamin oleh minimal 2 tetangga.',
            ];
        }

        return [
            'success' => true,
            'message' => "Jaminan berhasil dicatat ({$vouchCount}/2 penjamin). Butuh " . max(0, 2 - $vouchCount) . " penjamin lagi untuk aktivasi otomatis.",
        ];
    }
}
