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
        $this->notifModel->ensureTable();
        $this->tvModel->ensureTable();

        $db = \Config\Database::connect();

        // Ensure role column in users
        if (!$db->fieldExists('role', 'users')) {
            $forge = \Config\Database::forge();
            $forge->addColumn('users', [
                'role' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'user',
                    'after'      => 'password',
                ],
            ]);
        }

        // Metrik Pengguna
        $totalUsers = $this->userModel->countAllResults();
        $totalAdmins = $this->userModel->groupStart()->where('role', 'administrator')->orWhere('role', 'admin')->groupEnd()->countAllResults();

        // Metrik Transaksi
        $totalTx = 0;
        $totalIncome = 0;
        $totalExpense = 0;
        if ($db->tableExists('transactions')) {
            $totalTx = $db->table('transactions')->countAllResults();
            $incomeRow = $db->table('transactions')->selectSum('amount')->where('type', 'income')->get()->getRow();
            $expenseRow = $db->table('transactions')->selectSum('amount')->where('type', 'expense')->get()->getRow();
            $totalIncome = (float) ($incomeRow->amount ?? 0);
            $totalExpense = (float) ($expenseRow->amount ?? 0);
        }

        // Metrik POS Orders
        $totalPosOrders = 0;
        $totalPosRevenue = 0;
        if ($db->tableExists('pos_orders')) {
            $totalPosOrders = $db->table('pos_orders')->countAllResults();
            $posRevRow = $db->table('pos_orders')->selectSum('grand_total')->where('payment_status', 'paid')->get()->getRow();
            $totalPosRevenue = (float) ($posRevRow->grand_total ?? 0);
        }

        // Metrik Notifikasi & TV
        $totalNotifications = $this->notifModel->countAllResults();
        $totalTvChannels    = $this->tvModel->countAllResults();
        $activeTvChannels   = $this->tvModel->where('is_active', 1)->countAllResults();

        // User terbaru
        $recentUsers = $this->userModel->orderBy('created_at', 'DESC')->limit(8)->findAll();

        // Notifikasi terbaru
        $recentNotifs = $this->notifModel->orderBy('created_at', 'DESC')->limit(5)->findAll();

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
