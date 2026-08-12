<?php

namespace App\Controllers\Api;

use App\Models\CategoryModel;
use App\Models\SettingModel;
use App\Models\TransactionModel;

class ActivityController extends ApiController
{
    protected TransactionModel $txModel;
    protected SettingModel     $settingModel;

    public function __construct()
    {
        $this->txModel      = new TransactionModel();
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        $userId = $this->uid();
        $type   = $this->request->getGet('type') ?? 'all';
        $page   = max(1, (int) ($this->request->getGet('page') ?? 1));
        $search = trim($this->request->getGet('search') ?? '');

        if (!in_array($type, ['all', 'income', 'expense'])) {
            $type = 'all';
        }

        $result = $this->txModel->getActivity($userId, $type, $page, 20, $search);
        $categories = (new CategoryModel())->getForUser($userId);
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return $this->ok([
            'transactions' => $result['data'],
            'total'        => $result['total'],
            'page'         => $result['page'],
            'perPage'      => $result['perPage'],
            'totalPages'   => $result['totalPages'],
            'categories'   => $categories,
            'symbol'       => $symbol,
        ]);
    }
}
