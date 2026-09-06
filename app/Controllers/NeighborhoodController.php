<?php

namespace App\Controllers;

use App\Models\NeighborhoodModel;
use App\Models\NeighborhoodVouchModel;
use App\Models\UserModel;
use App\Models\SettingModel;
use App\Services\NeighborhoodService;

class NeighborhoodController extends BaseController
{
    protected NeighborhoodModel      $neighborhoodModel;
    protected NeighborhoodVouchModel $vouchModel;
    protected UserModel              $userModel;
    protected SettingModel           $settingModel;
    protected NeighborhoodService    $neighborhoodService;

    public function __construct()
    {
        $this->neighborhoodModel   = new NeighborhoodModel();
        $this->vouchModel          = new NeighborhoodVouchModel();
        $this->userModel           = new UserModel();
        $this->settingModel        = new SettingModel();
        $this->neighborhoodService = new NeighborhoodService();
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
        $isRtAdmin      = in_array(strtolower(trim((string)($user['role'] ?? ''))), ['rt_admin', 'admin', 'administrator'], true);

        // Jika belum terdaftar di RT, arahkan ke onboarding/join page
        if (!$neighborhood) {
            return redirect()->to('/neighborhood/join');
        }

        $residents = $this->neighborhoodModel->getResidents($neighborhoodId, 'verified');
        $pendingResidents = $isRtAdmin ? $this->neighborhoodModel->getResidents($neighborhoodId, 'pending') : [];

        return view('neighborhood/index', [
            'pageTitle'        => 'Komunitas & Sistem RT — ' . esc($neighborhood['name']),
            'user'             => $user,
            'neighborhood'     => $neighborhood,
            'residents'        => $residents,
            'pendingResidents' => $pendingResidents,
            'isRtAdmin'        => $isRtAdmin,
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

        return view('neighborhood/create', [
            'pageTitle' => 'Daftarkan Lingkungan RT Baru',
            'user'      => $user,
        ]);
    }

    /**
     * Simpan RT Baru (POST /neighborhood/store)
     */
    public function store()
    {
        $userId = session()->get('user_id');
        $post   = $this->request->getPost();

        $rtName = trim($post['name'] ?? '');
        if (empty($rtName) || empty($post['subdistrict']) || empty($post['rt']) || empty($post['rw'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Harap lengkapi semua data wilayah (RT, RW, Kelurahan, Kota, Provinsi).']);
        }

        try {
            $id = $this->neighborhoodService->registerNeighborhood($post, $userId);
            return $this->response->setJSON([
                'success'         => true,
                'message'         => 'Lingkungan RT berhasil didaftarkan!',
                'neighborhood_id' => $id,
                'redirect'        => '/neighborhood',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal mendaftarkan RT: ' . $e->getMessage()]);
        }
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
}
