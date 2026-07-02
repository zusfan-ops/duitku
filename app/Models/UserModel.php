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
        'name', 'email', 'password', 'avatar'
    ];

    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';

    protected $validationRules = [
        'name'     => 'required|min_length[2]|max_length[100]',
        'email'    => 'required|valid_email|max_length[150]',
        'password' => 'required|min_length[6]',
    ];

    protected $validationMessages = [
        'name'     => ['required' => 'Nama wajib diisi.'],
        'email'    => ['required' => 'Email wajib diisi.', 'valid_email' => 'Format email tidak valid.'],
        'password' => ['required' => 'Password wajib diisi.', 'min_length' => 'Password minimal 6 karakter.'],
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function generateAvatar(string $name): string
    {
        $colors = ['#2D5A27', '#1E40AF', '#7C3AED', '#B45309', '#BE185D', '#0F766E'];
        $initials = strtoupper(substr($name, 0, 2));
        $colorIndex = ord($name[0]) % count($colors);
        return json_encode(['initials' => $initials, 'color' => $colors[$colorIndex]]);
    }
}
