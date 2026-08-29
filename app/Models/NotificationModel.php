<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'app_notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'title',
        'message',
        'type',
        'target',
        'user_id',
        'action_url',
        'is_pinned',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil notifikasi aktif yang relevan untuk user tertentu (broadcast all + target user)
     */
    public function getForUser(int $userId, int $limit = 30): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table . ' n');
        $builder->select('n.*, (CASE WHEN nr.id IS NOT NULL THEN 1 ELSE 0 END) AS is_read, nr.read_at');
        $builder->join('notification_reads nr', "nr.notification_id = n.id AND nr.user_id = {$userId}", 'left');
        $builder->groupStart()
                ->where('n.target', 'all')
                ->orWhere('n.user_id', $userId)
                ->groupEnd();
        $builder->orderBy('n.is_pinned', 'DESC');
        $builder->orderBy('n.created_at', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    /**
     * Hitung jumlah notifikasi belum dibaca untuk user
     */
    public function getUnreadCount(int $userId): int
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table . ' n');
        $builder->join('notification_reads nr', "nr.notification_id = n.id AND nr.user_id = {$userId}", 'left');
        $builder->groupStart()
                ->where('n.target', 'all')
                ->orWhere('n.user_id', $userId)
                ->groupEnd();
        $builder->where('nr.id IS NULL', null, false);

        return $builder->countAllResults();
    }

    /**
     * Tandai notifikasi sebagai telah dibaca
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $db = \Config\Database::connect();
        $exists = $db->table('notification_reads')
                     ->where('notification_id', $notificationId)
                     ->where('user_id', $userId)
                     ->countAllResults();

        if ($exists === 0) {
            return $db->table('notification_reads')->insert([
                'notification_id' => $notificationId,
                'user_id'         => $userId,
                'read_at'         => date('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }
}
