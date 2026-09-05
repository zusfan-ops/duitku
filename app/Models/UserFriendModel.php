<?php

namespace App\Models;

use CodeIgniter\Model;

class UserFriendModel extends Model
{
    protected $table            = 'user_friends';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'friend_id',
        'status',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    public function ensureTable(): void
    {
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `user_friends` (
                    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `user_id`     INT UNSIGNED NOT NULL,
                    `friend_id`   INT UNSIGNED NOT NULL,
                    `status`      ENUM('pending', 'accepted', 'rejected', 'blocked') DEFAULT 'pending',
                    `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `uk_friend_pair` (`user_id`, `friend_id`),
                    KEY `idx_uf_user` (`user_id`),
                    KEY `idx_uf_friend` (`friend_id`),
                    KEY `idx_uf_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (\Throwable $e) {
            log_message('error', 'Auto create user_friends table error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim permintaan pertemanan ke username target
     */
    public function sendRequest(int $userId, string $targetUsername): array
    {
        $this->ensureTable();
        $userModel = new UserModel();
        $target = $userModel->findByUsername($targetUsername);

        if (!$target) {
            return ['success' => false, 'message' => 'Pengguna dengan username "' . $targetUsername . '" tidak ditemukan.'];
        }

        $friendId = (int)$target['id'];
        if ($friendId === $userId) {
            return ['success' => false, 'message' => 'Anda tidak dapat menambahkan akun sendiri sebagai teman.'];
        }

        // Cek status pertemanan yang sudah ada (dua arah)
        $existing = $this->db->table($this->table)
            ->groupStart()
                ->where('user_id', $userId)->where('friend_id', $friendId)
            ->groupEnd()
            ->orGroupStart()
                ->where('user_id', $friendId)->where('friend_id', $userId)
            ->groupEnd()
            ->get()->getRowArray();

        if ($existing) {
            if ($existing['status'] === 'accepted') {
                return ['success' => false, 'message' => 'Anda dan ' . ($target['name'] ?: $targetUsername) . ' sudah berteman.'];
            }
            if ($existing['status'] === 'pending') {
                if ((int)$existing['user_id'] === $userId) {
                    return ['success' => false, 'message' => 'Permintaan pertemanan sudah dikirim sebelumnya dan sedang menunggu persetujuan.'];
                } else {
                    // Pihak target sebelumnya sudah mengirim request ke kita, langsung terima saja!
                    $this->update($existing['id'], ['status' => 'accepted']);
                    return [
                        'success' => true,
                        'message' => 'Permintaan pertemanan diterima! Sekarang Anda berteman dengan ' . ($target['name'] ?: $targetUsername) . '.',
                        'status'  => 'accepted',
                        'friend'  => $target,
                    ];
                }
            }
            if ($existing['status'] === 'rejected') {
                // Update kembali menjadi pending
                $this->update($existing['id'], [
                    'user_id'   => $userId,
                    'friend_id' => $friendId,
                    'status'    => 'pending'
                ]);
                return [
                    'success' => true,
                    'message' => 'Permintaan pertemanan berhasil dikirim ke ' . ($target['name'] ?: $targetUsername) . '!',
                    'status'  => 'pending',
                    'friend'  => $target,
                ];
            }
        }

        // Insert permintaan baru
        $this->insert([
            'user_id'   => $userId,
            'friend_id' => $friendId,
            'status'    => 'pending',
        ]);

        return [
            'success' => true,
            'message' => 'Permintaan pertemanan berhasil dikirim ke ' . ($target['name'] ?: $targetUsername) . '!',
            'status'  => 'pending',
            'friend'  => $target,
        ];
    }

    /**
     * Respon permintaan pertemanan (Terima / Tolak)
     */
    public function respondRequest(int $requestId, int $recipientId, string $action): array
    {
        $this->ensureTable();
        $req = $this->where('id', $requestId)->first();
        if (!$req) {
            return ['success' => false, 'message' => 'Permintaan tidak ditemukan.'];
        }

        if ((int)$req['friend_id'] !== $recipientId) {
            return ['success' => false, 'message' => 'Anda tidak memiliki hak untuk merespon permintaan ini.'];
        }

        if ($action === 'accept') {
            $this->update($requestId, ['status' => 'accepted']);
            $userModel = new UserModel();
            $sender = $userModel->find($req['user_id']);
            return [
                'success' => true,
                'message' => 'Permintaan pertemanan diterima! Anda sekarang berteman.',
                'status'  => 'accepted',
                'friend'  => $sender,
            ];
        } elseif ($action === 'reject') {
            $this->update($requestId, ['status' => 'rejected']);
            return [
                'success' => true,
                'message' => 'Permintaan pertemanan ditolak.',
                'status'  => 'rejected',
            ];
        }

        return ['success' => false, 'message' => 'Aksi tidak valid.'];
    }

    /**
     * Ambil daftar teman yang sudah disetujui (Accepted)
     */
    public function getFriends(int $userId): array
    {
        $this->ensureTable();
        $sql = "
            SELECT 
                uf.id AS friendship_id,
                uf.status,
                uf.created_at AS friends_since,
                u.id AS friend_id,
                u.name,
                u.username,
                u.phone,
                u.avatar
            FROM user_friends uf
            JOIN users u ON (u.id = CASE WHEN uf.user_id = ? THEN uf.friend_id ELSE uf.user_id END)
            WHERE (uf.user_id = ? OR uf.friend_id = ?)
              AND uf.status = 'accepted'
            ORDER BY u.name ASC
        ";

        return $this->db->query($sql, [$userId, $userId, $userId])->getResultArray();
    }

    /**
     * Ambil daftar permintaan pertemanan yang masuk (Pending received)
     */
    public function getIncomingRequests(int $userId): array
    {
        $this->ensureTable();
        $sql = "
            SELECT 
                uf.id AS request_id,
                uf.user_id AS requester_id,
                uf.created_at,
                u.name AS requester_name,
                u.username AS requester_username,
                u.avatar AS requester_avatar,
                u.phone AS requester_phone
            FROM user_friends uf
            JOIN users u ON u.id = uf.user_id
            WHERE uf.friend_id = ?
              AND uf.status = 'pending'
            ORDER BY uf.id DESC
        ";

        return $this->db->query($sql, [$userId])->getResultArray();
    }

    /**
     * Ambil daftar permintaan pertemanan yang dikirim (Pending sent)
     */
    public function getOutgoingRequests(int $userId): array
    {
        $this->ensureTable();
        $sql = "
            SELECT 
                uf.id AS request_id,
                uf.friend_id AS target_id,
                uf.created_at,
                u.name AS target_name,
                u.username AS target_username,
                u.avatar AS target_avatar
            FROM user_friends uf
            JOIN users u ON u.id = uf.friend_id
            WHERE uf.user_id = ?
              AND uf.status = 'pending'
            ORDER BY uf.id DESC
        ";

        return $this->db->query($sql, [$userId])->getResultArray();
    }

    /**
     * Cari pengguna berdasarkan username atau nama dengan informasi status pertemanan
     */
    public function searchUsers(string $query, int $myId): array
    {
        $this->ensureTable();
        $q = trim($query);
        if (strlen($q) < 2) return [];

        $cleanQ = ltrim($q, '@');
        $builder = $this->db->table('users');
        $builder->select('id, name, username, avatar, phone');
        $builder->where('id !=', $myId);
        $builder->groupStart()
            ->like('username', $cleanQ)
            ->orLike('name', $q)
        ->groupEnd();
        $builder->limit(15);
        $users = $builder->get()->getResultArray();

        // Cari relasi pertemanan untuk masing-masing user
        foreach ($users as &$u) {
            $uId = (int)$u['id'];
            $rel = $this->db->table($this->table)
                ->groupStart()
                    ->where('user_id', $myId)->where('friend_id', $uId)
                ->groupEnd()
                ->orGroupStart()
                    ->where('user_id', $uId)->where('friend_id', $myId)
                ->groupEnd()
                ->get()->getRowArray();

            if (!$rel) {
                $u['friend_status'] = 'none';
                $u['request_id']    = null;
            } else {
                if ($rel['status'] === 'accepted') {
                    $u['friend_status'] = 'friends';
                } elseif ($rel['status'] === 'pending') {
                    $u['friend_status'] = ((int)$rel['user_id'] === $myId) ? 'pending_sent' : 'pending_received';
                } else {
                    $u['friend_status'] = 'none';
                }
                $u['request_id'] = (int)$rel['id'];
            }
        }

        return $users;
    }

    /**
     * Cek apakah dua user berteman
     */
    public function isFriend(int $userId, int $otherId): bool
    {
        $this->ensureTable();
        $row = $this->db->table($this->table)
            ->where('status', 'accepted')
            ->groupStart()
                ->where('user_id', $userId)->where('friend_id', $otherId)
            ->groupEnd()
            ->orGroupStart()
                ->where('user_id', $otherId)->where('friend_id', $userId)
            ->groupEnd()
            ->get()->getRowArray();

        return !empty($row);
    }
}
