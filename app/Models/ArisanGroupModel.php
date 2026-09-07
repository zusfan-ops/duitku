<?php

namespace App\Models;

use CodeIgniter\Model;

class ArisanGroupModel extends Model
{
    protected $table         = 'arisan_groups';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'neighborhood_id',
        'user_id',
        'name',
        'amount',
        'frequency',
        'total_members',
        'current_round',
        'status',
        'start_date',
        'description',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Daftar grup arisan yang diakses user (sebagai pemilik maupun anggota).
     */
    public function getForUser(int $userId): array
    {
        return $this->db->query("
            SELECT g.*, n.name AS neighborhood_name
            FROM arisan_groups g
            LEFT JOIN neighborhoods n ON n.id = g.neighborhood_id
            WHERE g.user_id = ?
               OR g.id IN (
                   SELECT a.group_id FROM arisan_members a WHERE a.user_id = ?
               )
            ORDER BY g.created_at DESC
        ", [$userId, $userId])->getResultArray();
    }

    /**
     * Detail grup + anggota + ringkasan pembayaran putaran berjalan.
     */
    public function getDetailWithMembers(int $groupId, int $userId): ?array
    {
        $group = $this->db->query("
            SELECT g.*, n.name AS neighborhood_name
            FROM arisan_groups g
            LEFT JOIN neighborhoods n ON n.id = g.neighborhood_id
            WHERE g.id = ?
        ", [$groupId])->getRowArray();

        if (!$group) return null;

        // Cek akses: pemilik atau anggota
        $isMember = $this->db->table('arisan_members')
            ->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->countAllResults() > 0;
        $group['can_access'] = (int)$group['user_id'] === (int)$userId || $isMember;
        $group['is_owner'] = (int)$group['user_id'] === (int)$userId;

        $memberModel = new ArisanMemberModel();
        $group['members'] = $memberModel->getForGroup($groupId);

        $paymentModel = new ArisanPaymentModel();
        $group['payments'] = $paymentModel->getForGroup($groupId);

        return $group;
    }

    /**
     * Lanjut putaran arisan: buat record payment utk semua anggota di round baru.
     * Mengembalikan jumlah record payment yg dibuat.
     */
    public function advanceRound(int $groupId): int
    {
        $group = $this->find($groupId);
        if (!$group) return 0;

        $nextRound = (int)$group['current_round'] + 1;
        $members = (new ArisanMemberModel())->where('group_id', $groupId)->findAll();

        $paymentModel = new ArisanPaymentModel();
        $created = 0;
        foreach ($members as $m) {
            $exists = $paymentModel->where('group_id', $groupId)
                ->where('member_id', $m['id'])
                ->where('round_number', $nextRound)
                ->first();
            if (!$exists) {
                $paymentModel->insert([
                    'group_id'     => $groupId,
                    'member_id'    => $m['id'],
                    'round_number' => $nextRound,
                    'amount'       => $group['amount'],
                    'status'       => 'unpaid',
                ]);
                $created++;
            }
        }

        $this->update($groupId, ['current_round' => $nextRound]);

        return $created;
    }

    /**
     * Status progres arisan terhadap total member.
     * Dipakai untuk progress bar UI.
     */
    public function getProgress(int $groupId): array
    {
        $group = $this->find($groupId);
        if (!$group) return ['current_round' => 0, 'total_members' => 0, 'percent' => 0];

        $total = max(1, (int)$group['total_members']);
        $round = (int)$group['current_round'];
        $percent = min(100, (int)round(($round / $total) * 100));

        return [
            'current_round' => $round,
            'total_members' => (int)$group['total_members'],
            'percent'       => $percent,
        ];
    }
}
