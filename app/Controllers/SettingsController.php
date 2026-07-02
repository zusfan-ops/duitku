<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\SettingModel;
use App\Models\UserModel;

class SettingsController extends BaseController
{
    protected CategoryModel $catModel;
    protected SettingModel  $settingModel;
    protected UserModel     $userModel;

    public function __construct()
    {
        $this->catModel     = new CategoryModel();
        $this->settingModel = new SettingModel();
        $this->userModel    = new UserModel();
    }

    public function index(): string
    {
        $userId     = session()->get('user_id');
        $settings   = $this->settingModel->getAll($userId);
        $categories = $this->catModel->getForUser($userId);
        $user       = $this->userModel->find($userId);
        $now        = date('Y-m');

        // Recurring transactions list
        $db        = \Config\Database::connect();
        $recurring = $db->query("
            SELECT r.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color
            FROM recurring_transactions r
            LEFT JOIN categories c ON c.id = r.category_id
            WHERE r.user_id = ?
            ORDER BY r.next_date ASC
        ", [$userId])->getResultArray();

        return view('settings/index', [
            'pageTitle'  => 'Pengaturan',
            'settings'   => $settings,
            'categories' => $categories,
            'currency'   => $settings['currency']        ?? 'IDR',
            'symbol'     => $settings['currency_symbol'] ?? 'Rp',
            'user'       => $user,
            'budget'     => (float)($settings['budget_' . $now] ?? 0),
            'monthKey'   => $now,
            'recurring'  => $recurring,
        ]);
    }

    // POST /settings/currency
    public function saveCurrency()
    {
        $userId   = session()->get('user_id');
        $currency = $this->request->getPost('currency');

        $map = [
            'IDR' => ['symbol' => 'Rp',  'label' => 'Rupiah'],
            'USD' => ['symbol' => '$',   'label' => 'Dollar AS'],
            'SGD' => ['symbol' => 'S$',  'label' => 'Dollar Singapura'],
            'MYR' => ['symbol' => 'RM',  'label' => 'Ringgit Malaysia'],
        ];

        if (!isset($map[$currency])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Mata uang tidak valid.']);
        }

        $this->settingModel->setPref($userId, 'currency', $currency);
        $this->settingModel->setPref($userId, 'currency_symbol', $map[$currency]['symbol']);

        return $this->response->setJSON(['success' => true, 'symbol' => $map[$currency]['symbol']]);
    }

    // POST /settings/budget
    public function saveBudget()
    {
        $userId = session()->get('user_id');
        $month  = $this->request->getPost('month') ?: date('Y-m');
        $amount = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('amount') ?? '0');

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Bulan tidak valid.']);
        }

        $this->settingModel->setPref($userId, 'budget_' . $month, $amount);
        return $this->response->setJSON(['success' => true, 'amount' => $amount]);
    }

    // POST /settings/profile
    public function saveProfile()
    {
        $userId = session()->get('user_id');
        $name   = trim($this->request->getPost('name') ?? '');
        $email  = trim($this->request->getPost('email') ?? '');

        if (strlen($name) < 2) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama minimal 2 karakter.']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Format email tidak valid.']);
        }

        // Check if email is taken by another user
        $existing = $this->userModel->where('email', $email)->where('id !=', $userId)->first();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Email sudah digunakan.']);
        }

        $data = ['name' => $name, 'email' => $email];

        $password = $this->request->getPost('password');
        if ($password && strlen($password) >= 6) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        } elseif ($password && strlen($password) > 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Password minimal 6 karakter.']);
        }

        $this->userModel->update($userId, $data);

        // Regenerate avatar if name changed
        $user = $this->userModel->find($userId);
        $newAvatar = $this->userModel->generateAvatar($name);
        $this->userModel->update($userId, ['avatar' => $newAvatar]);

        // Update session
        session()->set([
            'user_name'   => $name,
            'user_email'  => $email,
            'user_avatar' => $newAvatar,
        ]);

        return $this->response->setJSON(['success' => true, 'name' => $name, 'email' => $email, 'avatar' => $newAvatar]);
    }

    // POST /settings/avatar
    public function saveAvatar()
    {
        $userId = session()->get('user_id');
        $file   = $this->request->getFile('avatar');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setJSON(['success' => false, 'message' => 'File tidak valid.']);
        }

        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Format file harus JPG, PNG, WebP, atau GIF.']);
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ukuran file maksimal 2MB.']);
        }

        // Delete old avatar image
        $oldImage = $this->settingModel->get($userId, 'avatar_image');
        if ($oldImage && file_exists(FCPATH . 'uploads/avatars/' . $oldImage)) {
            unlink(FCPATH . 'uploads/avatars/' . $oldImage);
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/avatars', $newName);
        $this->settingModel->setPref($userId, 'avatar_image', $newName);

        return $this->response->setJSON(['success' => true, 'image' => '/uploads/avatars/' . $newName]);
    }

    // POST /settings/category/store
    public function storeCategory()
    {
        $userId = session()->get('user_id');
        $name   = trim($this->request->getPost('name'));
        $type   = $this->request->getPost('type');
        $icon   = $this->request->getPost('icon') ?: 'other';
        $color  = $this->request->getPost('color') ?: '#6B7280';

        if (!$name || !in_array($type, ['income', 'expense'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak valid.']);
        }

        $id = $this->catModel->insert([
            'user_id'    => $userId,
            'name'       => $name,
            'type'       => $type,
            'icon'       => $icon,
            'color'      => $color,
            'is_default' => 0,
            'sort_order' => 99,
        ]);

        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    // POST /settings/category/delete/{id}
    public function deleteCategory(int $id)
    {
        $userId  = session()->get('user_id');
        $deleted = $this->catModel->deleteForUser($id, $userId);

        if ($deleted) {
            return $this->response->setJSON(['success' => true]);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Tidak dapat dihapus.']);
    }

    // POST /settings/savings
    public function saveSavings()
    {
        $userId = session()->get('user_id');
        $name   = trim($this->request->getPost('savings_name') ?? '');
        $target = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('savings_target') ?? '0');
        $saved  = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('savings_saved') ?? '0');

        if (!$name || $target <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama dan target wajib diisi.']);
        }

        $this->settingModel->setPref($userId, 'savings_name', $name);
        $this->settingModel->setPref($userId, 'savings_target', $target);
        $this->settingModel->setPref($userId, 'savings_saved', max(0, $saved));

        return $this->response->setJSON(['success' => true]);
    }

    // POST /settings/savings/delete
    public function deleteSavings()
    {
        $userId = session()->get('user_id');
        $this->settingModel->setPref($userId, 'savings_name', '');
        $this->settingModel->setPref($userId, 'savings_target', 0);
        $this->settingModel->setPref($userId, 'savings_saved', 0);
        return $this->response->setJSON(['success' => true]);
    }

    // POST /settings/note
    public function saveNote()
    {
        $userId = session()->get('user_id');
        $note   = $this->request->getPost('note') ?? '';
        $now    = date('Y-m');

        $this->settingModel->setPref($userId, 'note_' . $now, $note);
        return $this->response->setJSON(['success' => true]);
    }
}
