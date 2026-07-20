<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Clients;
use App\Models\OperationMouvement;
use App\Models\TypeOperations;
use App\Models\Operateurs;
use App\Models\ConfigurationsTransaction;

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
        $numero = $this->normalizeNumero($this->request->getPost('numero'));
        $clientModel = new Clients();
        $client = $clientModel->getClientByNumero($numero);

        $config = config('MobileMoney');
        $operatorIdSimule = $config->operatorId;

        if (! $client) {
            return redirect()->back()->withInput()
                ->with('error', 'Numéro de téléphone incorrect.');
        }

        $operateurModel = new Operateurs();
        $operateur = $operateurModel->getOperateurByNumero($numero);

        if (! $operateur) {
            return redirect()->back()->withInput()
                ->with('error', 'Aucun opérateur ne correspond à ce numéro.');
        }

        if ((int) $operateur['id'] !== (int) $operatorIdSimule) {
            return redirect()->back()->withInput()
                ->with('error', 'Ce numero ne correspond pas a notre operateur');
        }

        session()->set([
            'client_id' => $client['id'],
            'nom_client' => $client['nom_client'],
        ]);

        return redirect()->to(site_url('client/compte'))
            ->with('success', 'Connexion réussie.');
    }

    public function solde()
    {
        if (! session()->has('client_id')) {
            return redirect()->to(site_url('client/connexion'));
        }

        return view('Client/solde', [
            'title' => 'Solde',
            'solde' => (new OperationMouvement())->getSoldeByClientId(
                (int) session()->get('client_id')
            ),
            'operateur' => (new Operateurs())->find(
                (int) config('MobileMoney')->operatorId
            ),
        ]);
    }

    public function operation()
    {
        if (! session()->has('client_id')) {
            return redirect()->to(site_url('client/connexion'));
        }

        $typeOperationsModel = new TypeOperations();
        $typeOperations = $typeOperationsModel->findAll();
        $operateurSimule = (new Operateurs())->find(
            (int) config('MobileMoney')->operatorId
        );

        return view('Client/operation', [
            'title' => 'Faire une opération',
            'typeOperations' => $typeOperations,
            'prefixeOperateurSimule' => $operateurSimule['prefixe'] ?? '',
        ]);
    }

    public function store()
    {
        if (! session()->has('client_id')) {
            return redirect()->to(site_url('client/connexion'));
        }

        $config = config('MobileMoney');
        $operatorIdSimule = $config->operatorId;
        $inclureFraisRetrait = $this->request
            ->getPost('inclure_frais_retrait') === '1';

        $typeOperationId = (string) $this->request->getPost('id_type_operation');

        if (! ctype_digit($typeOperationId)) {
            return redirect()->back()->withInput()->with('validation', [
                'id_type_operation' => 'Le type d’opération sélectionné est invalide.',
            ]);
        }

        $typeOperationsModel = new TypeOperations();
        $typeOperation = $typeOperationsModel->find((int) $typeOperationId);

        if (! $typeOperation) {
            return redirect()->back()->withInput()->with('validation', [
                'id_type_operation' => 'Le type d’opération sélectionné est invalide.',
            ]);
        }

        $montantSaisi = $this->request->getPost('montant');

        if (! is_numeric($montantSaisi) || (float) $montantSaisi <= 0) {
            return redirect()->back()->withInput()->with('validation', [
                'montant' => 'Le montant doit être un nombre supérieur à zéro.',
            ]);
        }

        $montant = (float) $montantSaisi;
        $configurationModel = new ConfigurationsTransaction();

        $clientId = (int) session()->get('client_id');
        $beneficiaireId = $clientId;
        $montantOperation = $montant;
        $fraisRetraitInclus = 0.0;

        if ($typeOperation['libelle'] === 'Transfert') {
            $clientModel = new Clients();
            $numeroBeneficiaire = $this->normalizeNumero(
                $this->request->getPost('numero_beneficiaire')
            );
            $beneficiaire = $clientModel->getClientByNumero($numeroBeneficiaire);

            if (! $beneficiaire) {
                return redirect()->back()->withInput()->with('validation', [
                    'numero_beneficiaire' => 'Le numéro du bénéficiaire est introuvable.',
                ]);
            }

            if ((int) $beneficiaire['id'] === $clientId) {
                return redirect()->back()->withInput()->with('validation', [
                    'numero_beneficiaire' => 'Vous ne pouvez pas effectuer un transfert vers votre propre compte.',
                ]);
            }

            $beneficiaireId = (int) $beneficiaire['id'];

            $operateurBeneficiaire = (new Operateurs())->getOperateurByNumero(
                $numeroBeneficiaire
            );
            $estMemeOperateur = $operateurBeneficiaire
                && (int) $operateurBeneficiaire['id'] === $operatorIdSimule;

            if ($inclureFraisRetrait && $estMemeOperateur) {
                $typeRetrait = $typeOperationsModel
                    ->where('libelle', 'Retrait')
                    ->first();

                if ($typeRetrait) {
                    $fraisRetraitInclus = $configurationModel->getFrais(
                        (int) $typeRetrait['id'],
                        $montant
                    );
                    $montantOperation += $fraisRetraitInclus;
                }
            }
        }

        $operationMouvement = new OperationMouvement();
        $fraisOperation = $configurationModel->getFrais(
            (int) $typeOperation['id'],
            $montantOperation
        );

        if (in_array($typeOperation['libelle'], ['Retrait', 'Transfert'], true)) {
            $solde = $operationMouvement->getSoldeByClientId($clientId);
            $totalADebiter = $montantOperation + $fraisOperation;

            if ($totalADebiter > $solde) {
                return redirect()->back()->withInput()->with('validation', [
                    'montant' => 'Solde insuffisant : le montant avec frais est de '
                        . number_format($totalADebiter, 2, ',', ' ') . ' Ar.',
                ]);
            }
        }

        $data = [
            'id_emetteur' => $clientId,
            'id_beneficiaire' => $beneficiaireId,
            'id_operateur' => (int) $operatorIdSimule,
            'id_type_operation' => (int) $typeOperation['id'],
            'montant' => $montantOperation,
        ];

        if (! $operationMouvement->insert($data)) {
            return redirect()->back()->withInput()
                ->with('validation', $operationMouvement->errors());
        }

        return redirect()->to(site_url('client/compte'))->with(
            'success',
            'Opération enregistrée. Frais d’opération : '
                . number_format($fraisOperation, 2, ',', ' ') . ' Ar. '
                . 'Frais de retrait inclus : '
                . number_format($fraisRetraitInclus, 2, ',', ' ') . ' Ar.'
        );
    }

    public function historique()
    {
        if (! session()->has('client_id')) {
            return redirect()->to(site_url('client/connexion'));
        }

        $operationMouvement = new OperationMouvement();
        $historique = $operationMouvement->getHistoriqueByClientId(session()->get('client_id'));

        return view('Client/historique', [
            'title' => 'Historique',
            'historique' => $historique
        ]);
    }

    public function deconnexion()
    {
        session()->remove(['client_id', 'nom_client']);

        return redirect()->to(site_url('client/connexion'))
            ->with('success', 'Vous êtes déconnecté.');
    }

    private function normalizeNumero($numero): string
    {
        if (! is_string($numero)) {
            return '';
        }

        return preg_replace('/\s+/u', '', trim($numero)) ?? '';
    }
}
