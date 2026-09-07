<?php

namespace App\Controllers;

use App\Models\NeighborhoodModel;
use App\Models\NeighborhoodVouchModel;
use App\Models\UserModel;
use App\Models\SettingModel;
use App\Models\CommunityToolModel;
use App\Models\IuranConfigModel;
use App\Models\IuranPaymentModel;
use App\Services\NeighborhoodService;

class NeighborhoodController extends BaseController
{
    protected NeighborhoodModel      $neighborhoodModel;
    protected NeighborhoodVouchModel $vouchModel;
    protected UserModel              $userModel;
    protected SettingModel           $settingModel;
    protected CommunityToolModel     $toolModel;
    protected NeighborhoodService    $neighborhoodService;
    protected IuranConfigModel       $iuranConfigModel;
    protected IuranPaymentModel      $iuranPaymentModel;

    public function __construct()
    {
        $this->neighborhoodModel   = new NeighborhoodModel();
        $this->vouchModel          = new NeighborhoodVouchModel();
        $this->userModel           = new UserModel();
        $this->settingModel        = new SettingModel();
        $this->toolModel           = new CommunityToolModel();
        $this->neighborhoodService = new NeighborhoodService();
        $this->iuranConfigModel    = new IuranConfigModel();
        $this->iuranPaymentModel   = new IuranPaymentModel();
    }

    /**
     * Dashboard Utama Lingkungan RT / Neighborhood Hub
     * GET /neighborhood
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);
        $neighborhood   = $neighborhoodId ? $this->neighborhoodModel->getWithDetails($neighborhoodId) : null;
        $userRole       = strtolower(trim((string)($user['role'] ?? '')));
        $isRtAdmin      = in_array($userRole, ['rt_admin', 'admin', 'administrator'], true);
        $isTreasurer    = in_array($userRole, ['rt_treasurer', 'bendahara'], true);
        $canManageKas   = $this->neighborhoodService->canManageKasOrAgenda($neighborhoodId, $userId);

        // Jika belum terdaftar di RT, arahkan ke onboarding/join page
        if (!$neighborhood) {
            return redirect()->to('/neighborhood/join');
        }

        $residents        = $this->neighborhoodModel->getResidents($neighborhoodId, 'verified');
        $pendingResidents = $isRtAdmin ? $this->neighborhoodModel->getResidents($neighborhoodId, 'pending') : [];

        // Kas RT data & Laporan Keuangan
        $kasData = $this->neighborhoodService->getKasData($neighborhoodId);

        // Agenda Kegiatan Warga
        $activitiesData = $this->neighborhoodService->getActivitiesData($neighborhoodId);

        // Pengumuman RT
        $announcements = $this->neighborhoodService->getAnnouncementsData($neighborhoodId, 15);

        // Forum Diskusi Warga
        $discussions = $this->neighborhoodService->getDiscussionsData($neighborhoodId, 30);

        // Data Alat RT
        $toolsCount = $this->toolModel->where('neighborhood_id', $neighborhoodId)->where('status !=', 'retired')->countAllResults();
        $toolsAvailable = $this->toolModel->where('neighborhood_id', $neighborhoodId)->where('status', 'available')->countAllResults();
        $toolsRented = $this->toolModel->where('neighborhood_id', $neighborhoodId)->where('status', 'rented')->countAllResults();

        return view('neighborhood/index', [
            'pageTitle'        => 'Komunitas & Sistem RT — ' . esc($neighborhood['name']),
            'user'             => $user,
            'neighborhood'     => $neighborhood,
            'residents'        => $residents,
            'pendingResidents' => $pendingResidents,
            'isRtAdmin'        => $isRtAdmin,
            'isTreasurer'      => $isTreasurer,
            'canManageKas'     => $canManageKas,
            'kasSummary'       => $kasData['summary'],
            'kasLedger'        => $kasData['ledger'],
            'activities'       => $activitiesData['upcoming'],
            'allActivities'    => $activitiesData['all'],
            'announcements'    => $announcements,
            'discussions'      => $discussions,
            'toolsCount'       => $toolsCount,
            'toolsAvailable'   => $toolsAvailable,
            'toolsRented'      => $toolsRented,
            'symbol'           => 'Rp',
        ]);
    }

    /**
     * Halaman Gabung ke RT / Masukkan Kode Unik RT
     * GET /neighborhood/join
     */
    public function join()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        $currentRt = !empty($user['neighborhood_id']) ? $this->neighborhoodModel->find($user['neighborhood_id']) : null;

