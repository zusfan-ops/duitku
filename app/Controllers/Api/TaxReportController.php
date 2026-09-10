<?php

namespace App\Controllers\Api;

use App\Models\TransactionModel;
use App\Models\SettingModel;

class TaxReportController extends ApiController
{
    protected TransactionModel $txModel;
    protected SettingModel     $settingModel;

    public function __construct()
    {
        $this->txModel      = new TransactionModel();
        $this->settingModel = new SettingModel();
    }

    // GET /api/tax-report?year=YYYY
    public function index()
    {
        $userId = $this->uid();
        $year   = (int) ($this->request->getGet('year') ?: date('Y'));

        if ($year < 2020 || $year > 2099) {
            $year = (int) date('Y');
        }

        $yearly = $this->txModel->getYearlySummary($userId, $year);
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        // ── PPh Final UMKM ──
        $totalOmset = $yearly['total_income'];
        $yearlyTaxUmkm = $totalOmset * 0.005;
        $monthlyTaxUmkm = $yearlyTaxUmkm / 12;
        $isFree = $totalOmset <= 500000000;

        // ── PPh 21 (simulasi TK/0) ──
        $biayaJabatan = min(6000000, $totalOmset * 0.05);
        $nettoTahunan = max(0, $totalOmset - $biayaJabatan);
        $ptkp         = 54000000;
        $pkp          = max(0, $nettoTahunan - $ptkp);

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

        return $this->ok([
            'year'         => $year,
            'symbol'       => $symbol,
            'yearly'       => $yearly,
            'pph_final' => [
                'total_omset'    => $totalOmset,
                'yearly_tax'     => $isFree ? 0 : $yearlyTaxUmkm,
                'monthly_tax'    => $isFree ? 0 : $monthlyTaxUmkm,
                'is_free'        => $isFree,
                'threshold'      => 500000000,
            ],
            'pph_21' => [
                'total_income'   => $totalOmset,
                'biaya_jabatan'  => $biayaJabatan,
                'netto_tahunan'  => $nettoTahunan,
                'ptkp'           => $ptkp,
                'pkp'            => $pkp,
                'yearly_tax'     => $taxPph21,
                'monthly_tax'    => $taxPph21 / 12,
                'ptkp_status'    => 'TK/0',
            ],
        ]);
    }
}
