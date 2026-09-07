<?php

namespace App\Models;

use CodeIgniter\Model;

class ArisanMemberModel extends Model
{
    protected $table         = 'arisan_members';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'group_id',
        'user_id',
        'member_name',
        'phone',
        'rotation_order',
        'has_received',
        'received_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Daftar anggota kelompok arisan dengan info user (jika terhubung ke akun DuitKu).
     */
    public function getForGroup(int $groupId): array
    {
        return $this->db->query("
            SELECT m.*,
                   u.name  AS user_display_name,
                   u.avatar
            FROM arisan_members m
            LEFT JOIN users u ON u.id = m.user_id
            WHERE m.group_id = ?
            ORDER BY m.rotation_order ASC
        ", [$groupId])->getResultArray();
    }

    /**
     * Tandai member sudah menerima arisan pada putaran tertentu.
     */
    public function markReceived(int $memberId, int $groupId): bool
    {
        return $this->update($memberId, [
            'has_received' => 1,
            'received_at'  => date('Y-m-d'),
        ]);
    }

    /**
     * Tambah member dengan urutan giliran otomatis (rotation_order terbesar + 1).
     */
    public function addMember(int $groupId, array $data): int
    {
        $maxOrder = (int)$this->db->table('arisan_members')
            ->selectMax('rotation_order', 'max_order')
            ->where('group_id', $groupId)
            ->get()
            ->getRow()
            ->max_order;

        $data['group_id']       = $groupId;
        $data['rotation_order'] = $data['rotation_order'] ?? ($maxOrder + 1);
        $data['member_name']    = $data['member_name'] ?? '';

        return $this->insert($data);
    }
}
