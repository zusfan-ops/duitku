<?php

namespace App\Filters;

use App\Libraries\ApiAuth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = $request->getHeaderLine('Authorization');

        if (preg_match('/Bearer\s+([A-Za-z0-9]{32,})/i', $auth, $m)) {
            $plain = $m[1];
            $db    = \Config\Database::connect();
            $row   = $db->table('api_tokens')
                        ->where('token', hash('sha256', $plain))
                        ->where('expires_at >', date('Y-m-d H:i:s'))
                        ->get()
                        ->getRow();

            if ($row) {
                ApiAuth::set((int) $row->user_id);
                return;
            }
        }

        ApiAuth::clear();
        $response = service('response');
        return $response
            ->setStatusCode(401)
            ->setContentType('application/json')
            ->setJSON(['success' => false, 'message' => 'Unauthorized']);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        ApiAuth::clear();
    }
}
