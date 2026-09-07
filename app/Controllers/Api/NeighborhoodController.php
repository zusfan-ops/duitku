<?php

namespace App\Controllers\Api;

use App\Models\NeighborhoodModel;
use App\Models\NeighborhoodVouchModel;
use App\Models\UserModel;
use App\Models\CommunityToolModel;
use App\Services\NeighborhoodService;

class NeighborhoodController extends ApiController
{
    protected NeighborhoodModel      $neighborhoodModel;
    protected NeighborhoodVouchModel $vouchModel;
    protected UserModel              $userModel;
    protected CommunityToolModel     $toolModel;
    protected NeighborhoodService    $neighborhoodService;

    public function __construct()
    {
        $this->neighborhoodModel   = new NeighborhoodModel();
        $this->vouchModel          = new NeighborhoodVouchModel();
        $this->userModel           = new UserModel();
        $this->toolModel           = new CommunityToolModel();
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
        $userRole     = strtolower(trim((string)($user['role'] ?? '')));
        $isRtAdmin    = in_array($userRole, ['rt_admin', 'admin', 'administrator'], true);
        $isTreasurer  = in_array($userRole, ['rt_treasurer', 'bendahara'], true);
        $canManageKas = $this->neighborhoodService->canManageKasOrAgenda($neighborhoodId, $userId);
        $pending      = $isRtAdmin ? $this->neighborhoodModel->getResidents($neighborhoodId, 'pending') : [];

        // Kas RT & Agenda Kegiatan
        $kasData        = $this->neighborhoodService->getKasData($neighborhoodId);
        $activitiesData = $this->neighborhoodService->getActivitiesData($neighborhoodId);

        // Pengumuman & Forum Diskusi
        $announcements  = $this->neighborhoodService->getAnnouncementsData($neighborhoodId, 15);
        $discussions    = $this->neighborhoodService->getDiscussionsData($neighborhoodId, 30);

        return $this->ok([
            'joined'              => true,
            'neighborhood'        => $neighborhood,
            'residents'           => $residents,
            'pending_residents'   => $pending,
            'is_rt_admin'         => $isRtAdmin,
            'is_treasurer'        => $isTreasurer,
            'can_manage_kas'      => $canManageKas,
            'verification_status' => $user['rt_verification_status'] ?? 'unregistered',
            'kas_summary'         => $kasData['summary'],
            'kas_ledger'          => $kasData['ledger'],
            'upcoming_activities' => $activitiesData['upcoming'],
            'all_activities'      => $activitiesData['all'],
            'announcements'       => $announcements,
            'discussions'         => $discussions,
            'tools_count'         => $toolsCount,
            'tools_available'     => $toolsAvailable,
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
     * Catat Kas RT (Pemasukan / Pengeluaran)
     * POST /api/neighborhood/kas/store
     */
    public function storeKas()
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        if (!$neighborhoodId) {
            return $this->fail('Anda belum terhubung ke komunitas RT mana pun.');
        }

        $post = $this->request->getPost();
        if (empty($post)) {
            $post = $this->request->getJSON(true) ?? [];
        }

        $receiptPath = null;
        if (!empty($post['receipt_photo_base64'])) {
            $rawB64 = $post['receipt_photo_base64'];
            $ext = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $rawB64, $type)) {
                $rawB64 = substr($rawB64, strpos($rawB64, ',') + 1);
                $ext = strtolower($type[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
            }
            $decoded = base64_decode($rawB64);
            if ($decoded !== false) {
                $uploadDir = FCPATH . 'uploads/kas_rt/';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
                $filename = 'kas_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                file_put_contents($uploadDir . $filename, $decoded);
                $receiptPath = '/uploads/kas_rt/' . $filename;
            }
        }

        $res = $this->neighborhoodService->recordKas($neighborhoodId, $userId, $post, $receiptPath);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Hapus Catatan Kas RT
     * POST /api/neighborhood/kas/delete/(:num)
     */
    public function deleteKas(int $kasId)
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $res = $this->neighborhoodService->deleteKas($neighborhoodId, $userId, $kasId);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Tambah Agenda Kegiatan RT
     * POST /api/neighborhood/activity/store
     */
    public function storeActivity()
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        if (!$neighborhoodId) {
            return $this->fail('Anda belum terhubung ke komunitas RT mana pun.');
        }

        $json = $this->request->getJSON(true) ?? $this->request->getPost();
        $res  = $this->neighborhoodService->createActivity($neighborhoodId, $userId, $json);

        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Hapus Agenda Kegiatan RT
     * POST /api/neighborhood/activity/delete/(:num)
     */
    public function deleteActivity(int $activityId)
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $res = $this->neighborhoodService->deleteActivity($neighborhoodId, $userId, $activityId);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Ubah Jabatan Warga (Jadikan Bendahara / Warga)
     * POST /api/neighborhood/member/role
     */
    public function changeMemberRole()
    {
        $adminUserId = $this->uid();
        $user        = $this->userModel->find($adminUserId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $json         = $this->request->getJSON(true) ?? $this->request->getPost();
        $targetUserId = (int)($json['target_user_id'] ?? 0);
        $role         = trim($json['role'] ?? 'user');

        $res = $this->neighborhoodService->setResidentRole($neighborhoodId, $adminUserId, $targetUserId, $role);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
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

    /**
     * Buat Pengumuman RT
     * POST /api/neighborhood/announcement/store
     */
    public function storeAnnouncement()
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        if (!$neighborhoodId) {
            return $this->fail('Anda belum terhubung ke komunitas RT.');
        }

        $json = $this->request->getJSON(true) ?? $this->request->getPost();
        $res  = $this->neighborhoodService->createAnnouncement($neighborhoodId, $userId, $json);

        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Hapus Pengumuman RT
     * POST /api/neighborhood/announcement/delete/(:num)
     */
    public function deleteAnnouncement(int $id)
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $res = $this->neighborhoodService->deleteAnnouncement($neighborhoodId, $userId, $id);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Daftar Postingan Forum Diskusi RT
     * GET /api/neighborhood/discussions
     */
    public function discussions()
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        if (!$neighborhoodId) {
            return $this->fail('Anda belum terhubung ke komunitas RT.');
        }

        $discussions = $this->neighborhoodService->getDiscussionsData($neighborhoodId, 50);
        return $this->ok(['discussions' => $discussions]);
    }

    /**
     * Buat Postingan Forum Diskusi RT
     * POST /api/neighborhood/discussion/store
     */
    public function storeDiscussion()
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        if (!$neighborhoodId) {
            return $this->fail('Anda belum terhubung ke komunitas RT.');
        }

        $json = $this->request->getJSON(true) ?? $this->request->getPost();
        $res  = $this->neighborhoodService->createDiscussion($neighborhoodId, $userId, $json);

        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Hapus Postingan Forum Diskusi RT
     * POST /api/neighborhood/discussion/delete/(:num)
     */
    public function deleteDiscussion(int $id)
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $res = $this->neighborhoodService->deleteDiscussion($neighborhoodId, $userId, $id);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Detail Postingan Forum Diskusi RT & Komentar
     * GET /api/neighborhood/discussion/(:num)
     */
    public function showDiscussion(int $id)
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $disc = $this->neighborhoodService->getDiscussionDetailData($neighborhoodId, $id);
        if (!$disc) {
            return $this->fail('Diskusi tidak ditemukan.');
        }

        return $this->ok(['discussion' => $disc]);
    }

    /**
     * Kirim Komentar pada Postingan Diskusi
     * POST /api/neighborhood/discussion/(:num)/comment
     */
    public function storeDiscussionComment(int $id)
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $json    = $this->request->getJSON(true) ?? $this->request->getPost();
        $comment = trim($json['comment'] ?? '');

        $res = $this->neighborhoodService->addDiscussionComment($neighborhoodId, $userId, $id, $comment);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Hapus Komentar pada Postingan Diskusi
     * POST /api/neighborhood/discussion/comment/delete/(:num)
     */
    public function deleteDiscussionComment(int $id)
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $res = $this->neighborhoodService->deleteDiscussionComment($neighborhoodId, $userId, $id);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }
}
