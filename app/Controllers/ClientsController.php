<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Clients;
use App\Models\OperationMouvement;
use App\Models\TypeOperations;
use App\Models\Operateurs;

class ClientsController extends BaseController
{
    public function index()
    {
        return view('Client/connexion', [
            'title' => 'Connexion'
        ]
        );
    }

    public function login()
    {
        $clientModel = new Clients();
        $numero = $clientModel->normaliserNumero(
            $this->request->getPost('numero')
        );
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

        $clientId = (int) session()->get('client_id');
        $operateurId = (int) config('MobileMoney')->operatorId;
        $typeOperationId = (string) $this->request->getPost('id_type_operation');
        $montantSaisi = $this->request->getPost('montant');
        $envoiMultiple = $this->request->getPost('envoi_multiple') === '1';
        $inclureFraisRetrait = $this->request
            ->getPost('inclure_frais_retrait') === '1';

        $typeOperationModel = new TypeOperations();
        $typeOperation = null;

        if (ctype_digit($typeOperationId)) {
            $typeOperation = $typeOperationModel->find((int) $typeOperationId);
        }

        if (! $typeOperation) {
            return $this->validationError(
                'id_type_operation',
                'Le type d’opération sélectionné est invalide.'
            );
        }

        $operationModel = new OperationMouvement();
        $montant = $operationModel->normaliserMontant($montantSaisi);

        if ($montant === null) {
            return $this->validationError(
                'montant',
                'Le montant doit être un nombre supérieur à zéro.'
            );
        }

        $beneficiaires = [[
            'id' => $clientId,
            'meme_operateur' => true,
        ]];

        if ($typeOperation['libelle'] === 'Transfert') {
            if ($envoiMultiple) {
                $texteNumeros = (string) $this->request
                    ->getPost('numeros_beneficiaires');
                $numeros = preg_split('/[\r\n,;]+/', $texteNumeros) ?: [];
            } else {
                $numeros = [$this->request->getPost('numero_beneficiaire')];
            }

            $clientModel = new Clients();
            $resultat = $clientModel->trouverBeneficiaires(
                $numeros,
                $clientId,
                $operateurId,
                $envoiMultiple
            );

            if (isset($resultat['error'])) {
                return $this->validationError(
                    $resultat['field'],
                    $resultat['error']
                );
            }

            $beneficiaires = $resultat['beneficiaires'];
        }

        $montants = $operationModel->diviserMontant(
            $montant,
            count($beneficiaires)
        );

        if (empty($montants)) {
            return $this->validationError(
                'montant',
                'Le montant est trop faible pour le nombre de bénéficiaires.'
            );
        }

        $plan = $operationModel->preparerPlan(
            $typeOperation,
            $beneficiaires,
            $montants,
            $clientId,
            $operateurId,
            $inclureFraisRetrait
        );

        $doitVerifierSolde = $typeOperation['libelle'] === 'Retrait'
            || $typeOperation['libelle'] === 'Transfert';

        if ($doitVerifierSolde
            && ! $operationModel->soldeSuffisant(
                $clientId,
                $plan['total_a_debiter']
            )) {
            return $this->validationError(
                'montant',
                'Solde insuffisant : le total avec frais est de '
                    . number_format($plan['total_a_debiter'], 2, ',', ' ') . ' Ar.'
            );
        }

        $erreur = $operationModel->enregistrerTout($plan['operations']);

        if ($erreur !== null) {
            return redirect()->back()->withInput()->with('error', $erreur);
        }

        $nombreOperations = count($plan['operations']);
        $message = $nombreOperations > 1
            ? "{$nombreOperations} transferts enregistrés."
            : 'Opération enregistrée.';

        return redirect()->to(site_url('client/compte'))->with(
            'success',
            $message . ' Frais totaux : '
                . number_format($plan['total_frais'], 2, ',', ' ') . ' Ar.'
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

    private function validationError(string $field, string $message)
    {
        return redirect()->back()->withInput()->with('validation', [
            $field => $message,
        ]);
    }
}
