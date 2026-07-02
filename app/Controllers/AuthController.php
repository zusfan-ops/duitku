<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\CategoryModel;
use App\Models\SettingModel;
use CodeIgniter\Controller;

class AuthController extends BaseController
{
    protected UserModel     $userModel;
    protected CategoryModel $categoryModel;
    protected SettingModel  $settingModel;

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->categoryModel = new CategoryModel();
        $this->settingModel  = new SettingModel();
    }

    // -------------------------------------------------------------------------
    // GET /login
    // -------------------------------------------------------------------------
    public function loginPage()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/');
        }
        return view('auth/login');
    }

    // -------------------------------------------------------------------------
    // POST /login
    // -------------------------------------------------------------------------
    public function login()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (!$email || !$password) {
            return redirect()->back()->with('error', 'Email dan password wajib diisi.');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau password salah.');
        }

        session()->set([
            'user_id'   => $user['id'],
            'user_name' => $user['name'],
            'user_email'=> $user['email'],
            'user_avatar'=> $user['avatar'],
            'logged_in' => true,
        ]);

        return redirect()->to('/');
    }

    // -------------------------------------------------------------------------
    // GET /register
    // -------------------------------------------------------------------------
    public function registerPage()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/');
        }
        return view('auth/register');
    }

    // -------------------------------------------------------------------------
    // POST /register
    // -------------------------------------------------------------------------
    public function register()
    {
        $name     = trim($this->request->getPost('name'));
        $email    = trim($this->request->getPost('email'));
        $password = $this->request->getPost('password');
        $confirm  = $this->request->getPost('password_confirm');

        if ($password !== $confirm) {
            return redirect()->back()->withInput()->with('error', 'Konfirmasi password tidak cocok.');
        }

        if ($this->userModel->findByEmail($email)) {
            return redirect()->back()->withInput()->with('error', 'Email sudah terdaftar.');
        }

        $avatar = $this->userModel->generateAvatar($name);
        $userId = $this->userModel->insert([
            'name'     => $name,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'avatar'   => $avatar,
        ]);

        if (!$userId) {
            return redirect()->back()->withInput()->with('error', 'Registrasi gagal. Coba lagi.');
        }

        // Default settings
        $this->settingModel->setPref($userId, 'currency', 'IDR');
        $this->settingModel->setPref($userId, 'currency_symbol', 'Rp');

        session()->set([
            'user_id'    => $userId,
            'user_name'  => $name,
            'user_email' => $email,
            'user_avatar'=> $avatar,
            'logged_in'  => true,
        ]);

        return redirect()->to('/')->with('success', 'Selamat datang, ' . $name . '!');
    }

    // -------------------------------------------------------------------------
    // GET /logout
    // -------------------------------------------------------------------------
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
