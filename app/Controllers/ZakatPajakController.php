<?php

namespace App\Controllers;

use App\Models\WalletModel;
use App\Models\TransactionModel;

class ZakatPajakController extends BaseController
{
    public function index()
    {
        $userId = session()->get('user_id');
        $walletModel = new WalletModel();
        $txModel = new TransactionModel();

        // Total kekayaan saldo seluruh dompet
        $wallets = $walletModel->where('user_id', $userId)->findAll();
        $totalBalance = 0;
        foreach ($wallets as $w) {
            $totalBalance += (float) ($w['balance'] ?? 0);
        }

        // Estimasi penghasilan bulanan (pemasukan bulan ini)
        $currentMonth = date('Y-m');
        $monthlyIncome = 0;
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('transactions')) {
                $row = $db->table('transactions')
                          ->selectSum('amount')
                          ->where('user_id', $userId)
                          ->where('type', 'income')
                          ->like('date', $currentMonth)
                          ->get()
                          ->getRow();
                $monthlyIncome = (float) ($row->amount ?? 0);
            }
        } catch (\Throwable $e) {}

        // Kategori untuk opsi catat transaksi
        $categories = [];
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('categories')) {
                $categories = $db->table('categories')
                                 ->where('user_id', $userId)
                                 ->orWhere('user_id IS NULL', null, false)
                                 ->get()
                                 ->getResultArray();
            }
        } catch (\Throwable $e) {}

        $data = [
            'pageTitle'     => 'Kalkulator Zakat & Pajak',
            'totalBalance'  => $totalBalance,
            'monthlyIncome' => $monthlyIncome,
            'wallets'       => $wallets,
            'categories'    => $categories,
        ];

        return view('zakat_pajak/index', $data);
    }
}
