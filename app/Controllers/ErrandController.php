<?php

namespace App\Controllers;

use App\Models\ErrandItemModel;
use App\Models\ErrandModel;
use App\Models\NeighborhoodModel;
use App\Models\UserModel;
use App\Services\ErrandService;

class ErrandController extends BaseController
{
    protected ErrandModel       $errandModel;
    protected ErrandItemModel   $itemModel;
    protected NeighborhoodModel $neighborhoodModel;
    protected UserModel         $userModel;
    protected ErrandService     $errandService;

    public function __construct()
    {
        $this->errandModel       = new ErrandModel();
        $this->itemModel         = new ErrandItemModel();
        $this->neighborhoodModel = new NeighborhoodModel();
        $this->userModel         = new UserModel();
        $this->errandService     = new ErrandService();
    }

    /**
     * Halaman Titip Belanja Antar-Warga
     * GET /neighborhood/errands
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);
        if (!$neighborhoodId) {
            return redirect()->to('/neighborhood/join');
        }

        $errands = $this->errandModel->getErrands($neighborhoodId, ['status' => 'open']);
        $myErrands = $this->errandModel->where('organizer_user_id', $userId)->orderBy('created_at', 'DESC')->findAll();
        $myRequests = $this->itemModel->getItemsForRequester($userId);

        return view('neighborhood/errands/index', [
            'pageTitle'  => 'Titip Belanja Warga',
            'errands'    => $errands,
            'myErrands'  => $myErrands,
            'myRequests' => $myRequests,
            'user'       => $user,
            'symbol'     => 'Rp',
        ]);
    }

    /**
     * Detail Sesi Titip Belanja & Item Titipan
     * GET /neighborhood/errands/{id}
     */
    public function detail(int $id)
    {
        $userId = session()->get('user_id');
        $errand = $this->errandModel->getErrandWithItems($id);

        if (!$errand) {
            return redirect()->to('/neighborhood/errands')->with('error', 'Sesi titip belanja tidak ditemukan.');
        }

        $isOrganizer = (int)$errand['organizer_user_id'] === (int)$userId;

        return view('neighborhood/errands/detail', [
            'pageTitle'   => 'Titip Belanja ke ' . esc($errand['destination_store']),
            'errand'      => $errand,
            'isOrganizer' => $isOrganizer,
            'userId'      => $userId,
            'symbol'      => 'Rp',
        ]);
    }

    /**
     * Buka Sesi Belanja Baru
     * POST /neighborhood/errands/store
     */
    public function store()
    {
        $userId = session()->get('user_id');
        $data   = $this->request->getPost();

        $res = $this->errandService->createErrand($userId, $data);
        return $this->response->setJSON($res);
    }

    /**
     * Titip Barang ke Sesi Belanja
     * POST /neighborhood/errands/item
     */
    public function addItem()
    {
        $userId   = session()->get('user_id');
        $errandId = (int)$this->request->getPost('errand_id');
        $data     = $this->request->getPost();

        $res = $this->errandService->submitItem($userId, $errandId, $data);
        return $this->response->setJSON($res);
    }

    /**
     * Konfirmasi Penyerahan Barang Belanjaan (Handover & Ledger Recording)
     * POST /neighborhood/errands/deliver
     */
    public function deliverItem()
    {
        $organizerId = session()->get('user_id');
        $itemId      = (int)$this->request->getPost('item_id');
        $token       = trim($this->request->getPost('handover_token') ?? '');
        $actualPrice = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('actual_price') ?? '0');

        // Foto struk belanja jika ada
        $receiptPhoto = null;
        $file = $this->request->getFile('receipt_photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/errands/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $receiptPhoto = 'errand_' . uniqid() . '.' . $file->getClientExtension();
            $file->move($uploadDir, $receiptPhoto);
        }

        $res = $this->errandService->deliverItem($organizerId, $itemId, $token, $actualPrice, $receiptPhoto ? '/uploads/errands/' . $receiptPhoto : null);
        return $this->response->setJSON($res);
    }
}
