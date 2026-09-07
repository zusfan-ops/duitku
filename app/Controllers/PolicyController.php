<?php

namespace App\Controllers;

class PolicyController extends BaseController
{
    public function privacy()
    {
        return view('policies/privacy', ['pageTitle' => 'Kebijakan Privasi']);
    }

    public function moderation()
    {
        return view('policies/moderation', ['pageTitle' => 'Kebijakan Moderasi Konten']);
    }
}
