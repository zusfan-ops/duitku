<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\JellyfinService;
use CodeIgniter\HTTP\ResponseInterface;

class JellyfinController extends BaseController
{
    public function index(): ResponseInterface
    {
        $limit = (int) ($this->request->getGet('limit') ?? 30);
        if ($limit < 1 || $limit > 100) {
            $limit = 30;
        }

        $movies = JellyfinService::getMovies($limit);

        return $this->response->setJSON([
            'success' => true,
            'total'   => count($movies),
            'movies'  => $movies,
        ]);
    }
}
