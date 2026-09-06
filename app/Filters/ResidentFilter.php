<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Libraries\ApiAuth;

class ResidentFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
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

        // Cek apakah user sudah terdaftar dan terverifikasi di suatu RT
        $neighborhoodId = (int)($user['neighborhood_id'] ?? 0);
        $status = $user['rt_verification_status'] ?? 'unregistered';

        if (!$neighborhoodId || $status !== 'verified') {
            if ($isApi) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON([
                        'success' => false, 
                        'message' => 'Anda belum terverifikasi sebagai warga di RT mana pun. Silakan masukkan kode unik RT atau tunggu persetujuan Ketua RT.',
                        'rt_verification_status' => $status
                    ]);
            }
            return redirect()->to('/neighborhood/join')->with('warning', 'Anda belum terhubung/terverifikasi di RT. Silakan pilih RT dan masukkan kode unik.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }
}