        return view('neighborhood/join', [
            'pageTitle' => 'Bergabung ke Komunitas RT',
            'user'      => $user,
            'currentRt' => $currentRt,
        ]);
    }

    /**
     * Proses Gabung ke RT (POST /neighborhood/join)
     */
    public function processJoin()
    {
        $userId = session()->get('user_id');
        $code   = trim($this->request->getPost('unique_code') ?? '');
        $status = $this->request->getPost('residence_status') ?: 'permanent';
        $house  = trim($this->request->getPost('house_number') ?? '');

        if (empty($code)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kode unik RT wajib diisi.']);
        }

        $result = $this->neighborhoodService->joinNeighborhood($userId, $code, $status, $house);
        return $this->response->setJSON($result);
    }

    /**
     * Halaman Daftarkan RT Baru (Top-Down / Inisiasi Pengurus)
     * GET /neighborhood/create
     */
    public function create()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        // Cek jika pengguna sudah memiliki pengajuan RT yang pending
        $existingRt = null;
        if (!empty($user['neighborhood_id'])) {
            $existingRt = $this->neighborhoodModel->find($user['neighborhood_id']);
        }

        return view('neighborhood/create', [
            'pageTitle'  => 'Daftarkan Lingkungan RT Baru',
            'user'       => $user,
            'existingRt' => $existingRt,
        ]);
    }

    /**
     * Simpan RT Baru (POST /neighborhood/store)
     */
    public function store()
    {
        $userId = session()->get('user_id');
        $post   = $this->request->getPost();
        $isAjax = $this->request->isAJAX() 
               || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest' 
               || str_contains($this->request->getHeaderLine('Accept'), 'application/json');

        $rtName = trim($post['name'] ?? '');
        if (empty($rtName) || empty($post['subdistrict']) || empty($post['rt']) || empty($post['rw'])) {
            $msg = 'Harap lengkapi semua data wilayah (RT, RW, Kelurahan, Kota, Provinsi).';
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }

        // Handle File Upload Surat Pengesahan/Penunjukan (SK)
        $skDocumentPath = null;
        $skFile = $this->request->getFile('sk_document');
        if ($skFile && $skFile->isValid() && !$skFile->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/rt_sk/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $newName = $skFile->getRandomName();
            $skFile->move($uploadDir, $newName);
            $skDocumentPath = '/uploads/rt_sk/' . $newName;
        }

        try {
            $id = $this->neighborhoodService->registerNeighborhood($post, $userId, $skDocumentPath);
            $msg = 'Pengajuan RT berhasil dikirim! Menunggu verifikasi dokumen SK oleh Admin Master.';
            
            if ($isAjax) {
                return $this->response->setJSON([
                    'success'         => true,
                    'message'         => $msg,
                    'neighborhood_id' => $id,
                    'redirect'        => '/neighborhood/join',
                ]);
            }

            return redirect()->to('/neighborhood/join')->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'Gagal mendaftarkan RT: ' . $e->getMessage();
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => $err]);
            }
            return redirect()->back()->withInput()->with('error', $err);
        }
    }

    /**
     * Catat Transaksi Kas RT (POST /neighborhood/kas/store)
     */
    public function storeKas()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        if (!$neighborhoodId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda belum terdaftar di RT mana pun.']);
        }

        $post = $this->request->getPost();

        // Handle foto bukti/struk kas jika ada
        $receiptPath = null;
        $file = $this->request->getFile('receipt_photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/kas_rt/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $newName = 'kas_' . uniqid() . '.' . $file->getClientExtension();
            $file->move($uploadDir, $newName);
            $receiptPath = '/uploads/kas_rt/' . $newName;
        }

        $result = $this->neighborhoodService->recordKas($neighborhoodId, $userId, $post, $receiptPath);
        return $this->response->setJSON($result);
    }

    /**
     * Hapus Transaksi Kas RT (POST /neighborhood/kas/delete/(:num))
     */
    public function deleteKas(int $kasId)
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $result = $this->neighborhoodService->deleteKas($neighborhoodId, $userId, $kasId);
        return $this->response->setJSON($result);
    }

    /**
     * Tambah Agenda Kegiatan RT (POST /neighborhood/activity/store)
     */
    public function storeActivity()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        if (!$neighborhoodId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda belum terdaftar di RT mana pun.']);
        }

        $post   = $this->request->getPost();
        $result = $this->neighborhoodService->createActivity($neighborhoodId, $userId, $post);
        return $this->response->setJSON($result);
    }

    /**
     * Hapus Agenda Kegiatan RT (POST /neighborhood/activity/delete/(:num))
     */
    public function deleteActivity(int $activityId)
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $result = $this->neighborhoodService->deleteActivity($neighborhoodId, $userId, $activityId);
        return $this->response->setJSON($result);
    }

    /**
     * Ubah Jabatan Warga (Ketua RT mengubah warga jadi Bendahara / Warga biasa)
     * POST /neighborhood/member/role
     */
    public function changeMemberRole()
    {
        $adminUserId    = session()->get('user_id');
        $user           = $this->userModel->find($adminUserId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $targetUserId = (int)$this->request->getPost('target_user_id');
        $role         = trim($this->request->getPost('role') ?? 'user');

        $result = $this->neighborhoodService->setResidentRole($neighborhoodId, $adminUserId, $targetUserId, $role);
        return $this->response->setJSON($result);
    }

    /**
     * Approval Queue (Persetujuan Warga oleh Ketua RT)
     * POST /neighborhood/resident/verify
     */
    public function verifyResident()
    {
        $adminUserId = session()->get('user_id');
        $targetId    = (int)$this->request->getPost('target_user_id');
        $action      = $this->request->getPost('action'); // 'approve' or 'reject'
        $notes       = $this->request->getPost('notes');

        $res = $this->neighborhoodService->verifyResident($adminUserId, $targetId, $action, $notes);
        return $this->response->setJSON($res);
    }

    /**
     * Vouching / Jaminan Tetangga
     * POST /neighborhood/resident/vouch
     */
    public function vouchResident()
    {
        $voucherUserId = session()->get('user_id');
        $targetId      = (int)$this->request->getPost('target_user_id');
        $notes         = $this->request->getPost('notes');

        $res = $this->neighborhoodService->vouchForNeighbor($voucherUserId, $targetId, $notes);
        return $this->response->setJSON($res);
    }

    /**
     * Buat Pengumuman RT (POST /neighborhood/announcement/store)
     */
    public function storeAnnouncement()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        if (!$neighborhoodId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda belum terdaftar di RT mana pun.']);
        }

        $post = $this->request->getPost();
        $res  = $this->neighborhoodService->createAnnouncement($neighborhoodId, $userId, $post);
        return $this->response->setJSON($res);
    }

    /**
     * Hapus Pengumuman RT (POST /neighborhood/announcement/delete/(:num))
     */
    public function deleteAnnouncement(int $id)
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $res = $this->neighborhoodService->deleteAnnouncement($neighborhoodId, $userId, $id);
        return $this->response->setJSON($res);
    }

    /**
     * Buat Postingan Forum Diskusi Warga (POST /neighborhood/discussion/store)
     */
    public function storeDiscussion()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        if (!$neighborhoodId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda belum terdaftar di RT mana pun.']);
        }

        $post = $this->request->getPost();
        $res  = $this->neighborhoodService->createDiscussion($neighborhoodId, $userId, $post);
        return $this->response->setJSON($res);
    }

    /**
     * Hapus Postingan Diskusi Warga (POST /neighborhood/discussion/delete/(:num))
     */
    public function deleteDiscussion(int $id)
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $res = $this->neighborhoodService->deleteDiscussion($neighborhoodId, $userId, $id);
        return $this->response->setJSON($res);
    }

    /**
     * Ambil Detail Diskusi Beserta Komentar (GET /neighborhood/discussion/(:num))
     */
    public function showDiscussion(int $id)
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $disc = $this->neighborhoodService->getDiscussionDetailData($neighborhoodId, $id);
        if (!$disc) {
            return $this->response->setJSON(['success' => false, 'message' => 'Diskusi tidak ditemukan.']);
        }

        return $this->response->setJSON(['success' => true, 'discussion' => $disc]);
    }

    /**
     * Tambah Komentar pada Diskusi (POST /neighborhood/discussion/(:num)/comment)
     */
    public function storeDiscussionComment(int $id)
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $comment  = (string)$this->request->getPost('comment');
        $parentId = $this->request->getPost('parent_id') ? (int)$this->request->getPost('parent_id') : null;
        $res      = $this->neighborhoodService->addDiscussionComment($neighborhoodId, $userId, $id, $comment, $parentId);
        return $this->response->setJSON($res);
    }

    /**
     * Hapus Komentar Diskusi (POST /neighborhood/discussion/comment/delete/(:num))
     */
    public function deleteDiscussionComment(int $id)
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $res = $this->neighborhoodService->deleteDiscussionComment($neighborhoodId, $userId, $id);
        return $this->response->setJSON($res);
    }

    /**
     * Halaman Iuran & Kas RT Web
     * GET /neighborhood/iuran
     */
    public function iuran()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        if (!$neighborhoodId) {
            return redirect()->to('/neighborhood/join');
        }

        $neighborhood = $this->neighborhoodModel->getWithDetails($neighborhoodId);
        $canManage    = $this->neighborhoodService->canManageKasOrAgenda($neighborhoodId, $userId);
        $config       = $this->iuranConfigModel->getActiveConfig($neighborhoodId);

        $residents   = $this->neighborhoodModel->getResidents($neighborhoodId, 'verified');
        $residentIds = array_map(fn($r) => (int)$r['id'], $residents);

        $periodMonth = date('Y-m');
        $payments    = [];
        $summary     = null;

        if ($config) {
            if ($canManage && $residentIds) {
                $this->iuranPaymentModel->ensurePeriodRows(
                    $neighborhoodId, $periodMonth, $residentIds,
                    (int)$config['id'], (float)$config['amount']
                );
            }
            $summary  = $this->iuranPaymentModel->getSummary($neighborhoodId, $periodMonth, (int)$config['id']);
            $payments = $this->iuranPaymentModel->getForPeriod($neighborhoodId, $periodMonth, (int)$config['id']);
        }

        $history = $this->iuranConfigModel->getHistory($neighborhoodId);

        return view('neighborhood/iuran', [
            'pageTitle'    => 'Iuran & Kas RT — ' . esc($neighborhood['name']),
            'user'         => $user,
            'neighborhood' => $neighborhood,
            'config'       => $config,
            'canManage'    => $canManage,
            'periodMonth'  => $periodMonth,
            'summary'      => $summary,
            'payments'     => $payments,
            'residents'    => $residents,
            'history'      => $history,
            'symbol'       => 'Rp',
        ]);
    }

    /**
     * Simpan Konfigurasi Iuran Web
     * POST /neighborhood/iuran/config
     */
    public function iuranConfig()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        if (!$neighborhoodId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda belum terdaftar di RT mana pun.']);
        }
        if (!$this->neighborhoodService->canManageKasOrAgenda($neighborhoodId, $userId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya Ketua RT atau Bendahara yang dapat mengatur iuran.']);
        }

        $post       = $this->request->getPost();
        $periodType = in_array($post['period_type'] ?? '', ['monthly', 'weekly', 'yearly'], true)
            ? $post['period_type'] : 'monthly';
        $amount = $this->parseAmount($post['amount'] ?? '0');

        if ($amount <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nominal iuran harus lebih dari Rp 0.']);
        }

        $id = $this->iuranConfigModel->setActive($neighborhoodId, [
            'period_type' => $periodType,
            'amount'      => $amount,
            'description' => trim($post['description'] ?? 'Iuran Warga Bulanan'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Konfigurasi iuran berhasil diperbarui.',
            'config_id' => $id,
        ]);
    }

    /**
     * Catat Pembayaran Iuran Web
     * POST /neighborhood/iuran/pay
     */
    public function iuranPay()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        if (!$neighborhoodId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda belum terdaftar di RT mana pun.']);
        }

        $post      = $this->request->getPost();
        $paymentId = (int)($post['payment_id'] ?? 0);

        if (!$paymentId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Catatan iuran tidak valid.']);
        }

        $res = $this->iuranPaymentModel->markPaid($paymentId, $userId, $post);
        return $this->response->setJSON($res);
    }
}
