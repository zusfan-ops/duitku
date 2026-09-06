<?php

namespace App\Controllers;

use App\Models\CommunityToolModel;
use App\Models\NeighborhoodModel;
use App\Models\ToolRentalModel;
use App\Models\UserModel;
use App\Services\ToolRentalService;

class CommunityToolController extends BaseController
{
    protected CommunityToolModel $toolModel;
    protected ToolRentalModel    $rentalModel;
    protected NeighborhoodModel  $neighborhoodModel;
    protected UserModel          $userModel;
    protected ToolRentalService  $rentalService;

    public function __construct()
    {
        $this->toolModel         = new CommunityToolModel();
        $this->rentalModel       = new ToolRentalModel();
        $this->neighborhoodModel = new NeighborhoodModel();
        $this->userModel         = new UserModel();
        $this->rentalService     = new ToolRentalService();
    }

    /**
     * Katalog Alat Bersama & Sharing Warga
     * GET /neighborhood/tools
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);
        if (!$neighborhoodId) {
            return redirect()->to('/neighborhood/join');
        }

        $category = $this->request->getGet('category');
        $status   = $this->request->getGet('status');
        $search   = $this->request->getGet('search');

        $tools   = $this->toolModel->getTools($neighborhoodId, [
            'category' => $category,
            'status'   => $status,
            'search'   => $search,
        ]);

        $myRentals   = $this->rentalModel->getRentalsForBorrower($userId);
        $isRtAdmin   = in_array(strtolower(trim((string)($user['role'] ?? ''))), ['rt_admin', 'admin', 'administrator'], true);
        $kasSummary  = $this->rentalService->getNeighborhoodToolKasRecords($neighborhoodId);
        $neighborhood = $this->neighborhoodModel->find($neighborhoodId);

        return view('neighborhood/tools/index', [
            'pageTitle'        => 'Pinjam Alat Bersama RT',
            'tools'            => $tools,
            'myRentals'        => $myRentals,
            'categories'       => CommunityToolModel::getCategories(),
            'user'             => $user,
            'isRtAdmin'        => $isRtAdmin,
            'selectedCategory' => $category ?: 'Semua',
            'kasSummary'       => $kasSummary,
            'toolRentalFee'    => (float)($neighborhood['tool_rental_fee'] ?? 2000.00),
            'neighborhood'     => $neighborhood,
            'symbol'           => 'Rp',
        ]);
    }

    /**
     * Tambah Alat Baru ke Inventaris RT
     * POST /neighborhood/tools/store
     */
    public function store()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $name        = trim($this->request->getPost('name') ?? '');
        $category    = $this->request->getPost('category') ?: 'Pertukangan';
        $rentalFee   = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('rental_fee') ?? '0');
        $deposit     = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('deposit_amount') ?? '0');
        $maxDays     = (int)($this->request->getPost('max_rent_days') ?: 3);
        $description = trim($this->request->getPost('description') ?? '');
        $isPersonal  = !empty($this->request->getPost('is_personal'));

        if (empty($name)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama alat wajib diisi.']);
        }

        // Upload foto alat jika ada
        $photoName = null;
        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/tools/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $photoName = 'tool_' . uniqid() . '.' . $file->getClientExtension();
            $file->move($uploadDir, $photoName);
        }

        $toolId = $this->toolModel->insert([
            'neighborhood_id' => $neighborhoodId,
            'owner_user_id'   => $isPersonal ? $userId : null,
            'name'            => $name,
            'category'        => $category,
            'description'     => $description,
            'photo'           => $photoName ? '/uploads/tools/' . $photoName : null,
            'status'          => 'available',
            'rental_fee'      => $rentalFee,
            'deposit_amount'  => $deposit,
            'max_rent_days'   => $maxDays,
            'condition_note'  => trim($this->request->getPost('condition_note') ?? 'Baik & Siap Pakai'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Alat berhasil ditambahkan ke katalog RT!',
            'tool_id' => $toolId,
        ]);
    }

    /**
     * Ajukan Peminjaman Alat
     * POST /neighborhood/tools/rent
     */
    public function rent()
    {
        $userId = session()->get('user_id');
        $toolId = (int)$this->request->getPost('tool_id');
        $days   = (int)($this->request->getPost('rental_days') ?: 1);
        $note   = $this->request->getPost('borrower_note');

        $result = $this->rentalService->requestRental($userId, $toolId, $days, $note);
        return $this->response->setJSON($result);
    }

    /**
     * Validasi Serah Terima (Handover via Token / Scan QR)
     * POST /neighborhood/tools/handover
     */
    public function handover()
    {
        $actorId  = session()->get('user_id');
        $rentalId = (int)$this->request->getPost('rental_id');
        $token    = trim($this->request->getPost('handover_token') ?? '');

        $result = $this->rentalService->confirmHandover($actorId, $rentalId, $token);
        return $this->response->setJSON($result);
    }

    /**
     * Validasi Pengembalian Alat (Return via Token / Scan QR)
     * POST /neighborhood/tools/return
     */
    public function returnTool()
    {
        $actorId       = session()->get('user_id');
        $rentalId      = (int)$this->request->getPost('rental_id');
        $token         = trim($this->request->getPost('return_token') ?? '');
        $conditionNote = $this->request->getPost('condition_note');

        $result = $this->rentalService->confirmReturn($actorId, $rentalId, $token, $conditionNote);
        return $this->response->setJSON($result);
    }

    /**
     * Update Tarif Kas RT Peminjaman Alat oleh RT Admin
     * POST /neighborhood/tools/fee-setting
     */
    public function updateFee()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);
        $newFee = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('tool_rental_fee') ?? '2000');

        $result = $this->rentalService->updateToolRentalFee($userId, $neighborhoodId, $newFee);
        return $this->response->setJSON($result);
    }
}
