<?php

namespace App\Controllers\Api;

use App\Models\SavingsGoalModel;

class SavingsController extends ApiController
{
    protected SavingsGoalModel $goalModel;

    public function __construct()
    {
        $this->goalModel = new SavingsGoalModel();
    }

    // GET api/savings
    public function index()
    {
        $userId = $this->uid();
        $goals  = $this->goalModel->getForUser($userId);
        return $this->ok(['goals' => $goals]);
    }

    // POST api/savings/store
    public function store()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];

        $name    = trim($json['name'] ?? '');
        $target  = $this->amount($json['target_amount'] ?? 0);
        $saved   = $this->amount($json['saved_amount'] ?? 0);
        $icon    = $json['icon']  ?? '🎯';
        $color   = $json['color'] ?? '#0AA956';
        $deadline= $json['deadline'] ?? null;
        $editId  = (int)($json['id'] ?? 0) ?: null;

        if (!$name || $target <= 0) {
            return $this->fail('Nama dan target wajib diisi.');
        }

        if ($deadline && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) {
            $deadline = null;
        }

        $data = [
            'user_id'       => $userId,
            'name'          => $name,
            'icon'          => $icon,
            'color'         => $color,
            'target_amount' => $target,
            'saved_amount'  => max(0, min($saved, $target)),
            'deadline'      => $deadline,
        ];

        if ($editId) {
            $goal = $this->goalModel->where('id', $editId)->where('user_id', $userId)->first();
            if (!$goal) return $this->fail('Goal tidak ditemukan.');
            $this->goalModel->update($editId, $data);
            return $this->ok(['id' => $editId]);
        }

        $id = $this->goalModel->insert($data);
        if (!$id) return $this->fail('Gagal menyimpan.');
        return $this->ok(['id' => $id]);
    }

    // POST api/savings/topup/{id}
    public function topup(int $id)
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $amount = $this->amount($json['amount'] ?? 0);

        if ($amount <= 0) return $this->fail('Nominal tidak valid.');

        $ok = $this->goalModel->topUp($id, $userId, $amount);
        if (!$ok) return $this->fail('Goal tidak ditemukan.');

        $goal = $this->goalModel->find($id);
        return $this->ok([
            'saved_amount'  => (float)$goal['saved_amount'],
            'target_amount' => (float)$goal['target_amount'],
        ]);
    }

    // POST api/savings/delete/{id}
    public function delete(int $id)
    {
        $userId = $this->uid();
        $ok     = $this->goalModel->deleteForUser($id, $userId);
        if (!$ok) return $this->fail('Goal tidak ditemukan.');
        return $this->ok();
    }
}
