<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\NotificationModel;
use App\Models\TvChannelModel;

class DashboardController extends BaseController
{
    protected UserModel         $userModel;
    protected NotificationModel $notifModel;
    protected TvChannelModel    $tvModel;

    public function __construct()
    {
        $this->userModel  = new UserModel();
        $this->notifModel = new NotificationModel();
        $this->tvModel    = new TvChannelModel();
    }

    public function index()
    {
        $db = \Config\Database::connect();

        // 1. Metrik Pengguna
        $totalUsers = 0;
        $totalAdmins = 0;
        $recentUsers = [];
        try {
            $totalUsers  = $this->userModel->countAllResults();
            $totalAdmins = $this->userModel->groupStart()
                                           ->where('role', 'administrator')
                                           ->orWhere('role', 'admin')
                                           ->groupEnd()
                                           ->countAllResults();
            $recentUsers = $this->userModel->orderBy('created_at', 'DESC')->limit(8)->findAll();
        } catch (\Throwable $e) {}

        // 2. Metrik Transaksi
        $totalTx      = 0;
        $totalIncome  = 0;
        $totalExpense = 0;
        try {
            if ($db->tableExists('transactions')) {
                $totalTx = $db->table('transactions')->countAllResults();
                $incomeRow = $db->table('transactions')->selectSum('amount')->where('type', 'income')->get()->getRow();
                $expenseRow = $db->table('transactions')->selectSum('amount')->where('type', 'expense')->get()->getRow();
                $totalIncome = (float) ($incomeRow->amount ?? 0);
                $totalExpense = (float) ($expenseRow->amount ?? 0);
            }
        } catch (\Throwable $e) {}

        // 3. Metrik POS Orders
        $totalPosOrders  = 0;
        $totalPosRevenue = 0;
        try {
            if ($db->tableExists('pos_orders')) {
                $totalPosOrders = $db->table('pos_orders')->countAllResults();
                $posRevRow = $db->table('pos_orders')->selectSum('grand_total')->where('payment_status', 'paid')->get()->getRow();
                $totalPosRevenue = (float) ($posRevRow->grand_total ?? 0);
            }
        } catch (\Throwable $e) {}

        // 4. Metrik Notifikasi & TV
        $totalNotifications = 0;
        $totalTvChannels    = 0;
        $activeTvChannels   = 0;
        $recentNotifs       = [];
        try {
            if ($db->tableExists('app_notifications')) {
                $totalNotifications = $this->notifModel->countAllResults();
                $recentNotifs       = $this->notifModel->orderBy('created_at', 'DESC')->limit(5)->findAll();
            }
            if ($db->tableExists('tv_channels')) {
                $totalTvChannels  = $this->tvModel->countAllResults();
                $activeTvChannels = $this->tvModel->where('is_active', 1)->countAllResults();
            }
        } catch (\Throwable $e) {}

        $data = [
            'pageTitle'          => 'Admin Dashboard',
            'activeMenu'         => 'dashboard',
            'totalUsers'         => $totalUsers,
            'totalAdmins'        => $totalAdmins,
            'totalTx'            => $totalTx,
            'totalIncome'        => $totalIncome,
            'totalExpense'       => $totalExpense,
            'totalPosOrders'     => $totalPosOrders,
            'totalPosRevenue'    => $totalPosRevenue,
            'totalNotifications' => $totalNotifications,
            'totalTvChannels'    => $totalTvChannels,
            'activeTvChannels'   => $activeTvChannels,
            'recentUsers'        => $recentUsers,
            'recentNotifs'       => $recentNotifs,
        ];

        return view('admin/dashboard', $data);
    }
}
