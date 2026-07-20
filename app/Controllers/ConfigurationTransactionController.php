<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ConfigurationsTransaction;
use App\Models\SoldeClientModel;

use App\Models\TypeOperationModel;
use App\Models\GainsFraisModel;
use App\Models\TotalGainsFraisModel;

class ConfigurationTransactionController extends BaseController
{
    public function index()
    {
        $model = new ConfigurationsTransaction();
        $typeModel = new TypeOperationModel();
        return view('Operateur/form', [
            'configurations' => $model->orderBy('id', 'ASC')->findAll(),
            'types' => $typeModel->findAll(),
        ]);
    }

    public function store()
    {
        $model = new ConfigurationsTransaction();
        $data = $this->request->getPost();

        if (!$model->insert($data)) {
            return view('Operateur/form', [
                'configurations' => $model->orderBy('id', 'ASC')->findAll(),
                'types' => $model->findAll(),
                'validation' => $model->errors(),
            ]);
        }

        return redirect()->to('/operateur');
    }

    public function soldes()
    {
        $model = new SoldeClientModel();

        return view('Operateur/soldes', [
            'soldes' => $model->orderBy('nom_client', 'ASC')->findAll(),
        ]);
    }

    public function gains()
    {
        $gainsModel = new GainsFraisModel();
        $totalModel = new TotalGainsFraisModel();
        $typeModel = new TypeOperationModel();

        $idType = $this->request->getGet('id_type_operation');

        if (!empty($idType)) {
            $gains = $gainsModel->where('id_type_operation', $idType)->findAll();
        } else {
            $gains = $gainsModel->findAll();
        }

        $totalFiltre = array_sum(array_column($gains, 'montant_frais'));

        return view('Operateur/gains', [
            'gains' => $gains,
            'total' => $totalModel->first(),
            'totalFiltre' => $totalFiltre,
            'types' => $typeModel->findAll(),
            'selectedType' => $idType,
        ]);
    }
}