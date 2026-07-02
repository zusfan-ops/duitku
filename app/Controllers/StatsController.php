<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\CategoryModel;
use App\Models\SettingModel;

class StatsController extends BaseController
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

        $idMonths   = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        [$y, $m]    = explode('-', $month);
        $monthLabel = ($idMonths[(int)$m - 1] ?? '') . ' ' . $y;

        return view('stats/index', [
            'pageTitle'      => 'Statistik',
            'catStats'       => $catStats,
            'trend'          => $trend,
            'monthly'        => $monthly,
            'symbol'         => $symbol,
            'month'          => $monthLabel,
            'monthKey'       => $month,
            'prevMonth'      => $prevMonth,
            'nextMonth'      => $nextMonth,
            'isCurrentMonth' => $month === date('Y-m'),
        ]);
    }
}
