<?php

namespace App\Controllers\Api;

use App\Models\CategoryModel;
use App\Models\SettingModel;
use App\Models\UserModel;
use App\Models\WalletModel;

class AuthController extends ApiController
{
    protected UserModel     $userModel;
    protected SettingModel  $settingModel;
    protected CategoryModel $categoryModel;
    protected WalletModel   $walletModel;

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->settingModel  = new SettingModel();
        $this->categoryModel = new CategoryModel();
        $this->walletModel   = new WalletModel();
    }

    public function login()
    {
        $json     = $this->request->getJSON(true) ?? [];
        $email    = trim($json['email'] ?? '');
        $password = $json['password'] ?? '';
        $device   = trim($json['device'] ?? 'android');

        if (!$email || !$password) {
            return $this->fail('Email dan password wajib diisi.');
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            return $this->fail('Email atau password salah.');
        }

        $token = $this->issueToken((int) $user['id'], $device);

        return $this->ok([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ]);
    }

    public function register()
    {
        $json    = $this->request->getJSON(true) ?? [];
        $name    = trim($json['name'] ?? '');
        $email   = trim($json['email'] ?? '');
        $password = $json['password'] ?? '';
        $confirm = $json['password_confirm'] ?? '';
        $device  = trim($json['device'] ?? 'android');

        if (strlen($name) < 2) {
            return $this->fail('Nama minimal 2 karakter.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('Format email tidak valid.');
        }
        if (strlen($password) < 6) {
            return $this->fail('Password minimal 6 karakter.');
        }
        if ($password !== $confirm) {
            return $this->fail('Konfirmasi password tidak cocok.');
        }
        if ($this->userModel->findByEmail($email)) {
            return $this->fail('Email sudah terdaftar.');
        }

        $avatar = $this->userModel->generateAvatar($name);
        $userId = $this->userModel->insert([
            'name'     => $name,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'avatar'   => $avatar,
        ]);

        if (!$userId) {
            return $this->fail('Registrasi gagal. Coba lagi.');
        }

        $this->settingModel->setPref($userId, 'currency', 'IDR');
        $this->settingModel->setPref($userId, 'currency_symbol', 'Rp');

        // Default wallet
        $this->walletModel->insert([
            'user_id'         => $userId,
            'name'            => 'Kas / Dompet',
            'type'            => 'cash',
            'icon'            => '💵',
            'color'           => '#0AA956',
            'initial_balance' => 0,
            'is_default'      => 1,
            'sort_order'      => 0,
        ]);

        $user  = $this->userModel->find($userId);
        $token = $this->issueToken($userId, $device);

        return $this->ok([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ]);
    }

    public function me()
    {
        $user = $this->userModel->find($this->uid());
        if (!$user) {
            return $this->fail('User tidak ditemukan.', 401);
        }
        return $this->ok(['user' => $this->userPayload($user)]);
    }

    public function logout()
    {
        $auth = $this->request->getHeaderLine('Authorization');
        if (preg_match('/Bearer\s+([A-Za-z0-9]{32,})/i', $auth, $m)) {
            \Config\Database::connect()
                ->table('api_tokens')
                ->where('token', hash('sha256', $m[1]))
                ->delete();
        }
        return $this->ok();
    }

    private function issueToken(int $userId, string $device): string
    {
        $plain = bin2hex(random_bytes(32));
        $db    = \Config\Database::connect();
        $db->table('api_tokens')->insert([
            'user_id'    => $userId,
            'token'      => hash('sha256', $plain),
            'device'     => $device,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year')),
        ]);
        return $plain;
    }

    private function userPayload(array $user): array
    {
        $avatarJson = $user['avatar'] ? json_decode($user['avatar'], true) : ['initials' => 'U', 'color' => '#2D5A27'];

        $avatarImage = null;
        $imgFile     = $this->settingModel->get((int) $user['id'], 'avatar_image');
        if ($imgFile && file_exists(FCPATH . 'uploads/avatars/' . $imgFile)) {
            $avatarImage = '/uploads/avatars/' . $imgFile;
        }

        return [
            'id'        => (int) $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'initials'  => $avatarJson['initials'] ?? 'U',
            'color'     => $avatarJson['color'] ?? '#2D5A27',
            'avatarImage' => $avatarImage,
        ];
    }
}
