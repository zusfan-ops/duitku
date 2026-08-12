<?php

namespace App\Controllers\Api;

use App\Models\SettingModel;
use App\Models\TransactionModel;

class StatsController extends ApiController
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
        $month  = $this->request->getGet('month') ?: date('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $symbol   = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $catStats = $this->txModel->getCategoryStats($userId, $month);
        $trend    = $this->txModel->getMonthlyTrend($userId, 6);
        $monthly  = $this->txModel->getMonthlySummary($userId, $month);

        $dt        = new \DateTime($month . '-01');
        $prevMonth = (clone $dt)->modify('-1 month')->format('Y-m');
        $nextMonth = (clone $dt)->modify('+1 month')->format('Y-m');

        return $this->ok([
            'catStats'  => $catStats,
            'trend'     => $trend,
            'monthly'   => $monthly,
            'symbol'    => $symbol,
            'month'     => $month,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }
}
