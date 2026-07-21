<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Clients;
use App\Models\OperationMouvement;
use App\Models\TypeOperations;
use App\Models\Operateurs;
use App\Models\Epargne;
use App\Models\EpargneClient;


class ClientsController extends BaseController
{
    public function index()
    {
        return view('Client/connexion', [
            'title' => 'Connexion'
        ]);
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
            'operateur_id' => $operateurId,
        ]];

        if ($typeOperation['id'] === 3) {
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

        // ==== MODIFIÉ : on passe aussi $plan['commissions'] ====
        $erreur = $operationModel->enregistrerTout(
            $plan['operations'],
            $plan['commissions']
        );

        if ($erreur !== null) {
            return redirect()->back()->withInput()->with('error', $erreur);
        }

        $nombreOperations = count($plan['operations']);
        $message = $nombreOperations > 1
            ? "{$nombreOperations} transferts enregistrés."
            : 'Opération enregistrée.';

        // ==== MODIFIÉ : ajout du détail commission dans le message ====
        $messageFrais = ' Frais totaux : '
            . number_format($plan['total_frais'], 2, ',', ' ') . ' Ar.';

        $messageCommission = $plan['total_commission'] > 0
            ? ' Commission : ' . number_format($plan['total_commission'], 2, ',', ' ') . ' Ar.'
            : '';

        return redirect()->to(site_url('client/compte'))->with(
            'success',
            $message . $messageFrais . $messageCommission
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

    public function choix() {
        if (! session()->has('client_id')) {
            return redirect()->to(site_url('client/connexion'));
        }


        $operateurSimule = (new Operateurs())->find(
            (int) config('MobileMoney')->operatorId
        );

        return view('Client/epargne', [
            'title' => 'Choix epargne',
            'prefixeOperateurSimule' => $operateurSimule['prefixe'] ?? '',
        ]);
    }

    public function epargne() {
        $epargneClientModel = new EpargneClient();
        if (! session()->has('client_id')) {
            return redirect()->to(site_url('client/connexion'));
        }

        $clientId = (int) session()->get('client_id');

        $pourcentage = $this->request->getPost('pourcentage');

        $epargneClientModel->updatePourcentage($clientId, )
        return redirect()->to('/client');
    
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

    public function exporterHistoriquePdf()
    {
        if (! session()->has('client_id')) {
            return redirect()->to(site_url('client/connexion'));
        }

        require_once APPPATH . 'ThirdParty/fpdf.php';

        $clientId = (int) session()->get('client_id');
        $historique = (new OperationMouvement())
            ->getHistoriqueByClientId($clientId);

        // FPDF standard ne gère pas directement l’UTF-8.
        $encode = static function ($texte): string {
            $texteConverti = iconv(
                'UTF-8',
                'windows-1252//TRANSLIT',
                (string) $texte
            );

            return $texteConverti !== false ? $texteConverti : (string) $texte;
        };

        $pdf = new \FPDF('L', 'mm', 'A4');
        $pdf->SetTitle($encode('Historique des opérations'));
        $pdf->SetAuthor($encode((string) session()->get('nom_client')));
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // Titre
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(
            0,
            10,
            $encode('Historique des opérations'),
            0,
            1,
            'C'
        );

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(
            0,
            8,
            $encode('Client : ' . session()->get('nom_client')),
            0,
            1
        );

        $pdf->Ln(4);

        if (empty($historique)) {
            $pdf->SetFont('Arial', 'I', 11);
            $pdf->Cell(
                0,
                10,
                $encode('Aucune opération enregistrée.'),
                0,
                1,
                'C'
            );
        } else {
            // En-tête du tableau
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(230, 230, 230);

            $pdf->Cell(43, 9, 'Date', 1, 0, 'C', true);
            $pdf->Cell(38, 9, $encode('Opération'), 1, 0, 'C', true);
            $pdf->Cell(28, 9, 'Sens', 1, 0, 'C', true);
            $pdf->Cell(90, 9, 'Correspondant', 1, 0, 'C', true);
            $pdf->Cell(48, 9, 'Montant', 1, 1, 'C', true);

            // Contenu
            $pdf->SetFont('Arial', '', 9);

            foreach ($historique as $operation) {
                $estRecu = $operation['sens'] === 'Recu';
                $correspondant = '-';

                if ($operation['type_operation'] === 'Transfert') {
                    $correspondant = $estRecu
                        ? ($operation['emetteur'] ?? '-')
                        : ($operation['beneficiaire'] ?? '-');
                }

                $sens = $estRecu ? 'Reçu' : 'Envoyé';

                $montant = ($estRecu ? '+' : '-')
                    . number_format(
                        (float) $operation['montant'],
                        2,
                        ',',
                        ' '
                    )
                    . ' Ar';

                $pdf->Cell(
                    43,
                    8,
                    $encode($operation['date_operation']),
                    1
                );

                $pdf->Cell(
                    38,
                    8,
                    $encode($operation['type_operation']),
                    1
                );

                $pdf->Cell(
                    28,
                    8,
                    $encode($sens),
                    1,
                    0,
                    'C'
                );

                $pdf->Cell(
                    90,
                    8,
                    $encode($correspondant),
                    1
                );

                $pdf->Cell(
                    48,
                    8,
                    $encode($montant),
                    1,
                    1,
                    'R'
                );
            }
        }

        $contenuPdf = $pdf->Output('S');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader(
                'Content-Disposition',
                'attachment; filename="historique_operations.pdf"'
            )
            ->setBody($contenuPdf);
    }
}