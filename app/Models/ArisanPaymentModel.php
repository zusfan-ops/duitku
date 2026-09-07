<?php

namespace App\Models;

use CodeIgniter\Model;

class ArisanPaymentModel extends Model
{
    protected $table         = 'arisan_payments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'group_id',
        'member_id',
        'round_number',
        'amount',
        'status',
        'paid_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Daftar pembayaran arisan sebuah grup dengan info member (untuk tampilan detail).
     */
    public function getForGroup(int $groupId, ?int $round = null): array
    {
        $q = $this->db->query("
            SELECT p.*, m.member_name, m.rotation_order
            FROM arisan_payments p
            LEFT JOIN arisan_members m ON m.id = p.member_id
            WHERE p.group_id = ?
              " . ($round !== null ? "AND p.round_number = ?" : "") . "
            ORDER BY p.round_number ASC, m.rotation_order ASC
        ", $round !== null ? [$groupId, $round] : [$groupId])->getResultArray();

        return $q;
    }

    /**
     * Tandai pembayaran setoran arisan member pada putaran tertentu.
     */
    public function markPaid(int $paymentId, int $groupId): array
    {
        $rec = $this->find($paymentId);
        if (!$rec || (int)$rec['group_id'] !== (int)$groupId) {
            return ['success' => false, 'message' => 'Setoran tidak ditemukan.'];
        }

        $this->update($paymentId, [
            'status'  => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'message' => 'Setoran berhasil dicatat.'];
    }

    /**
     * Ringkasan pembayaran untuk satu putaran: total terkumpul, jumlah sudah bayar.
     */
    public function getRoundSummary(int $groupId, int $round): array
    {
        $row = $this->db->query("
            SELECT
                COUNT(id)                                                             AS total_record,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END)                     AS paid_count,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END)                AS total_paid,
                SUM(CASE WHEN status = 'unpaid' THEN amount ELSE 0 END)              AS total_unpaid
            FROM arisan_payments
            WHERE group_id = ? AND round_number = ?
        ", [$groupId, $round])->getRowArray();

        return [
            'total_record' => (int)($row['total_record'] ?? 0),
            'paid_count'   => (int)($row['paid_count'] ?? 0),
            'total_paid'   => (float)($row['total_paid'] ?? 0),
            'total_unpaid' => (float)($row['total_unpaid'] ?? 0),
        ];
    }
}
