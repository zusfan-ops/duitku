<?php

namespace App\Controllers\Api;

use App\Models\ErrandItemModel;
use App\Models\ErrandModel;
use App\Models\UserModel;
use App\Services\ErrandService;

class ErrandController extends ApiController
{
    protected ErrandModel       $errandModel;
    protected ErrandItemModel   $itemModel;
    protected UserModel         $userModel;
    protected ErrandService     $errandService;

    public function __construct()
    {
        $this->errandModel   = new ErrandModel();
        $this->itemModel     = new ErrandItemModel();
        $this->userModel     = new UserModel();
        $this->errandService = new ErrandService();
    }

    /**
     * Daftar Sesi Titip Belanja di RT
     * GET /api/neighborhood/errands
     */
    public function index()
    {
        $userId = $this->uid();
        $user   = $this->userModel->find($userId);

        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);
        if (!$neighborhoodId) {
            return $this->fail('Anda belum terdaftar di RT.');
        }

        $errands = $this->errandModel->getErrands($neighborhoodId, ['status' => 'open']);
        return $this->ok(['errands' => $errands]);
    }

    /**
     * Detail Sesi Titip Belanja
     * GET /api/neighborhood/errands/(:num)
     */
    public function show(int $id)
    {
        $errand = $this->errandModel->getErrandWithItems($id);
        if (!$errand) {
            return $this->fail('Sesi titip belanja tidak ditemukan.');
        }

        return $this->ok(['errand' => $errand]);
    }

    /**
     * Buka Sesi Belanja Baru
     * POST /api/neighborhood/errands/store
     */
    public function store()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];

        $res = $this->errandService->createErrand($userId, $json);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Titip Barang
     * POST /api/neighborhood/errands/item
     */
    public function addItem()
    {
        $userId   = $this->uid();
        $json     = $this->request->getJSON(true) ?? [];
        $errandId = (int)($json['errand_id'] ?? 0);

        $res = $this->errandService->submitItem($userId, $errandId, $json);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Konfirmasi Penyerahan Barang (Handover & Ledger Recording)
     * POST /api/neighborhood/errands/deliver
     */
    public function deliverItem()
    {
        $organizerId = $this->uid();
        $json        = $this->request->getJSON(true) ?? [];

        $itemId      = (int)($json['item_id'] ?? 0);
        $token       = trim($json['handover_token'] ?? '');
        $actualPrice = (float)($json['actual_price'] ?? 0);
        $receipt     = $json['receipt_photo'] ?? null;

        $res = $this->errandService->deliverItem($organizerId, $itemId, $token, $actualPrice, $receipt);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok($res);
    }

    /**
     * Daftar Titipan Saya
     * GET /api/neighborhood/errands/my-requests
     */
    public function myRequests()
    {
        $userId = $this->uid();
        $requests = $this->itemModel->getItemsForRequester($userId);
        return $this->ok(['requests' => $requests]);
    }
}
