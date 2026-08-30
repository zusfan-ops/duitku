<?php

namespace App\Controllers;

class ArcadeController extends BaseController
{
    public function index()
    {
        $data = [
            'pageTitle'  => 'DuitKu Arcade Mini-Games',
            'activeMenu' => 'arcade',
        ];

        return view('arcade/index', $data);
    }
}
