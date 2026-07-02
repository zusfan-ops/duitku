<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\CategoryModel;
use App\Models\SettingModel;

class ActivityController extends BaseController
{
    protected TransactionModel $txModel;
    protected CategoryModel    $catModel;
    protected SettingModel     $settingModel;

    public function __construct()
    {
        $this->txModel      = new TransactionModel();
        $this->catModel     = new CategoryModel();
        $this->settingModel = new SettingModel();
    }

    public function index(): string
    {
        $userId = session()->get('user_id');
        $type   = $this->request->getGet('type') ?? 'all';
        $page   = (int)($this->request->getGet('page') ?? 1);
        $search = trim($this->request->getGet('search') ?? '');
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        if (!in_array($type, ['all', 'income', 'expense'])) {
            $type = 'all';
        }
        if ($page < 1) $page = 1;

        $result     = $this->txModel->getActivity($userId, $type, $page, 20, $search);
        $categories = $this->catModel->getForUser($userId);

        return view('activity/index', [
            'pageTitle'   => 'Aktivitas',
            'transactions'=> $result['data'],
            'total'       => $result['total'],
            'page'        => $result['page'],
            'totalPages'  => $result['totalPages'],
            'activeType'  => $type,
            'categories'  => $categories,
            'symbol'      => $symbol,
            'search'      => $search,
        ]);
    }
}
