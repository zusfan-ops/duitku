<?php

namespace App\Controllers;

class FeaturesController extends BaseController
{
    public function index(): string
    {
        return view('features/index', [
            'pageTitle' => 'Layanan & Fitur',
        ]);
    }
}
