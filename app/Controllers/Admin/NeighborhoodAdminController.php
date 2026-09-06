<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NeighborhoodModel;
use App\Models\UserModel;
use App\Services\NeighborhoodService;

class NeighborhoodAdminController extends BaseController
{
    protected NeighborhoodModel   $neighborhoodModel;
    protected UserModel           $userModel;
    protected NeighborhoodService $neighborhoodService;

    public function __construct()
    {
        $this->neighborhoodModel   = new NeighborhoodModel();
        $this->userModel           = new UserModel();
        $this->neighborhoodService = new NeighborhoodService();
    }

    /**
     * Halaman Utama Approval & Manajemen RT di Panel Admin
     * GET /admin/neighborhoods
     */
    public function index()
    {
        $this->neighborhoodModel->ensureTable();

        $status = $this->request->getGet('status') ?: 'all';
        $search = trim($this->request->getGet('q') ?? '');

        $builder = $this->neighborhoodModel->select('neighborhoods.*, u.name AS applicant_name, u.email AS applicant_email, u.phone AS applicant_phone')
            ->join('users u', 'u.id = neighborhoods.admin_user_id', 'left')
            ->orderBy("CASE WHEN neighborhoods.status = 'pending' THEN 0 ELSE 1 END", 'ASC')
            ->orderBy('neighborhoods.created_at', 'DESC');

        if ($status !== 'all') {
            $builder->where('neighborhoods.status', $status);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('neighborhoods.name', $search)
                ->orLike('neighborhoods.unique_code', $search)
                ->orLike('neighborhoods.subdistrict', $search)
                ->orLike('neighborhoods.city', $search)
                ->orLike('u.name', $search)
                ->groupEnd();
        }

        $neighborhoods = $builder->findAll();

        $pendingCount = $this->neighborhoodModel->where('status', 'pending')->countAllResults();
        $verifiedCount = $this->neighborhoodModel->where('status', 'verified')->countAllResults();

        $data = [
            'pageTitle'      => 'Approval Pengajuan & Komunitas RT',
            'activeMenu'     => 'neighborhoods',
            'neighborhoods'  => $neighborhoods,
            'pendingCount'   => $pendingCount,
            'verifiedCount'  => $verifiedCount,
            'currentStatus'  => $status,
            'search'         => $search,
        ];

        return view('admin/neighborhoods/index', $data);
    }

    /**
     * Approve Pengajuan RT oleh Superadmin
     * POST /admin/neighborhoods/approve/(:num)
     */
    public function approve(int $id)
    {
        $superadminId = (int)(session()->get('user_id') ?? 1);
        $result = $this->neighborhoodService->approveNeighborhoodBySuperadmin($superadminId, $id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Reject Pengajuan RT oleh Superadmin
     * POST /admin/neighborhoods/reject/(:num)
     */
    public function reject(int $id)
    {
        $superadminId = (int)(session()->get('user_id') ?? 1);
        $reason = trim($this->request->getPost('rejection_reason') ?? '');

        $result = $this->neighborhoodService->rejectNeighborhoodBySuperadmin($superadminId, $id, $reason);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }
}
