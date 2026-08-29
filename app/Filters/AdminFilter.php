<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = session()->get('user_id');
        if (!session()->get('logged_in') || !$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Ambil role langsung dari database agar selalu akurat dengan perubahan di DB
        try {
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            $role = strtolower(trim((string)($user['role'] ?? 'user')));
            session()->set('user_role', $role);
        } catch (\Throwable $e) {
            $role = 'user';
        }

        $allowedRoles = ['administrator', 'admin'];
        if (!in_array($role, $allowedRoles, true)) {
            $email = session()->get('user_email') ?? 'User';
            return redirect()->to('/')->with('error', "Akses Ditolak: Akun Anda ({$email}) saat ini memiliki role '{$role}'. Diperlukan role 'administrator'. Silakan update kolom role pada tabel users di database.");
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }
}
