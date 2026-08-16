<?php

namespace App\Controllers;

use App\Models\SavingsGoalModel;
use App\Models\SettingModel;

class SavingsController extends BaseController
{
    protected SavingsGoalModel $goalModel;
    protected SettingModel     $settingModel;

    public function __construct()
    {
        $this->goalModel    = new SavingsGoalModel();
        $this->settingModel = new SettingModel();
    }

    // GET /savings
    public function index(): string
    {
        $userId = session()->get('user_id');
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $goals  = $this->goalModel->getForUser($userId);

        return view('savings/index', [
            'pageTitle' => 'Target Menabung',
            'goals'     => $goals,
            'symbol'    => $symbol,
        ]);
    }

    // POST /savings/store
    public function store()
    {
        $userId  = session()->get('user_id');
        $name    = trim($this->request->getPost('name') ?? '');
        $target  = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('target_amount') ?? '0');
        $saved   = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('saved_amount')  ?? '0');
        $icon    = $this->request->getPost('icon')  ?: '🎯';
        $color   = $this->request->getPost('color') ?: '#0AA956';
        $deadline= $this->request->getPost('deadline') ?: null;
        $editId  = (int)($this->request->getPost('id') ?: 0) ?: null;

        if (!$name || $target <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama dan target wajib diisi.']);
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
            if (!$goal) {
                return $this->response->setJSON(['success' => false, 'message' => 'Goal tidak ditemukan.']);
            }
            $this->goalModel->update($editId, $data);
            return $this->response->setJSON(['success' => true, 'id' => $editId]);
        }

        $id = $this->goalModel->insert($data);
        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    // POST /savings/topup/{id}
    public function topup(int $id)
    {
        $userId = session()->get('user_id');
        $amount = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('amount') ?? '0');

        if ($amount <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nominal tidak valid.']);
        }

        $ok = $this->goalModel->topUp($id, $userId, $amount);
        if (!$ok) {
            return $this->response->setJSON(['success' => false, 'message' => 'Goal tidak ditemukan.']);
        }

        $goal = $this->goalModel->find($id);
        return $this->response->setJSON([
            'success'      => true,
            'saved_amount' => (float)$goal['saved_amount'],
            'target_amount'=> (float)$goal['target_amount'],
        ]);
    }

    // POST /savings/delete/{id}
    public function delete(int $id)
    {
        $userId = session()->get('user_id');
        $ok     = $this->goalModel->deleteForUser($id, $userId);
        return $this->response->setJSON(['success' => $ok]);
    }
}
