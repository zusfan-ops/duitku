<?php

namespace App\Controllers\Api;

use App\Models\CategoryModel;
use App\Models\SavingsGoalModel;
use App\Models\SettingModel;
use App\Models\UserModel;

class SettingController extends ApiController
{
    protected SettingModel    $settingModel;
    protected CategoryModel   $catModel;
    protected UserModel       $userModel;
    protected SavingsGoalModel $goalModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
        $this->catModel     = new CategoryModel();
        $this->userModel    = new UserModel();
        $this->goalModel    = new SavingsGoalModel();
    }

    public function index()
    {
        $userId   = $this->uid();
        $settings = $this->settingModel->getAll($userId);
        $user     = $this->userModel->find($userId);
        $now      = date('Y-m');

        $db        = \Config\Database::connect();
        $recurring = $db->query("
            SELECT r.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color
            FROM recurring_transactions r
            LEFT JOIN categories c ON c.id = r.category_id
            WHERE r.user_id = ?
            ORDER BY r.next_date ASC
        ", [$userId])->getResultArray();

        $avatarImage = null;
        $imgFile     = $this->settingModel->get($userId, 'avatar_image');
        if ($imgFile && file_exists(FCPATH . 'uploads/avatars/' . $imgFile)) {
            $avatarImage = '/uploads/avatars/' . $imgFile;
        }

        $avatarJson = $user ? json_decode($user['avatar'], true) : ['initials' => 'U', 'color' => '#2D5A27'];

        return $this->ok([
            'settings'     => $settings,
            'categories'   => $this->catModel->getForUser($userId),
            'currency'     => $settings['currency'] ?? 'IDR',
            'symbol'       => $settings['currency_symbol'] ?? 'Rp',
            'budget'       => (float) ($settings['budget_' . $now] ?? 0),
            'monthKey'     => $now,
            'recurring'    => $recurring,
            'user'         => [
                'id'         => (int) ($user['id'] ?? 0),
                'name'       => $user['name'] ?? '',
                'email'      => $user['email'] ?? '',
                'initials'   => $avatarJson['initials'] ?? 'U',
                'color'      => $avatarJson['color'] ?? '#2D5A27',
                'avatarImage'=> $avatarImage,
            ],
            'savings'      => [
                'name'   => $settings['savings_name'] ?? '',
                'target' => (float) ($settings['savings_target'] ?? 0),
                'saved'  => (float) ($settings['savings_saved'] ?? 0),
            ],
            'savings_goals' => $this->goalModel->getForUser($userId),
        ]);
    }

    public function saveCurrency()
    {
        $userId   = $this->uid();
        $json     = $this->request->getJSON(true) ?? [];
        $currency = $json['currency'] ?? '';

        $map = [
            'IDR' => ['symbol' => 'Rp',  'label' => 'Rupiah'],
            'USD' => ['symbol' => '$',   'label' => 'Dollar AS'],
            'SGD' => ['symbol' => 'S$',  'label' => 'Dollar Singapura'],
            'MYR' => ['symbol' => 'RM',  'label' => 'Ringgit Malaysia'],
        ];

        if (!isset($map[$currency])) {
            return $this->fail('Mata uang tidak valid.');
        }

        $this->settingModel->setPref($userId, 'currency', $currency);
        $this->settingModel->setPref($userId, 'currency_symbol', $map[$currency]['symbol']);

        return $this->ok(['symbol' => $map[$currency]['symbol']]);
    }

    public function saveBudget()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $month  = $json['month'] ?? date('Y-m');
        $amount = $this->amount($json['amount'] ?? 0);

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return $this->fail('Bulan tidak valid.');
        }

        $this->settingModel->setPref($userId, 'budget_' . $month, $amount);
        return $this->ok(['amount' => $amount]);
    }

    public function saveProfile()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $name   = trim($json['name'] ?? '');
        $email  = trim($json['email'] ?? '');

        if (strlen($name) < 2) {
            return $this->fail('Nama minimal 2 karakter.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('Format email tidak valid.');
        }

        $existing = $this->userModel->where('email', $email)->where('id !=', $userId)->first();
        if ($existing) {
            return $this->fail('Email sudah digunakan.');
        }

        $data = ['name' => $name, 'email' => $email];

        $password = $json['password'] ?? '';
        if ($password && strlen($password) >= 6) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        } elseif ($password && strlen($password) > 0) {
            return $this->fail('Password minimal 6 karakter.');
        }

        $newAvatar = $this->userModel->generateAvatar($name);
        $data['avatar'] = $newAvatar;

        $this->userModel->update($userId, $data);

        return $this->ok(['name' => $name, 'email' => $email, 'avatar' => $newAvatar]);
    }

    public function saveAvatar()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];

        if (empty($json['image_base64'])) {
            return $this->fail('File tidak valid.');
        }

        if (!preg_match('/^data:image\/(png|jpe?g|webp|gif);base64,(.+)$/i', $json['image_base64'], $m)) {
            return $this->fail('Format file harus JPG, PNG, WebP, atau GIF.');
        }

        $data = base64_decode($m[2], true);
        if ($data === false || strlen($data) > 2 * 1024 * 1024) {
            return $this->fail('Ukuran file maksimal 2MB.');
        }

        $mime  = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
        $oldImage = $this->settingModel->get($userId, 'avatar_image');
        if ($oldImage && file_exists(FCPATH . 'uploads/avatars/' . $oldImage)) {
            unlink(FCPATH . 'uploads/avatars/' . $oldImage);
        }

        $dir = FCPATH . 'uploads/avatars';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $name = 'avatar_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $mime;
        file_put_contents($dir . '/' . $name, $data);
        $this->settingModel->setPref($userId, 'avatar_image', $name);

        return $this->ok(['image' => '/uploads/avatars/' . $name]);
    }

    public function saveSavings()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $name   = trim($json['savings_name'] ?? '');
        $target = $this->amount($json['savings_target'] ?? 0);
        $saved  = $this->amount($json['savings_saved'] ?? 0);

        if (!$name || $target <= 0) {
            return $this->fail('Nama dan target wajib diisi.');
        }

        $this->settingModel->setPref($userId, 'savings_name', $name);
        $this->settingModel->setPref($userId, 'savings_target', $target);
        $this->settingModel->setPref($userId, 'savings_saved', max(0, $saved));

        return $this->ok();
    }

    public function deleteSavings()
    {
        $userId = $this->uid();
        $this->settingModel->setPref($userId, 'savings_name', '');
        $this->settingModel->setPref($userId, 'savings_target', 0);
        $this->settingModel->setPref($userId, 'savings_saved', 0);
        return $this->ok();
    }

    public function saveNote()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $note   = $json['note'] ?? '';
        $now    = date('Y-m');

        $this->settingModel->setPref($userId, 'note_' . $now, $note);
        return $this->ok();
    }
}
