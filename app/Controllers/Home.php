<?php

namespace App\Controllers;

use App\Models\OperationMouvement;
use App\Models\Operateurs;
use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index()
    {
        if (! session()->has('client_id')) {
            return redirect()->to(site_url('client/connexion'));
        }

        $operationMouvement = new OperationMouvement();
        $solde = $operationMouvement->getSoldeByClientId(session()->get('client_id'));

        $operateurModel = new Operateurs();
        $operateur = $operateurModel->find(session()->get('operateur_id'));

        return view('Client/solde', [
            'title' => 'Espace client',
            'solde' => $solde,
            'operateur' => $operateur,
        ]);
    }
}
