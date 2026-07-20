<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Clients;

class ClientsController extends BaseController
{
    public function index()
    {
        return view('Client/connexion', [
            'title' => 'Connexion'
        ]
        );
    }

    public function login(){
        $numero = $this->request->getPost('numero');
        $clientModel = new Clients();
        $client = $clientModel->getClientByNumero($numero);

        if ($client) {
            session()->set('client_id', $client['id']);
            session()->set('nom_client', $client['nom_client']);

            return redirect()->to('client/compte')->with('success', 'Connexion réussie.'); 
        } else {
            return redirect()->back()->with('error', 'Numéro de téléphone incorrect.');
        }
    }

    public function solde(){
        $soldeModel = new SoldeClients();
        $solde = $soldeModel->getSoldeByClientId(session()->get('client_id'));
        return view('Client/solde', [
            'title' => 'Solde',
            'solde' => $solde
        ]);
    }
}
