<?php

namespace App\Controllers\Api;

use App\Models\CommunityToolModel;
use App\Models\ToolRentalModel;
use App\Models\UserModel;
use App\Services\ToolRentalService;

class CommunityToolController extends ApiController
{
    protected CommunityToolModel $toolModel;
    protected ToolRentalModel    $rentalModel;
    protected UserModel          $userModel;
    protected ToolRentalService  $rentalService;

    public function __construct()
    {
        $this->toolModel     = new CommunityToolModel();
        $this->rentalModel   = new ToolRentalModel();
        $this->userModel     = new UserModel();
        $this->rentalService = new ToolRentalService();
    }

    /**
     * Daftar Alat Bersama RT
     * GET /api/neighborhood/tools
     */
    public function index()
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);

        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);
        if (!$neighborhoodId) {
            return $this->fail('Anda belum terdaftar di RT.');
        }

        $category = $this->request->getGet('category');
        $status   = $this->request->getGet('status');
        $search   = $this->request->getGet('search');

        $tools = $this->toolModel->getTools($neighborhoodId, [
            'category' => $category,
            'status'   => $status,
            'search'   => $search,
        ]);

        return $this->ok([
            'tools'      => $tools,
            'categories' => array_keys(CommunityToolModel::getCategories()),
        ]);
    }

    /**
     * Tambah Alat ke Inventaris RT
     * POST /api/neighborhood/tools/store
     */
    public function store()
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);

        $json = $this->request->getJSON(true) ?? [];

        $name = trim($json['name'] ?? '');
        if (empty($name)) {
            return $this->fail('Nama alat wajib diisi.');
        }

        $toolId = $this->toolModel->insert([
            'neighborhood_id' => $neighborhoodId,
            'owner_user_id'   => !empty($json['is_personal']) ? $userId : null,
            'name'            => $name,
            'category'        => $json['category'] ?? 'Pertukangan',
            'description'     => trim($json['description'] ?? ''),
            'photo'           => $json['photo'] ?? null,
            'status'          => 'available',
            'rental_fee'      => (float)($json['rental_fee'] ?? 0),
            'deposit_amount'  => (float)($json['deposit_amount'] ?? 0),
            'max_rent_days'   => (int)($json['max_rent_days'] ?? 3),
            'condition_note'  => trim($json['condition_note'] ?? 'Baik & Siap Pakai'),
        ]);

        return $this->ok([
            'message' => 'Alat berhasil ditambahkan ke katalog RT!',
            'tool_id' => $toolId,
        ]);
    }

    /**
     * Ajukan Pinjam Alat
     * POST /api/neighborhood/tools/rent
     */
    public function rent()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];

        $toolId = (int)($json['tool_id'] ?? 0);
        $days   = (int)($json['rental_days'] ?? 1);
        $note   = $json['borrower_note'] ?? null;

        $res = $this->rentalService->requestRental($userId, $toolId, $days, $note);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Konfirmasi Serah Terima (Handover) via Token / Scan QR
     * POST /api/neighborhood/tools/handover
     */
    public function handover()
    {
        $actorId = $this->uid();
        $json    = $this->request->getJSON(true) ?? [];

        $rentalId = (int)($json['rental_id'] ?? 0);
        $token    = trim($json['handover_token'] ?? '');

        $res = $this->rentalService->confirmHandover($actorId, $rentalId, $token);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Konfirmasi Pengembalian (Return) via Token / Scan QR
     * POST /api/neighborhood/tools/return
     */
    public function returnTool()
    {
        $actorId = $this->uid();
        $json    = $this->request->getJSON(true) ?? [];

        $rentalId      = (int)($json['rental_id'] ?? 0);
        $token         = trim($json['return_token'] ?? '');
        $conditionNote = $json['condition_note'] ?? null;

        $res = $this->rentalService->confirmReturn($actorId, $rentalId, $token, $conditionNote);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Riwayat Pinjaman Saya
     * GET /api/neighborhood/tools/my-rentals
     */
    public function myRentals()
    {
        $userId = $this->uid();
        $rentals = $this->rentalModel->getRentalsForBorrower($userId);
        return $this->ok(['rentals' => $rentals]);
    }
}
