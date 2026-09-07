<?php

namespace App\Controllers;

use App\Models\ArisanGroupModel;
use App\Models\ArisanMemberModel;
use App\Models\ArisanPaymentModel;
use App\Models\UserModel;

class ArisanController extends BaseController
{
    protected ArisanGroupModel   $groupModel;
    protected ArisanMemberModel  $memberModel;
    protected ArisanPaymentModel $paymentModel;
    protected UserModel          $userModel;

    public function __construct()
    {
        $this->groupModel   = new ArisanGroupModel();
        $this->memberModel  = new ArisanMemberModel();
        $this->paymentModel = new ArisanPaymentModel();
        $this->userModel    = new UserModel();
    }

    /**
     * Daftar kelompok arisan milik / diikuti user.
     * GET /arisan
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $groups = $this->groupModel->getForUser($userId);

        $result = [];
        foreach ($groups as $g) {
            $g['progress'] = $this->groupModel->getProgress((int)$g['id']);
            $result[] = $g;
        }

        return view('arisan/index', [
            'pageTitle' => 'Arisan & Tabungan Kelompok',
            'user'      => $user,
            'groups'    => $result,
            'symbol'    => 'Rp',
        ]);
    }

    /**
     * Detail satu grup arisan.
     * GET /arisan/(:num)
     */
    public function show(int $id)
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $group  = $this->groupModel->getDetailWithMembers($id, $userId);

        if (!$group || !$group['can_access']) {
            return redirect()->to('/arisan')->with('error', 'Grup arisan tidak ditemukan.');
        }

        $group['progress'] = $this->groupModel->getProgress((int)$group['id']);

        return view('arisan/detail', [
            'pageTitle' => $group['name'] . ' — Arisan',
            'user'      => $user,
            'group'     => $group,
            'symbol'    => 'Rp',
        ]);
    }

    /**
     * Buat grup arisan baru.
     * POST /arisan/store
     */
    public function store()
    {
        $userId = (int) session()->get('user_id');
        $post   = $this->request->getPost();

        $name = trim($post['name'] ?? '');
        if (empty($name)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama kelompok arisan wajib diisi.']);
        }

        $amount = $this->parseAmount($post['amount'] ?? '0');
        if ($amount <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nominal arisan harus lebih dari Rp 0.']);
        }

        $frequency = in_array($post['frequency'] ?? '', ['weekly', 'biweekly', 'monthly'], true)
            ? $post['frequency'] : 'monthly';

        $groupId = $this->groupModel->insert([
            'neighborhood_id' => !empty($post['neighborhood_id']) ? (int)$post['neighborhood_id'] : null,
            'user_id'         => $userId,
            'name'            => $name,
            'amount'          => $amount,
            'frequency'       => $frequency,
            'total_members'   => 0,
            'current_round'   => 0,
            'status'          => 'active',
            'start_date'      => $post['start_date'] ?? date('Y-m-d'),
            'description'     => trim($post['description'] ?? ''),
        ]);

        $memberId = $this->memberModel->addMember($groupId, [
            'user_id'      => $userId,
            'member_name'  => trim($post['owner_name'] ?? ''),
            'phone'        => $post['owner_phone'] ?? null,
            'rotation_order' => 1,
        ]);

        $this->groupModel->update($groupId, ['total_members' => 1]);
        $this->paymentModel->insert([
            'group_id'     => $groupId,
            'member_id'    => $memberId,
            'round_number' => 1,
            'amount'       => $amount,
            'status'       => 'unpaid',
        ]);
        $this->groupModel->update($groupId, ['current_round' => 1]);

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Kelompok arisan berhasil dibuat.',
            'group_id' => $groupId,
        ]);
    }

    /**
     * Tambah anggota ke grup arisan.
     * POST /arisan/(:num)/member
     */
    public function addMember(int $id)
    {
        $userId = (int) session()->get('user_id');
        $group  = $this->groupModel->find($id);

        if (!$group || (int)$group['user_id'] !== $userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda bukan pemilik grup arisan ini.']);
        }

        $post       = $this->request->getPost();
        $memberName = trim($post['member_name'] ?? '');
        if (empty($memberName)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama anggota wajib diisi.']);
        }

        $memberId = $this->memberModel->addMember($id, [
            'user_id'     => !empty($post['user_id']) ? (int)$post['user_id'] : null,
            'member_name' => $memberName,
            'phone'       => $post['phone'] ?? null,
        ]);

        $totalMembers = $this->memberModel->where('group_id', $id)->countAllResults();
        $this->groupModel->update($id, ['total_members' => $totalMembers]);

        for ($r = 1; $r <= (int)$group['current_round']; $r++) {
            $exists = $this->paymentModel->where('group_id', $id)
                ->where('member_id', $memberId)
                ->where('round_number', $r)
                ->first();
            if (!$exists) {
                $this->paymentModel->insert([
                    'group_id'     => $id,
                    'member_id'    => $memberId,
                    'round_number' => $r,
                    'amount'       => $group['amount'],
                    'status'       => 'unpaid',
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Anggota berhasil ditambahkan.',
        ]);
    }

    /**
     * Catat setoran anggota.
     * POST /arisan/payment/pay
     */
    public function payPayment()
    {
        $userId = (int) session()->get('user_id');
        $post   = $this->request->getPost();

        $paymentId = (int)($post['payment_id'] ?? 0);
        $groupId   = (int)($post['group_id'] ?? 0);

        $group = $this->groupModel->find($groupId);
        if (!$group) {
            return $this->response->setJSON(['success' => false, 'message' => 'Grup arisan tidak ditemukan.']);
        }

        $isMember = $this->memberModel->where('group_id', $groupId)->where('user_id', $userId)->countAllResults() > 0;
        if ((int)$group['user_id'] !== $userId && !$isMember) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak memiliki akses ke grup ini.']);
        }

        $res = $this->paymentModel->markPaid($paymentId, $groupId);
        return $this->response->setJSON($res);
    }

    /**
     * Lanjut ke putaran arisan berikutnya.
     * POST /arisan/(:num)/advance
     */
    public function advanceRound(int $id)
    {
        $userId = (int) session()->get('user_id');
        $group  = $this->groupModel->find($id);

        if (!$group || (int)$group['user_id'] !== $userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda bukan pemilik grup arisan ini.']);
        }

        $created = $this->groupModel->advanceRound($id);

        return $this->response->setJSON([
            'success'       => true,
            'message'       => 'Putaran baru telah dibuka.',
            'slots_created' => $created,
        ]);
    }
}
