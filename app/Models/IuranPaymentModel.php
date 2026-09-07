<?php

namespace App\Models;

use CodeIgniter\Model;

class IuranPaymentModel extends Model
{
    protected $table         = 'neighborhood_iuran_payments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'neighborhood_id',
        'config_id',
        'resident_user_id',
        'period_month',
        'amount_expected',
        'amount_paid',
        'status',
        'paid_at',
        'paid_via',
        'kas_ledger_id',
        'notes',
        'reminder_sent',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Pastikan catatan pembayaran per warga per periode tersedia.
     * Dipanggil saat lihat laporan bulanan; buat record unpaid utk warga verified yg belum punya.
     */
    public function ensurePeriodRows(int $neighborhoodId, string $periodMonth, array $residentUserIds, int $configId, float $expected): int
    {
        $created = 0;
        foreach ($residentUserIds as $uid) {
            $exists = $this->where('config_id', $configId)
                ->where('resident_user_id', $uid)
                ->where('period_month', $periodMonth)
                ->first();
            if (!$exists) {
                $this->insert([
                    'neighborhood_id' => $neighborhoodId,
                    'config_id'       => $configId,
                    'resident_user_id' => $uid,
                    'period_month'    => $periodMonth,
                    'amount_expected' => $expected,
                    'amount_paid'     => 0,
                    'status'          => 'unpaid',
                ]);
                $created++;
            }
        }
        return $created;
    }

    /**
     * Ambil catatan pembayaran iuran dengan info nama warga untuk periode bulan.
     */
    public function getForPeriod(int $neighborhoodId, string $periodMonth, int $configId): array
    {
        return $this->db->query("
            SELECT p.*,
                   u.name AS resident_name,
                   u.house_number,
                   u.avatar
            FROM neighborhood_iuran_payments p
            LEFT JOIN users u ON u.id = p.resident_user_id
            WHERE p.neighborhood_id = ?
              AND p.period_month = ?
              AND p.config_id = ?
            ORDER BY u.name ASC
        ", [$neighborhoodId, $periodMonth, $configId])->getResultArray();
    }

    /**
     * Ringkasan iuran untuk periode tertentu: total expected, terkumpul, tunggakan.
     */
    public function getSummary(int $neighborhoodId, string $periodMonth, int $configId): array
    {
        $row = $this->db->query("
            SELECT
                COUNT(id)                                                        AS total_warga,
                SUM(amount_expected)                                            AS total_expected,
                SUM(amount_paid)                                                AS total_paid,
                SUM(CASE WHEN status IN ('paid') THEN 1 ELSE 0 END)             AS paid_count,
                SUM(CASE WHEN status IN ('partial') THEN 1 ELSE 0 END)          AS partial_count,
                SUM(CASE WHEN status IN ('unpaid','overdue') THEN 1 ELSE 0 END) AS unpaid_count,
                SUM(CASE WHEN status IN ('unpaid','overdue','partial')
                         THEN (amount_expected - amount_paid) ELSE 0 END)        AS total_arrears
            FROM neighborhood_iuran_payments
            WHERE neighborhood_id = ? AND period_month = ? AND config_id = ?
        ", [$neighborhoodId, $periodMonth, $configId])->getRowArray();

        return [
            'total_warga'    => (int)($row['total_warga'] ?? 0),
            'total_expected' => (float)($row['total_expected'] ?? 0),
            'total_paid'     => (float)($row['total_paid'] ?? 0),
            'paid_count'     => (int)($row['paid_count'] ?? 0),
            'partial_count'  => (int)($row['partial_count'] ?? 0),
            'unpaid_count'   => (int)($row['unpaid_count'] ?? 0),
            'total_arrears'  => (float)($row['total_arrears'] ?? 0),
        ];
    }

    /**
     * Tandai pembayaran iuran. Otomatis link ke kas RT jika paid_via = 'kas_rt'.
     * Mengembalikan array [success, status, kas_id].
     */
    public function markPaid(int $id, int $userId, array $data): array
    {
        $rec = $this->find($id);
        if (!$rec) {
            return ['success' => false, 'message' => 'Catatan iuran tidak ditemukan.'];
        }

        $amount = (float)($data['amount'] ?? $rec['amount_expected']);
        $remaining = (float)$rec['amount_expected'] - (float)$rec['amount_paid'];
        if ($amount <= 0 || $amount > ($remaining + 0.01)) {
            return ['success' => false, 'message' => 'Nominal pembayaran tidak valid.'];
        }

        $newPaid = (float)$rec['amount_paid'] + $amount;
        $newStatus = $newPaid >= (float)$rec['amount_expected'] - 0.01 ? 'paid' : 'partial';

        $update = [
            'amount_paid' => $newPaid,
            'status'      => $newStatus,
            'paid_via'    => $data['paid_via'] ?? 'cash',
            'notes'       => $data['notes'] ?? $rec['notes'],
        ];
        if ($newStatus === 'paid') {
            $update['paid_at'] = $data['paid_at'] ?? date('Y-m-d H:i:s');
        }

        // Auto-link ke kas RT (income) jika dibayar via kas_rt / selalu catat ke kas RT
        $kasId = null;
        if (($data['link_kas'] ?? true) && !$rec['kas_ledger_id']) {
            $kasModel = new NeighborhoodKasModel();
            $kasId = $kasModel->insert([
                'neighborhood_id' => $rec['neighborhood_id'],
                'created_by'      => $userId,
                'type'            => 'in',
                'category'        => 'Iuran Warga',
                'amount'          => $amount,
                'date'            => date('Y-m-d'),
                'description'     => 'Iuran ' . $rec['period_month'] . ' — ' . ($data['resident_name'] ?? 'Warga'),
                'reference_type'  => 'iuran',
                'reference_id'    => $id,
            ]);
            if ($kasId) {
                $update['kas_ledger_id'] = $kasId;
            }
        } elseif ($rec['kas_ledger_id']) {
            $kasId = (int)$rec['kas_ledger_id'];
        }

        $this->update($id, $update);

        return [
            'success' => true,
            'status'  => $newStatus,
            'amount_paid' => $newPaid,
            'kas_id'  => $kasId,
        ];
    }

    /**
     * Ambil riwayat pembayaran seorang warga (dipakai untuk tampilan warga sendiri).
     */
    public function getMyPaymentHistory(int $residentUserId, int $limit = 12): array
    {
        return $this->where('resident_user_id', $residentUserId)
            ->orderBy('period_month', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
