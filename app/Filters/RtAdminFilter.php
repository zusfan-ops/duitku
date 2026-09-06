<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Libraries\ApiAuth;

class RtAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Cek dari Web Session atau API Token
        $userId = session()->get('user_id') ?: ApiAuth::id();
        $isApi = str_starts_with($request->getUri()->getPath(), 'api/');

        if (!$userId) {
            if ($isApi) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
            }
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            if ($isApi) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON(['success' => false, 'message' => 'Pengguna tidak ditemukan.']);
            }
            return redirect()->to('/login');
        }

        $role = strtolower(trim((string)($user['role'] ?? 'user')));
        $isRtAdmin = in_array($role, ['rt_admin', 'admin', 'administrator'], true);

        if (!$isRtAdmin) {
            if ($isApi) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['success' => false, 'message' => 'Akses ditolak: Fitur ini khusus untuk Ketua RT / Pengelola Lingkungan.']);
            }
            return redirect()->to('/neighborhood')->with('error', 'Akses ditolak: Fitur ini khusus untuk Ketua RT / Pengelola Lingkungan.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }
}
