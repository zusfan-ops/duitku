<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\SettingModel;

class ExportController extends BaseController
{
    protected TransactionModel $txModel;
    protected SettingModel     $settingModel;

    public function __construct()
    {
        $this->txModel      = new TransactionModel();
        $this->settingModel = new SettingModel();
    }

    // GET /export/pdf?month=YYYY-MM
    public function pdf()
    {
        $userId = session()->get('user_id');
        $month  = $this->request->getGet('month') ?: date('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $rows     = $this->txModel->getForExport($userId, $month);
        $catStats = $this->txModel->getCategoryStats($userId, $month);
        $monthly  = $this->txModel->getMonthlySummary($userId, $month);
        $symbol   = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $userName = session()->get('user_name');

        $idMonths = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        [$y, $m]    = explode('-', $month);
        $monthLabel = ($idMonths[(int)$m - 1] ?? $month) . ' ' . $y;

        return view('export/pdf', compact('rows', 'catStats', 'monthly', 'symbol', 'userName', 'month', 'monthLabel'));
    }

    // GET /export/csv?month=YYYY-MM
    public function csv()
    {
        $userId = session()->get('user_id');
        $month  = $this->request->getGet('month') ?: date('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $rows     = $this->txModel->getForExport($userId, $month);
        $symbol   = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $fileName = 'duitku-laporan-' . $month . '.csv';

        $this->response->setHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel compatibility
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Tanggal', 'Tipe', 'Kategori', 'Catatan', 'Jumlah (' . $symbol . ')']);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['date'],
                $row['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran',
                $row['category_name'] ?? 'Tanpa Kategori',
                $row['note'] ?? '',
                number_format($row['amount'], 0, ',', '.'),
            ]);
        }

        fclose($out);
        exit;
    }
}
