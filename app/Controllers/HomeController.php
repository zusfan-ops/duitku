<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\CategoryModel;
use App\Models\SettingModel;
use App\Models\DebtModel;
use App\Models\WalletModel;

class HomeController extends BaseController
{
    protected TransactionModel $txModel;
    protected CategoryModel    $catModel;
    protected SettingModel     $settingModel;
    protected DebtModel        $debtModel;
    protected WalletModel      $walletModel;

    public function __construct()
    {
        $this->txModel      = new TransactionModel();
        $this->catModel     = new CategoryModel();
        $this->settingModel = new SettingModel();
        $this->debtModel    = new DebtModel();
        $this->walletModel  = new WalletModel();
    }

    public function index(): string
    {
        $userId   = session()->get('user_id');
        $now      = date('Y-m');
        $currency = $this->settingModel->get($userId, 'currency', 'IDR');
        $symbol   = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        // Auto-apply due recurring transactions
        $this->applyRecurring($userId);

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

        return view('home/index', [
            'pageTitle'     => 'Beranda',
            'balance'       => $balance,
            'monthly'       => $monthly,
            'recent'        => $recent,
            'categories'    => $categories,
            'currency'      => $currency,
            'symbol'        => $symbol,
            'month'         => date('F Y'),
            'monthKey'      => $now,
            'budget'        => $budget,
            'budgetPct'     => $budgetPct,
            'savingsName'   => $savingsName,
            'savingsTarget' => $savingsTarget,
            'savingsSaved'  => $savingsSaved,
            'savingsPct'    => $savingsPct,
            'monthNote'     => $monthNote,
            'debtSummary'   => $debtSummary,
            'wallets'       => $wallets,
            'dailyBalance'  => $dailyBalance,
            'upcomingBills' => $upcomingBills,
            'upcomingDebts' => $upcomingDebts,
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
