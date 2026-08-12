<?php

namespace App\Controllers\Api;

use App\Models\DebtModel;
use App\Models\SettingModel;
use App\Models\TransactionModel;
use App\Models\WalletModel;

class DashboardController extends ApiController
{
    protected TransactionModel $txModel;
    protected SettingModel     $settingModel;
    protected DebtModel        $debtModel;
    protected WalletModel      $walletModel;

    public function __construct()
    {
        $this->txModel      = new TransactionModel();
        $this->settingModel = new SettingModel();
        $this->debtModel    = new DebtModel();
        $this->walletModel  = new WalletModel();
    }

    public function index()
    {
        $userId   = $this->uid();
        $now      = date('Y-m');
        $currency = $this->settingModel->get($userId, 'currency', 'IDR');
        $symbol   = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        $this->applyRecurring($userId);

        $monthly    = $this->txModel->getMonthlySummary($userId, $now);
        $categories = (new \App\Models\CategoryModel())->getForUser($userId);
        $recent     = $this->txModel->getRecent($userId, 15);

        $walletData = $this->walletModel->getWithBalances($userId);
        $wallets    = $walletData['wallets'];
        $balance    = $walletData['total'];

        $budget    = (float) ($this->settingModel->get($userId, 'budget_' . $now, 0));
        $budgetPct = ($budget > 0) ? min(($monthly['expense'] / $budget) * 100, 100) : 0;

        $savingsName   = $this->settingModel->get($userId, 'savings_name', '');
        $savingsTarget = (float) ($this->settingModel->get($userId, 'savings_target', 0));
        $savingsSaved  = (float) ($this->settingModel->get($userId, 'savings_saved', 0));
        $savingsPct    = ($savingsTarget > 0) ? min(($savingsSaved / $savingsTarget) * 100, 100) : 0;

        $monthNote = $this->settingModel->get($userId, 'note_' . $now, '');
        $debtSummary = $this->debtModel->getSummary($userId);
        $dailyBalance = $this->txModel->getDailyBalance($userId, $now);

        // Top spending categories this month (for dashboard preview card)
        $catStats = array_values(array_filter(
            $this->txModel->getCategoryStats($userId, $now),
            fn ($c) => $c['type'] === 'expense'
        ));
        $topCategories = array_map(function ($c) use ($monthly) {
            $c['pct'] = $monthly['expense'] > 0 ? round(((float) $c['total'] / $monthly['expense']) * 100, 1) : 0;
            return $c;
        }, array_slice($catStats, 0, 3));

        // Reminders: bills within 3 days
        $billsRaw     = $this->settingModel->get($userId, 'bills', '[]');
        $billsAll     = json_decode($billsRaw, true) ?: [];
        $todayDay     = (int) date('j');
        $upcomingBills = [];
        foreach ($billsAll as $b) {
            $dueDay   = (int) ($b['dueDay'] ?? 0);
            $daysLeft = $dueDay - $todayDay;
            if ($daysLeft >= -3 && $daysLeft <= 3) {
                $upcomingBills[] = array_merge($b, ['daysLeft' => $daysLeft]);
            }
        }

        // Debts due within 7 days
        $upcomingDebts = $this->debtModel->getUpcoming($userId, 7);
        $todayDate     = date('Y-m-d');
        foreach ($upcomingDebts as &$d) {
            $d['daysLeft'] = (int) floor((strtotime($d['due_date']) - strtotime($todayDate)) / 86400);
        }
        unset($d);

        return $this->ok([
            'balance'        => $balance,
            'monthly'        => $monthly,
            'recent'         => $recent,
            'categories'     => $categories,
            'currency'       => $currency,
            'symbol'         => $symbol,
            'month'          => date('F Y'),
            'monthKey'       => $now,
            'budget'         => $budget,
            'budgetPct'      => round($budgetPct, 1),
            'savingsName'    => $savingsName,
            'savingsTarget'  => $savingsTarget,
            'savingsSaved'   => $savingsSaved,
            'savingsPct'     => round($savingsPct, 1),
            'monthNote'      => $monthNote,
            'debtSummary'    => $debtSummary,
            'topCategories'  => $topCategories,
            'wallets'        => $wallets,
            'dailyBalance'   => $dailyBalance,
            'upcomingBills'  => $upcomingBills,
            'upcomingDebts'  => $upcomingDebts,
            'user'           => [
                'name'  => session()->get('user_name') ?: '',
            ],
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
