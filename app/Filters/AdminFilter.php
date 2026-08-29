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

        // Cek role dari database / session
        $role = session()->get('user_role');
        if (!$role) {
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            $role = $user['role'] ?? 'user';
            session()->set('user_role', $role);
        }

        $allowedRoles = ['administrator', 'admin'];
        if (!in_array(strtolower((string)$role), $allowedRoles, true)) {
            return redirect()->to('/')->with('error', 'Akses ditolak. Anda tidak memiliki izin Administrator.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }
}
