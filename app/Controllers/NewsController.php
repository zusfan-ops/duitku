<?php

namespace App\Controllers;

use App\Services\NewsService;

class NewsController extends BaseController
{
    /**
     * Web & PWA View: /berita
     */
    public function index()
    {
        $category = $this->request->getGet('category') ?? 'Semua';
        $source   = $this->request->getGet('source') ?? '';
        $search   = $this->request->getGet('search') ?? '';
        $page     = max(1, (int)($this->request->getGet('page') ?? 1));

        $newsData = NewsService::getNews([
            'category' => $category,
            'source'   => $source,
            'search'   => $search,
            'page'     => $page,
            'limit'    => 40,
        ]);

        $data = [
            'pageTitle'       => 'Berita Terkini',
            'news'            => $newsData['items'],
            'total'           => $newsData['total'],
            'page'            => $newsData['page'],
            'totalPages'      => $newsData['total_pages'],
            'categories'      => $newsData['categories'],
            'sources'         => $newsData['sources'],
            'selectedCategory'=> $category,
            'selectedSource'  => $source,
            'searchQuery'     => $search,
            'headlines'       => NewsService::getHeadlines(5),
        ];

        return view('news/index', $data);
    }

    /**
     * JSON API Endpoint: GET /api/berita
     * Digunakan oleh Flutter Native App dan PWA
     */
    public function apiList()
    {
        $category = $this->request->getGet('category') ?? '';
        $source   = $this->request->getGet('source') ?? '';
        $search   = $this->request->getGet('search') ?? '';
        $page     = max(1, (int)($this->request->getGet('page') ?? 1));
        $limit    = min(100, max(1, (int)($this->request->getGet('limit') ?? 40)));
        $refresh  = (bool)($this->request->getGet('refresh') ?? false);

        $newsData = NewsService::getNews([
            'category' => $category,
            'source'   => $source,
            'search'   => $search,
            'page'     => $page,
            'limit'    => $limit,
            'refresh'  => $refresh,
        ]);

        return $this->response->setJSON([
            'success'     => true,
            'total'       => $newsData['total'],
            'page'        => $newsData['page'],
            'limit'       => $newsData['limit'],
            'total_pages' => $newsData['total_pages'],
            'headlines'   => NewsService::getHeadlines(6),
            'items'       => $newsData['items'],
            'categories'  => $newsData['categories'],
            'sources'     => $newsData['sources'],
        ]);
    }

    /**
     * Force refresh cache: POST /api/berita/refresh
     */
    public function apiRefresh()
    {
        $items = NewsService::getAllItems(true);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Feed berita berhasil diperbarui',
            'count'   => count($items),
        ]);
    }
}
