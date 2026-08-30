<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\CategoryModel;
use App\Models\SettingModel;
use App\Models\DebtModel;
use App\Models\WalletModel;
use App\Models\VehicleModel;
use App\Models\RecurringTransactionModel;

class HomeController extends BaseController
{
    protected TransactionModel           $txModel;
    protected CategoryModel              $catModel;
    protected SettingModel               $settingModel;
    protected DebtModel                  $debtModel;
    protected WalletModel                $walletModel;
    protected VehicleModel               $vehicleModel;
    protected RecurringTransactionModel  $recurringModel;

    public function __construct()
    {
        $this->txModel        = new TransactionModel();
        $this->catModel       = new CategoryModel();
        $this->settingModel   = new SettingModel();
        $this->debtModel      = new DebtModel();
        $this->walletModel    = new WalletModel();
        $this->vehicleModel   = new VehicleModel();
        $this->recurringModel = new RecurringTransactionModel();
    }

    public function index(): string
    {
        $userId   = session()->get('user_id');
        $now      = date('Y-m');
        $currency = $this->settingModel->get($userId, 'currency', 'IDR');
        $symbol   = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        // Auto-apply due recurring transactions
        try {
            $this->applyRecurring($userId);
        } catch (\Throwable $e) {}

        $monthly    = $this->txModel->getMonthlySummary($userId, $now);
        $categories = $this->catModel->getForUser($userId);
        $recent     = $this->txModel->getRecent($userId, 15);

        // Multi-wallet: total balance includes wallet initial_balance
        $walletData = $this->walletModel->getWithBalances($userId);
        $wallets    = $walletData['wallets'];
        $balance    = $walletData['total'];

        // Budget for current month
        $budget    = (float)($this->settingModel->get($userId, 'budget_' . $now, 0));
        $budgetPct = ($budget > 0) ? min(($monthly['expense'] / $budget) * 100, 100) : 0;

        // Savings goal
        $savingsName   = $this->settingModel->get($userId, 'savings_name', '');
        $savingsTarget = (float)($this->settingModel->get($userId, 'savings_target', 0));
        $savingsSaved  = (float)($this->settingModel->get($userId, 'savings_saved', 0));
        $savingsPct    = ($savingsTarget > 0) ? min(($savingsSaved / $savingsTarget) * 100, 100) : 0;

        // Monthly note
        $monthNote = $this->settingModel->get($userId, 'note_' . $now, '');

        // Debt summary
        $debtSummary = $this->debtModel->getSummary($userId);

        // Daily balance sparkline (current month)
        $dailyBalance = $this->txModel->getDailyBalance($userId, $now);

        // ── Reminders ───────────────────────────────────────────────
        // Bills due within 3 days (or up to 3 days overdue)
        $billsRaw      = $this->settingModel->get($userId, 'bills', '[]');
        $billsAll      = json_decode($billsRaw, true) ?: [];
        $todayDay      = (int)date('j');
        $upcomingBills = [];
        foreach ($billsAll as $b) {
            $dueDay   = (int)($b['dueDay'] ?? 0);
            $daysLeft = $dueDay - $todayDay;
            if ($daysLeft >= -3 && $daysLeft <= 3) {
                $upcomingBills[] = array_merge($b, ['daysLeft' => $daysLeft]);
            }
        }

        // Debts due within 7 days
        $upcomingDebts = $this->debtModel->getUpcoming($userId, 7);
        $todayDate     = date('Y-m-d');
        foreach ($upcomingDebts as &$d) {
            $d['daysLeft'] = (int)floor((strtotime($d['due_date']) - strtotime($todayDate)) / 86400);
        }
        unset($d);

        // Vehicle tax reminders (within 30 days)
        $upcomingTaxes = [];
        try {
            $upcomingTaxes = $this->vehicleModel->getUpcomingTaxes($userId, 30);
        } catch (\Throwable $e) {}

        // Recurring due reminders (within 3 days)
        $upcomingRecurring = [];
        try {
            $allRecurring = $this->recurringModel->getForUser($userId);
            foreach ($allRecurring as $rec) {
                $recDays = (int) floor((strtotime($rec['next_date']) - strtotime($todayDate)) / 86400);
                if ($recDays <= 3) {
                    $upcomingRecurring[] = array_merge($rec, ['daysLeft' => $recDays]);
                }
            }
        } catch (\Throwable $e) {}

        // Consolidated Notifications Array
        $notifications = [];
        foreach ($upcomingBills as $b) {
            $notifications[] = [
                'id'         => 'bill_' . ($b['id'] ?? uniqid()),
                'type'       => 'bill',
                'title'      => $b['name'],
                'subtitle'   => ($b['daysLeft'] <= 0 ? ($b['daysLeft'] == 0 ? 'Jatuh tempo Hari Ini!' : 'Lewat ' . abs($b['daysLeft']) . ' hari!') : 'Jatuh tempo dalam ' . $b['daysLeft'] . ' hari (tgl ' . ($b['dueDay'] ?? '') . ')'),
                'amount'     => (float)($b['amount'] ?? 0),
                'days_left'  => (int)$b['daysLeft'],
                'urgency'    => $b['daysLeft'] <= 0 ? 'urgent' : ($b['daysLeft'] <= 2 ? 'warning' : 'info'),
                'icon'       => '📋',
                'action_url' => '/bills',
                'raw'        => $b,
            ];
        }
        foreach ($upcomingDebts as $d) {
            $notifications[] = [
                'id'         => 'debt_' . $d['id'],
                'type'       => 'debt',
                'title'      => ($d['type'] === 'hutang' ? 'Bayar Hutang: ' : 'Tagih Piutang: ') . $d['person'],
                'subtitle'   => ($d['daysLeft'] <= 0 ? ($d['daysLeft'] == 0 ? 'Jatuh tempo Hari Ini!' : 'Lewat ' . abs($d['daysLeft']) . ' hari!') : 'Jatuh tempo dalam ' . $d['daysLeft'] . ' hari (' . date('d M', strtotime($d['due_date'])) . ')'),
                'amount'     => (float)($d['amount'] ?? 0),
                'days_left'  => (int)$d['daysLeft'],
                'urgency'    => $d['daysLeft'] <= 0 ? 'urgent' : ($d['daysLeft'] <= 2 ? 'warning' : 'info'),
                'icon'       => $d['type'] === 'hutang' ? '💸' : '💰',
                'action_url' => '/hutang',
                'raw'        => $d,
            ];
        }
        foreach ($upcomingTaxes as $t) {
            $notifications[] = [
                'id'         => 'tax_' . $t['vehicle_id'] . '_' . md5($t['type']),
                'type'       => 'tax',
                'title'      => $t['type'] . ' · ' . $t['vehicle_name'],
                'subtitle'   => ($t['days_left'] <= 0 ? ($t['days_left'] == 0 ? 'Jatuh tempo Hari Ini!' : 'Lewat ' . abs($t['days_left']) . ' hari!') : 'Jatuh tempo dalam ' . $t['days_left'] . ' hari (' . date('d M Y', strtotime($t['due_date'])) . ')'),
                'amount'     => 0,
                'days_left'  => (int)$t['days_left'],
                'urgency'    => $t['days_left'] <= 0 ? 'urgent' : ($t['days_left'] <= 7 ? 'warning' : 'info'),
                'icon'       => '🚗',
                'action_url' => '/kendaraan/' . $t['vehicle_id'],
                'raw'        => $t,
            ];
        }
        foreach ($upcomingRecurring as $r) {
            $notifications[] = [
                'id'         => 'recurring_' . $r['id'],
                'type'       => 'recurring',
                'title'      => ($r['category_name'] ?? 'Transaksi Berulang') . ($r['note'] ? ' (' . $r['note'] . ')' : ''),
                'subtitle'   => ($r['daysLeft'] <= 0 ? ($r['daysLeft'] == 0 ? 'Jatuh tempo Hari Ini!' : 'Lewat ' . abs($r['daysLeft']) . ' hari!') : 'Jatuh tempo dalam ' . $r['daysLeft'] . ' hari (' . date('d M', strtotime($r['next_date'])) . ')'),
                'amount'     => (float)($r['amount'] ?? 0),
                'days_left'  => (int)$r['daysLeft'],
                'urgency'    => $r['daysLeft'] <= 0 ? 'urgent' : ($r['daysLeft'] <= 1 ? 'warning' : 'info'),
                'icon'       => '🔁',
                'action_url' => '/recurring',
                'raw'        => $r,
            ];
        }

        usort($notifications, fn($a, $b) => $a['days_left'] <=> $b['days_left']);

        // Business / POS Workspace Summary
        $businessSummary = [
            'today_sales'   => 0.0,
            'today_cost'    => 0.0,
            'today_profit'  => 0.0,
            'today_orders'  => 0,
            'month_sales'   => 0.0,
            'month_cost'    => 0.0,
            'month_profit'  => 0.0,
            'month_orders'  => 0,
            'low_stock_count' => 0,
            'low_stock_products' => [],
            'kasbon_unsettled_count' => 0,
            'kasbon_unsettled_total' => 0.0,
            'best_sellers'  => [],
            'recent_orders' => [],
        ];

        try {
            $posOrderModel = new \App\Models\PosOrderModel();
            $posProductModel = new \App\Models\PosProductModel();
            $todaySummary = $posOrderModel->getTodaySummary($userId);
            $monthReport = $posOrderModel->getMonthlyReport($userId, $now);
            $monthSummary = $monthReport['summary'] ?? [];
            $bestSellers = array_slice($monthReport['bestSellers'] ?? [], 0, 4);
            $lowStockProducts = $posProductModel->getLowStock($userId);
            $recentOrders = $posOrderModel->getRecent($userId, 5);

            $db = \Config\Database::connect();
            $kasbonSummary = $db->query("
                SELECT COUNT(*) AS count, COALESCE(SUM(amount), 0) AS total
                FROM debts
                WHERE user_id = ? AND type = 'piutang' AND is_settled = 0
            ", [$userId])->getRowArray() ?: ['count' => 0, 'total' => 0];

            $businessSummary = [
                'today_sales'   => (float)($todaySummary['total_sales'] ?? 0),
                'today_cost'    => (float)($todaySummary['total_cost'] ?? 0),
                'today_profit'  => (float)($todaySummary['total_profit'] ?? 0),
                'today_orders'  => (int)($todaySummary['total_orders'] ?? 0),
                'month_sales'   => (float)($monthSummary['total_sales'] ?? 0),
                'month_cost'    => (float)($monthSummary['total_cost'] ?? 0),
                'month_profit'  => (float)($monthSummary['total_profit'] ?? 0),
                'month_orders'  => (int)($monthSummary['total_orders'] ?? 0),
                'low_stock_count' => count($lowStockProducts),
                'low_stock_products' => array_slice($lowStockProducts, 0, 4),
                'kasbon_unsettled_count' => (int)($kasbonSummary['count'] ?? 0),
                'kasbon_unsettled_total' => (float)($kasbonSummary['total'] ?? 0),
                'best_sellers'  => $bestSellers,
                'recent_orders' => $recentOrders,
            ];
        } catch (\Throwable $e) {}

        return view('home/index', [
            'pageTitle'          => 'Beranda',
            'balance'            => $balance,
            'monthly'            => $monthly,
            'recent'             => $recent,
            'categories'         => $categories,
            'currency'           => $currency,
            'symbol'             => $symbol,
            'month'              => date('F Y'),
            'monthKey'           => $now,
            'budget'             => $budget,
            'budgetPct'          => $budgetPct,
            'savingsName'        => $savingsName,
            'savingsTarget'      => $savingsTarget,
            'savingsSaved'       => $savingsSaved,
            'savingsPct'         => $savingsPct,
            'monthNote'          => $monthNote,
            'debtSummary'        => $debtSummary,
            'wallets'            => $wallets,
            'dailyBalance'       => $dailyBalance,
            'upcomingBills'      => $upcomingBills,
            'upcomingDebts'      => $upcomingDebts,
            'upcomingTaxes'      => $upcomingTaxes,
            'upcomingRecurring'  => $upcomingRecurring,
            'notifications'      => $notifications,
            'unreadCount'        => count($notifications),
            'business'           => $businessSummary,
            'todoSummary'        => (new \App\Models\TodoModel())->getSummary($userId),
        ]);
    }

    private function applyRecurring(int $userId): void
    {
        $db    = \Config\Database::connect();
        $today = date('Y-m-d');

        $dues = $db->query("
            SELECT * FROM recurring_transactions WHERE user_id = ? AND next_date <= ?
        ", [$userId, $today])->getResultArray();

        if (empty($dues)) return;

        $defaultWalletId = $this->walletModel->getDefaultWalletId($userId);

        foreach ($dues as $r) {
            $db->query("
                INSERT INTO transactions (user_id, wallet_id, category_id, type, amount, note, date, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ", [$userId, $defaultWalletId, $r['category_id'], $r['type'], $r['amount'], $r['note'], $today]);

            $nextDate = date('Y-m-d', strtotime($r['next_date'] . ' +1 month'));
            $db->query("UPDATE recurring_transactions SET next_date = ?, updated_at = NOW() WHERE id = ?", [$nextDate, $r['id']]);
        }
    }
}
