<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ConfigurationsTransaction;
use App\Models\SoldeClientModel;

use App\Models\TypeOperationModel;
use App\Models\GainsFraisModel;
use App\Models\TotalGainsFraisModel;

use App\Models\CommissionsParOperateurModel;
class ConfigurationTransactionController extends BaseController
{
    public function index()
    {
        $model = new ConfigurationsTransaction();
        $typeModel = new TypeOperationModel();
        return view('Operateur/form', [
            'title' => 'Configuration des frais',
            'configurations' => $model->getAllOrderedById(),
            'types' => $typeModel->findAll(),
        ]);
    }

    public function store()
    {
        $model = new ConfigurationsTransaction();
        $data = $this->request->getPost();

        if (!$model->insert($data)) {
            $typeModel = new TypeOperationModel();

            return view('Operateur/form', [
                'title' => 'Configuration des frais',
                'configurations' => $model->getAllOrderedById(),
                'types' => $typeModel->findAll(),
                'validation' => $model->errors(),
            ]);
        }

        return redirect()->to('/operateur');
    }

    public function soldes()
    {
        $model = new SoldeClientModel();

        return view('Operateur/soldes', [
            'title' => 'Soldes des clients',
            'soldes' => $model->getAllOrderedByName(),
        ]);
    }

    public function gains()
    {
        $gainsModel = new GainsFraisModel();
        $totalModel = new TotalGainsFraisModel();
        $typeModel = new TypeOperationModel();

        $idType = $this->request->getGet('id_type_operation');
        $idTypeFiltre = is_numeric($idType) ? (int) $idType : null;
        $gains = $gainsModel->getByType($idTypeFiltre);

        $totalFiltre = array_sum(array_column($gains, 'montant_frais'));

        return view('Operateur/gains', [
            'title' => 'Gains sur les frais',
            'gains' => $gains,
            'total' => $totalModel->first(),
            'totalFiltre' => $totalFiltre,
            'types' => $typeModel->findAll(),
            'selectedType' => $idType,
        ]);
    }

    public function commissions()
    {
        $model = new CommissionsParOperateurModel();
        $commissions = $model
            ->where('id_operateur !=', (int) config('MobileMoney')->operatorId)
            ->orderBy('total_commission', 'DESC')
            ->findAll();

        return view('Operateur/commissions', [
            'title' => 'Commissions perçues',
            'commissions' => $commissions,
            'totalCommissions' => array_sum(
                array_column($commissions, 'total_commission')
            ),
        ]);
    }

    public function soldesExportCsv()
{
    $model = new SoldeClientModel();
    $soldes = $model->getAllOrderedByName();

    // Prépare les en-têtes HTTP pour forcer le téléchargement
    $filename = 'soldes_clients_' . date('Y-m-d') . '.csv';

    $response = $this->response;
    $response->setHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

    // Ouvre un flux en mémoire (php://output écrit directement dans la réponse)
    $handle = fopen('php://output', 'w');

    // BOM UTF-8 pour qu'Excel affiche correctement les accents
    fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Ligne d'en-tête du CSV
    fputcsv($handle, ['id', 'nom_client', 'solde'], ';');

    // Une ligne par client
    foreach ($soldes as $solde) {
        fputcsv($handle, [ 
            $solde['id'],
            $solde['nom_client'],
            $solde['SOLDE'],
        ], ';');
    }

    fclose($handle);

    return $response;
}
}
