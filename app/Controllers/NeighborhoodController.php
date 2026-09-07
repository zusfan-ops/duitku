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
