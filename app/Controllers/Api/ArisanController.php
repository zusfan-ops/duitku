<?php

namespace App\Controllers\Api;

use App\Models\ArisanGroupModel;
use App\Models\ArisanMemberModel;
use App\Models\ArisanPaymentModel;

class ArisanController extends ApiController
{
    protected ArisanGroupModel   $groupModel;
    protected ArisanMemberModel  $memberModel;
    protected ArisanPaymentModel $paymentModel;

    public function __construct()
    {
        $this->groupModel   = new ArisanGroupModel();
        $this->memberModel  = new ArisanMemberModel();
        $this->paymentModel = new ArisanPaymentModel();
    }

    /**
     * Daftar grup arisan milik / diikuti user.
     * GET /api/arisan
     */
    public function index()
    {
        $userId = $this->uid();
        $groups = $this->groupModel->getForUser($userId);

        $result = [];
        foreach ($groups as $g) {
            $progress = $this->groupModel->getProgress((int)$g['id']);
            $g['progress'] = $progress;
            $result[] = $g;
        }

        return $this->ok(['groups' => $result]);
    }

    /**
     * Detail satu grup arisan lengkap dengan anggota & pembayaran.
     * GET /api/arisan/(:num)
     */
    public function show(int $id)
    {
        $userId = $this->uid();
        $group  = $this->groupModel->getDetailWithMembers($id, $userId);

        if (!$group || !$group['can_access']) {
            return $this->fail('Grup arisan tidak ditemukan.');
        }

        $group['progress'] = $this->groupModel->getProgress((int)$group['id']);

        return $this->ok(['group' => $group]);
    }

    /**
     * Buat grup arisan baru (standalone atau terhubung RT).
     * POST /api/arisan/store
     */
    public function store()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? $this->request->getPost();

        $name = trim($json['name'] ?? '');
        if (empty($name)) {
            return $this->fail('Nama kelompok arisan wajib diisi.');
        }

        $amount = (float)(str_replace(['.', ','], ['', '.'], (string)($json['amount'] ?? '0')));
        if ($amount <= 0) {
            return $this->fail('Nominal arisan harus lebih dari Rp 0.');
        }

        $frequency = in_array($json['frequency'] ?? '', ['weekly', 'biweekly', 'monthly'], true)
            ? $json['frequency'] : 'monthly';

        $groupId = $this->groupModel->insert([
            'neighborhood_id' => !empty($json['neighborhood_id']) ? (int)$json['neighborhood_id'] : null,
            'user_id'         => $userId,
            'name'            => $name,
            'amount'          => $amount,
            'frequency'       => $frequency,
            'total_members'   => 0,
            'current_round'   => 0,
            'status'          => 'active',
            'start_date'      => $json['start_date'] ?? date('Y-m-d'),
            'description'     => trim($json['description'] ?? ''),
        ]);

        // Owner otomatis menjadi anggota pertama
        $memberId = $this->memberModel->addMember($groupId, [
            'user_id'      => $userId,
            'member_name'  => $json['owner_name'] ?? ($json['member_name'] ?? 'Saya'),
            'phone'        => $json['owner_phone'] ?? null,
            'rotation_order' => 1,
        ]);

        $this->groupModel->update($groupId, ['total_members' => 1]);

        // Inisialisasi putaran 1
        $this->paymentModel->insert([
            'group_id'     => $groupId,
            'member_id'    => $memberId,
            'round_number' => 1,
            'amount'       => $amount,
            'status'       => 'unpaid',
        ]);
        $this->groupModel->update($groupId, ['current_round' => 1]);

        return $this->ok([
            'group_id'  => $groupId,
            'message'   => 'Kelompok arisan berhasil dibuat.',
            'group'     => $this->groupModel->find($groupId),
        ]);
    }

    /**
     * Tambah anggota ke grup arisan.
     * POST /api/arisan/(:num)/member
     */
    public function addMember(int $id)
    {
        $userId = $this->uid();
        $group  = $this->groupModel->find($id);

        if (!$group || (int)$group['user_id'] !== $userId) {
            return $this->fail('Anda bukan pemilik grup arisan ini.');
        }

        $json       = $this->request->getJSON(true) ?? $this->request->getPost();
        $memberName = trim($json['member_name'] ?? '');
        if (empty($memberName)) {
            return $this->fail('Nama anggota wajib diisi.');
        }

        $memberId = $this->memberModel->addMember($id, [
            'user_id'     => !empty($json['user_id']) ? (int)$json['user_id'] : null,
            'member_name' => $memberName,
            'phone'       => $json['phone'] ?? null,
        ]);

        $totalMembers = $this->memberModel->where('group_id', $id)->countAllResults();
        $this->groupModel->update($id, ['total_members' => $totalMembers]);

        // Buka slot pembayaran member baru untuk semua putaran yang sudah berjalan
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

        return $this->ok([
            'member_id'  => $memberId,
            'total_members' => $totalMembers,
            'message'    => 'Anggota berhasil ditambahkan.',
        ]);
    }

    /**
     * Catat setoran anggota pada putaran arisan.
     * POST /api/arisan/payment/pay
     */
    public function payPayment()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? $this->request->getPost();

        $paymentId = (int)($json['payment_id'] ?? 0);
        $groupId   = (int)($json['group_id'] ?? 0);

        $group = $this->groupModel->find($groupId);
        if (!$group) {
            return $this->fail('Grup arisan tidak ditemukan.');
        }
        // Pemilik atau anggota dapat mencatat setoran
        $isMember = $this->memberModel->where('group_id', $groupId)->where('user_id', $userId)->countAllResults() > 0;
        if ((int)$group['user_id'] !== $userId && !$isMember) {
            return $this->fail('Anda tidak memiliki akses ke grup ini.');
        }

        $res = $this->paymentModel->markPaid($paymentId, $groupId);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        return $this->ok([
            'message' => $res['message'],
        ]);
    }

    /**
     * Lanjut ke putaran arisan berikutnya (buat slot payment baru semua anggota).
     * POST /api/arisan/(:num)/advance
     */
    public function advanceRound(int $id)
    {
        $userId = $this->uid();
        $group  = $this->groupModel->find($id);

        if (!$group || (int)$group['user_id'] !== $userId) {
            return $this->fail('Anda bukan pemilik grup arisan ini.');
        }

        $created = $this->groupModel->advanceRound($id);

        return $this->ok([
            'message'      => 'Putaran baru telah dibuka.',
            'next_round'   => (int)$this->groupModel->find($id)['current_round'],
            'slots_created' => $created,
        ]);
    }
}
