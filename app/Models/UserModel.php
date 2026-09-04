<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'name', 'username', 'email', 'phone', 'password', 'avatar', 'role'
    ];

    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';

    protected $validationRules = [
        'name'     => 'required|min_length[2]|max_length[100]',
        'email'    => 'permit_empty|max_length[150]',
        'phone'    => 'permit_empty|max_length[30]',
        'password' => 'required|min_length[6]',
    ];

    protected $validationMessages = [
        'name'     => ['required' => 'Nama wajib diisi.'],
        'password' => ['required' => 'Password wajib diisi.', 'min_length' => 'Password minimal 6 karakter.'],
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function findByPhone(string $phone): ?array
    {
        return $this->where('phone', $phone)->first();
    }

    public function findByUsername(string $username): ?array
    {
        return $this->where('LOWER(username)', strtolower(trim($username)))->first();
    }

    public function findByIdentifier(string $val): ?array
    {
        return $this->groupStart()
                    ->where('email', $val)
                    ->orWhere('phone', $val)
                    ->groupEnd()
                    ->first();
    }

    public function generateAvatar(string $name): string
    {
        $colors = ['#2D5A27', '#1E40AF', '#7C3AED', '#B45309', '#BE185D', '#0F766E'];
        $initials = strtoupper(substr($name, 0, 2));
        $colorIndex = ord($name[0]) % count($colors);
        return json_encode(['initials' => $initials, 'color' => $colors[$colorIndex]]);
    }

    public static function getReservedUsernames(): array
    {
        return [
            'login', 'register', 'logout', 'admin', 'api', 'features', 'fitur',
            'settings', 'pos', 'tv', 'barang', 'kendaraan', 'bills', 'hutang',
            'stats', 'activity', 'scan', 'emergency', 'zakat-pajak', 'pajak-zakat',
            'arcade', 'games', 'notifications', 'belanja', 'todo', 'savings',
            'wallets', 'marketplace', 'jual-beli-sewa', 'download', 'release',
            'migrate', 'u', 'p', 'm', 'menu', 'shop', 'toko', 'export', 'import',
            'search', 'backup', 'public', 'uploads', 'assets', 'css', 'js', 'images'
        ];
    }

    public function generateUniqueUsername(string $name, int $userId): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '', $name)));
        if (strlen($base) < 3) {
            $base = 'user' . $userId;
        }

        $reserved = self::getReservedUsernames();
        $candidate = $base;
        $counter = 1;

        while (in_array($candidate, $reserved) ||
               $this->where('LOWER(username)', $candidate)->where('id !=', $userId)->countAllResults() > 0) {
            $candidate = $base . $counter;
            $counter++;
        }

        return $candidate;
    }

    public function ensureUsername(int $userId): string
    {
        $user = $this->find($userId);
        if (!$user) return '';

        if (!empty($user['username'])) {
            return $user['username'];
        }

        $newUsername = $this->generateUniqueUsername($user['name'] ?? 'user', $userId);
        $this->update($userId, ['username' => $newUsername]);
        return $newUsername;
    }
}

