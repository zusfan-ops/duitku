<?php

namespace App\Controllers;

class FaqController extends BaseController
{
    /**
     * Halaman FAQ (Pertanyaan yang Sering Diajukan)
     * GET /faq
     */
    public function index()
    {
        return view('faq/index', [
            'pageTitle' => 'Pusat Bantuan & FAQ',
        ]);
    }
}
