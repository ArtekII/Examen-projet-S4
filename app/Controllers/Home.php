<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        if (! session()->has('client_id')) {
            return redirect()->to(site_url('client/connexion'));
        }
        return view('client/compte', [
            'title' => 'Espace client'
        ]);
    }
}
