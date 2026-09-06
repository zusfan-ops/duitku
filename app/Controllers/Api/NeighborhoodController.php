<?php

namespace App\Controllers\Api;

use App\Models\NeighborhoodModel;
use App\Models\NeighborhoodVouchModel;
use App\Models\UserModel;
use App\Services\NeighborhoodService;

class NeighborhoodController extends ApiController
{
    protected NeighborhoodModel      $neighborhoodModel;
    protected NeighborhoodVouchModel $vouchModel;
    protected UserModel              $userModel;
    protected NeighborhoodService    $neighborhoodService;

    public function __construct()
    {
        $this->neighborhoodModel   = new NeighborhoodModel();
        $this->vouchModel          = new NeighborhoodVouchModel();
        $this->userModel           = new UserModel();
        $this->neighborhoodService = new NeighborhoodService();
    }

    /**
     * Detail Komunitas RT User Saat Ini
     * GET /api/neighborhood
     */
    public function index()
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);

        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);
        if (!$neighborhoodId) {
            return $this->ok([
                'joined'        => false,
                'neighborhood'  => null,
                'message'       => 'Anda belum terhubung ke komunitas RT mana pun.',
            ]);
        }

        $neighborhood = $this->neighborhoodModel->getWithDetails($neighborhoodId);
        $residents    = $this->neighborhoodModel->getResidents($neighborhoodId, 'verified');
        $isRtAdmin    = in_array(strtolower(trim((string)($user['role'] ?? ''))), ['rt_admin', 'admin', 'administrator'], true);
        $pending      = $isRtAdmin ? $this->neighborhoodModel->getResidents($neighborhoodId, 'pending') : [];

        return $this->ok([
            'joined'            => true,
            'neighborhood'      => $neighborhood,
            'residents'         => $residents,
            'pending_residents' => $pending,
            'is_rt_admin'       => $isRtAdmin,
            'verification_status' => $user['rt_verification_status'] ?? 'unregistered',
        ]);
    }

    /**
     * Daftarkan RT Baru
     * POST /api/neighborhood/register
     */
    public function registerRt()
    {
        $userId = $this->uid();
        $post   = $this->request->getPost();
        if (empty($post)) {
            $post = $this->request->getJSON(true) ?? [];
        }

        if (empty($post['subdistrict']) || empty($post['rt']) || empty($post['rw'])) {
            return $this->fail('Data wilayah tidak lengkap (RT, RW, Kelurahan/Desa wajib diisi).');
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
        } elseif (!empty($post['sk_document_base64']) || (!empty($post['sk_document']) && is_string($post['sk_document']) && str_starts_with($post['sk_document'], 'data:image'))) {
            $rawB64 = $post['sk_document_base64'] ?? $post['sk_document'];
            $ext = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $rawB64, $type)) {
                $rawB64 = substr($rawB64, strpos($rawB64, ',') + 1);
                $ext = strtolower($type[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
            }
            $decoded = base64_decode($rawB64);
            if ($decoded !== false) {
                $uploadDir = FCPATH . 'uploads/rt_sk/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = 'sk_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                file_put_contents($uploadDir . $filename, $decoded);
                $skDocumentPath = '/uploads/rt_sk/' . $filename;
            }
        }

        try {
            $id = $this->neighborhoodService->registerNeighborhood($post, $userId, $skDocumentPath);
            return $this->ok([
                'status'          => 'pending',
                'message'         => 'Pengajuan RT berhasil dikirim dan sedang ditinjau Superadmin!',
                'neighborhood_id' => $id,
            ]);
        } catch (\Throwable $e) {
            return $this->fail('Gagal mendaftarkan RT: ' . $e->getMessage());
        }
    }

    /**
     * Gabung ke RT via Kode Unik
     * POST /api/neighborhood/join
     */
    public function join()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];

        $code   = trim($json['unique_code'] ?? '');
        $status = $json['residence_status'] ?? 'permanent';
        $house  = $json['house_number'] ?? null;

        if (empty($code)) {
            return $this->fail('Kode unik RT wajib diisi.');
        }

        $result = $this->neighborhoodService->joinNeighborhood($userId, $code, $status, $house);
        if (!$result['success']) {
            return $this->fail($result['message']);
        }

        return $this->ok($result);
    }

    /**
     * Verifikasi Warga (Approve/Reject) oleh Ketua RT
     * POST /api/neighborhood/resident/verify
     */
    public function verifyResident()
    {
        $adminUserId = $this->uid();
        $json        = $this->request->getJSON(true) ?? [];

        $targetId = (int)($json['target_user_id'] ?? 0);
        $action   = $json['action'] ?? 'approve';
        $notes    = $json['notes'] ?? null;

        $res = $this->neighborhoodService->verifyResident($adminUserId, $targetId, $action, $notes);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Penjamin (Vouch) untuk Tetangga
     * POST /api/neighborhood/resident/vouch
     */
    public function vouchResident()
    {
        $voucherUserId = $this->uid();
        $json          = $this->request->getJSON(true) ?? [];

        $targetId = (int)($json['target_user_id'] ?? 0);
        $notes    = $json['notes'] ?? null;

        $res = $this->neighborhoodService->vouchForNeighbor($voucherUserId, $targetId, $notes);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }
}
