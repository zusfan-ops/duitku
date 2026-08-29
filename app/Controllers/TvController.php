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
}
