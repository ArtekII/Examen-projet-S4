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

        return view('Operateur/commissions', [
            'commissions' => $model
            ->where('id_operateur !=', (int) config('MobileMoney')->operatorId)
            ->orderBy('total_commission', 'DESC')
            ->findAll(),
            
        ]);
    }
}
