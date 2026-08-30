<?php

namespace App\Controllers\Api;

use App\Models\TvChannelModel;

class TvController extends ApiController
{
    protected TvChannelModel $tvModel;

    public function __construct()
    {
        $this->tvModel = new TvChannelModel();
    }

    public function index()
    {
        $category = $this->request->getGet('category');
        $channels = $this->tvModel->getActiveChannels($category);
        $categories = $this->tvModel->getCategories();

        $payload = array_map(function ($c) {
            $logoUrl = $c['logo_url'];
            if ($logoUrl && !str_starts_with($logoUrl, 'http://') && !str_starts_with($logoUrl, 'https://')) {
                $logoUrl = base_url(ltrim($logoUrl, '/'));
            }
            return [
                'id'          => (int) $c['id'],
                'name'        => $c['name'],
                'category'    => $c['category'] ?? 'Nasional',
                'stream_url'  => $c['stream_url'],
                'logo_url'    => $logoUrl,
                'description' => $c['description'] ?? '',
                'sort_order'  => (int) ($c['sort_order'] ?? 0),
            ];
        }, $channels);

        return $this->ok([
            'channels'   => $payload,
            'categories' => array_merge(['Semua'], $categories),
        ]);
    }

    public function show(int $id)
    {
        $channel = $this->tvModel->find($id);
        if (!$channel || !$channel['is_active']) {
            return $this->fail('Channel TV tidak ditemukan atau sedang tidak aktif.', 404);
        }

        $logoUrl = $channel['logo_url'];
        if ($logoUrl && !str_starts_with($logoUrl, 'http://') && !str_starts_with($logoUrl, 'https://')) {
            $logoUrl = base_url(ltrim($logoUrl, '/'));
        }

        return $this->ok([
            'channel' => [
                'id'          => (int) $channel['id'],
                'name'        => $channel['name'],
                'category'    => $channel['category'] ?? 'Nasional',
                'stream_url'  => $channel['stream_url'],
                'logo_url'    => $logoUrl,
                'description' => $channel['description'] ?? '',
            ]
        ]);
    }
    public function chats()
    {
        $chatModel = new \App\Models\TvChatModel();
        $afterId   = (int) ($this->request->getGet('after_id') ?? 0);
        $messages  = $chatModel->getRecentChats(50, $afterId);

        return $this->ok([
            'messages' => $messages,
        ]);
    }

    public function sendChat()
    {
        $userId   = $this->user['id'] ?? null;
        $userName = $this->user['name'] ?? 'Pengguna';
        if (!$userId) {
            return $this->fail('Unauthenticated', 401);
        }

        $json    = $this->request->getJSON(true);
        $message = $json['message'] ?? $this->request->getPost('message') ?? '';

        try {
            $chatModel = new \App\Models\TvChatModel();
            $chat = $chatModel->postMessage((int)$userId, (string)$userName, (string)$message);

            return $this->ok([
                'message' => 'Pesan terkirim',
                'chat'    => $chat,
            ]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }
}
