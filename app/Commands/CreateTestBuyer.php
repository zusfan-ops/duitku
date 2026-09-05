<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;
use App\Models\SettingModel;

class CreateTestBuyer extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:create-buyer';
    protected $description = 'Create or reset test buyer user for chat simulation';

    public function run(array $params)
    {
        $userModel = new UserModel();
        $settingModel = new SettingModel();

        $email = 'buyer_test@duitku.com';
        $user = $userModel->findByEmail($email);

        if ($user) {
            CLI::write("User test buyer sudah ada (ID: {$user['id']}, Email: {$user['email']})", 'green');
            return;
        }

        $avatar = $userModel->generateAvatar('Budi Pembeli');
        $userId = $userModel->insert([
            'name'     => 'Budi Pembeli',
            'username' => 'budipembeli',
            'email'    => $email,
            'phone'    => '081299887766',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'avatar'   => $avatar,
            'role'     => 'user',
        ]);

        if ($userId) {
            $settingModel->setPref($userId, 'currency', 'IDR');
            $settingModel->setPref($userId, 'currency_symbol', 'Rp');
            CLI::write("User test buyer BERHASIL dibuat! ID: {$userId}, Email: {$email}, Password: password123", 'green');
        } else {
            CLI::error("Gagal membuat user test buyer.");
        }
    }
}
