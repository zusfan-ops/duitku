<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'key', 'value'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function get(int $userId, string $key, mixed $default = null): mixed
    {
        $row = $this->where('user_id', $userId)->where('key', $key)->first();
        return $row ? $row['value'] : $default;
    }

    public function setPref(int $userId, string $key, mixed $value): void
    {
        $existing = $this->where('user_id', $userId)->where('key', $key)->first();
        if ($existing) {
            $this->update($existing['id'], ['value' => $value]);
        } else {
            $this->insert(['user_id' => $userId, 'key' => $key, 'value' => $value]);
        }
    }

    public function getAll(int $userId): array
    {
        $rows = $this->where('user_id', $userId)->findAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
    }

    /**
     * Get full store profile for POS & Public Menu
     */
    public function getStoreProfile(int $userId): array
    {
        $all = $this->getAll($userId);
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        $defaultName = $user['name'] ? ('Toko ' . $user['name']) : 'Toko POS';
        $defaultSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $defaultName), '-')) ?: ('toko-' . $userId);

        return [
            'user_id'         => $userId,
            'owner_name'      => $user['name'] ?? 'Pemilik',
            'store_name'      => $all['store_name'] ?? $defaultName,
            'store_slug'      => $all['store_slug'] ?? $defaultSlug,
            'store_tagline'   => $all['store_tagline'] ?? 'Selamat Datang! Silakan pilih menu favorit Anda.',
            'store_address'   => $all['store_address'] ?? '',
            'store_phone'     => $all['store_phone'] ?? ($user['phone'] ?? ''),
            'store_qr_footer' => $all['store_qr_footer'] ?? 'Scan QR untuk melihat daftar menu & memesan langsung dari meja Anda.',
            'store_is_open'   => ($all['store_is_open'] ?? '1') === '1',
            'currency_symbol' => $all['currency_symbol'] ?? 'Rp',
            'currency'        => $all['currency'] ?? 'IDR',
        ];
    }

    /**
     * Find user ID from store slug (or ID)
     */
    public function findUserByStoreSlug(string $slug): ?array
    {
        $slug = trim(strtolower($slug));
        if (!$slug) return null;

        // 1. Direct match on store_slug setting
        $row = $this->where('key', 'store_slug')
                    ->where('LOWER(value)', $slug)
                    ->first();
        if ($row) {
            return $this->getStoreProfile((int)$row['user_id']);
        }

        // 2. Check if slug matches user id (e.g. 'toko-1' or '1')
        if (preg_match('/^(?:toko-)?(\d+)$/', $slug, $m)) {
            $uId = (int)$m[1];
            $userModel = new UserModel();
            if ($userModel->find($uId)) {
                return $this->getStoreProfile($uId);
            }
        }

        // 3. Check if slug matches slugified user name
        $userModel = new UserModel();
        $users = $userModel->findAll();
        foreach ($users as $u) {
            $userSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', 'toko ' . $u['name']), '-'));
            $userDirectSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $u['name']), '-'));
            if ($userSlug === $slug || $userDirectSlug === $slug) {
                return $this->getStoreProfile((int)$u['id']);
            }
        }

        return null;
    }
}

