<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\SettingModel;

class TaxReportController extends BaseController
{
    protected TransactionModel $txModel;
    protected SettingModel     $settingModel;

    public function __construct()
    {
        $this->txModel      = new TransactionModel();
        $this->settingModel = new SettingModel();
    }

    // GET /tax-report?year=YYYY
    public function index()
    {
        $userId = session()->get('user_id');
        $year   = (int) ($this->request->getGet('year') ?: date('Y'));

        if ($year < 2020 || $year > 2099) {
            $year = (int) date('Y');
        }

        $yearly  = $this->txModel->getYearlySummary($userId, $year);
        $symbol  = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $userName = session()->get('user_name');

        $data = [
            'pageTitle' => 'Laporan Pajak Tahunan ' . $year,
            'year'      => $year,
            'yearly'    => $yearly,
            'symbol'    => $symbol,
            'userName'  => $userName,
        ];

        return view('tax_report/index', $data);
    }

    // GET /tax-report/print?year=YYYY
    public function print()
    {
        $userId = session()->get('user_id');
        $year   = (int) ($this->request->getGet('year') ?: date('Y'));

        if ($year < 2020 || $year > 2099) {
            $year = (int) date('Y');
        }

        $yearly  = $this->txModel->getYearlySummary($userId, $year);
        $symbol  = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $userName = session()->get('user_name');

        $data = [
            'pageTitle' => 'Laporan Pajak Tahunan ' . $year,
            'year'      => $year,
            'yearly'    => $yearly,
            'symbol'    => $symbol,
            'userName'  => $userName,
            'printMode' => true,
        ];

        return view('tax_report/index', $data);
    }

    // GET /tax-report/csv?year=YYYY
    public function csv()
    {
        $userId = session()->get('user_id');
        $year   = (int) ($this->request->getGet('year') ?: date('Y'));

        if ($year < 2020 || $year > 2099) {
            $year = (int) date('Y');
        }

        $yearly = $this->txModel->getYearlySummary($userId, $year);
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $fileName = 'duitku-laporan-pajak-' . $year . '.csv';

        $this->response->setHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        // PPh Final UMKM section
        fputcsv($out, ['LAPORAN PAJAK TAHUN ' . $year]);
        fputcsv($out, []);
        fputcsv($out, ['BAGIAN A: PPh Final UMKM (0.5%)']);
        fputcsv($out, ['Bulan', 'Omset / Peredaran Bruto (' . $symbol . ')', 'PPh Final 0.5% (' . $symbol . ')']);

        $totalOmset = 0;
        $totalTax   = 0;
        foreach ($yearly['months'] as $m) {
            $tax = $m['income'] * 0.005;
            fputcsv($out, [
                $m['label'] . ' ' . $year,
                number_format($m['income'], 0, ',', '.'),
                number_format($tax, 0, ',', '.'),
            ]);
            $totalOmset += $m['income'];
            $totalTax   += $tax;
        }

        fputcsv($out, ['TOTAL', number_format($totalOmset, 0, ',', '.'), number_format($totalTax, 0, ',', '.')]);

        $yearlyOmset = $totalOmset;
        if ($yearlyOmset <= 500000000) {
            fputcsv($out, ['Status: BEBAS PAJAK (Orang Pribadi, omset <= Rp 500 Juta/tahun)']);
        } else {
            fputcsv($out, ['Status: KENA PAJAK (omset > Rp 500 Juta/tahun)']);
        }

        fputcsv($out, []);
        fputcsv($out, ['BAGIAN B: Simulasi PPh 21 Pribadi']);
        fputcsv($out, ['Keterangan', 'Nilai (' . $symbol . ')']);
        fputcsv($out, ['Total Penghasilan Kotor Tahunan', number_format($totalOmset, 0, ',', '.')]);
        fputcsv($out, ['Biaya Jabatan (5%, max Rp 6.000.000)', number_format(min(6000000, $totalOmset * 0.05), 0, ',', '.')]);

        $biayaJabatan = min(6000000, $totalOmset * 0.05);
        $nettoTahunan = max(0, $totalOmset - $biayaJabatan);
        $ptkp         = 54000000;
        $pkp          = max(0, $nettoTahunan - $ptkp);

        fputcsv($out, ['Penghasilan Netto Tahunan', number_format($nettoTahunan, 0, ',', '.')]);
        fputcsv($out, ['PTKP (TK/0)', number_format($ptkp, 0, ',', '.')]);
        fputcsv($out, ['Penghasilan Kena Pajak (PKP)', number_format($pkp, 0, ',', '.')]);

        $taxPph21 = 0;
        if ($pkp > 0) {
            $tier1 = min($pkp, 60000000);
            $taxPph21 += $tier1 * 0.05;
            if ($pkp > 60000000) {
                $taxPph21 += min($pkp - 60000000, 190000000) * 0.15;
            }
            if ($pkp > 250000000) {
                $taxPph21 += min($pkp - 250000000, 250000000) * 0.25;
            }
            if ($pkp > 500000000) {
                $taxPph21 += min($pkp - 500000000, 4500000000) * 0.30;
            }
            if ($pkp > 5000000000) {
                $taxPph21 += ($pkp - 5000000000) * 0.35;
            }
        }

        fputcsv($out, ['Estimasi PPh 21 Tahunan', number_format($taxPph21, 0, ',', '.')]);
        fputcsv($out, ['Estimasi PPh 21 Per Bulan', number_format($taxPph21 / 12, 0, ',', '.')]);

        fclose($out);
        exit;
    }
}
