<?php

namespace App\Controllers;

use App\Models\TvChannelModel;

class TvController extends BaseController
{
    protected TvChannelModel $tvModel;

    public function __construct()
    {
        $this->tvModel = new TvChannelModel();
    }

    public function index()
    {
        $category   = $this->request->getGet('category');
        $channels   = $this->tvModel->getActiveChannels($category);
        $categories = $this->tvModel->getCategories();

        $selectedId = (int) ($this->request->getGet('play') ?? 0);
        $currentChannel = null;
        if ($selectedId > 0) {
            foreach ($channels as $c) {
                if ((int)$c['id'] === $selectedId) {
                    $currentChannel = $c;
                    break;
                }
            }
        }
        if (!$currentChannel && !empty($channels)) {
            $currentChannel = $channels[0];
        }

        $data = [
            'pageTitle'      => 'TV & Live Streaming — DuitKu',
            'channels'       => $channels,
            'categories'     => $categories,
            'selectedCat'    => $category ?? 'Semua',
            'currentChannel' => $currentChannel,
        ];

        return view('tv/index', $data);
    }

    public function chats()
    {
        $chatModel = new \App\Models\TvChatModel();
        $afterId   = (int) ($this->request->getGet('after_id') ?? 0);
        $messages  = $chatModel->getRecentChats(50, $afterId);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $messages,
        ]);
    }

    public function sendChat()
    {
        $userId   = session()->get('user_id');
        $userName = session()->get('user_name') ?? 'Pengguna';
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'Silakan login terlebih dahulu.',
            ]);
        }

        $json    = $this->request->getJSON(true);
        $message = $json['message'] ?? $this->request->getPost('message') ?? '';

        try {
            $chatModel = new \App\Models\TvChatModel();
            $chat = $chatModel->postMessage((int)$userId, (string)$userName, (string)$message);

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $chat,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
