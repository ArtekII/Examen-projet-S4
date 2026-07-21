<?php

namespace App\Models;

use CodeIgniter\Model;

class OperationMouvement extends Model
{
    protected $table = 'operation_mouvement';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'id_emetteur',
        'id_beneficiaire',
        'id_operateur',
        'id_type_operation',
        'montant'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'date_operation';
    protected $updatedField = '';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id_emetteur' => 'required|integer',
        'id_beneficiaire' => 'required|integer',
        'id_operateur' => 'required|integer',
        'id_type_operation' => 'required|integer',
        'montant' => 'required|decimal|greater_than[0]'
    ];
    protected $validationMessages = [
        'id_emetteur' => [
            'required' => 'L\'ID de l\'émetteur est requis.',
            'integer' => 'L\'ID de l\'émetteur doit être un entier.'
        ],
        'id_beneficiaire' => [
            'integer' => 'L\'ID du bénéficiaire doit être un entier.'
        ],
        'id_operateur' => [
            'required' => 'L\'ID de l\'opérateur est requis.',
            'integer' => 'L\'ID de l\'opérateur doit être un entier.'
        ],
        'id_type_operation' => [
            'required' => 'L\'ID du type d\'opération est requis.',
            'integer' => 'L\'ID du type d\'opération doit être un entier.'
        ],
        'montant' => [
            'required' => 'Le montant est requis.',
            'decimal' => 'Le montant doit être un nombre décimal avec 2 décimales.'
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function getSoldeByClientId(int $clientId): float
    {
        $result = $this->db
            ->table('v_get_solde_client')
            ->select('SOLDE')
            ->where('id', $clientId)
            ->get()
            ->getRowArray();

        return (float) ($result['SOLDE'] ?? 0.00);
    }

    public function getHistoriqueByClientId(int $clientId): array
    {
        $rows = $this->db
            ->table('v_get_historique_client')
            ->groupStart()
            ->where('id_emetteur', $clientId)
            ->orWhere('id_beneficiaire', $clientId)
            ->groupEnd()
            ->orderBy('date_operation', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['sens'] = match (true) {
                $row['type_operation'] === 'Depot' => 'Recu',
                $row['type_operation'] === 'Retrait' => 'Envoye',
                $row['type_operation'] === 'Transfert' && (int) $row['id_beneficiaire'] === $clientId => 'Recu',
                $row['type_operation'] === 'Transfert' && (int) $row['id_emetteur'] === $clientId => 'Envoye',
                default => null,
            };
        }

        return $rows;
    }

    public function normaliserMontant($montant): ?float
    {
        if (!is_numeric($montant) || (float) $montant <= 0) {
            return null;
        }

        return round((float) $montant, 2);
    }

    public function diviserMontant(float $montant, int $nombre): array
    {
        $totalCentimes = (int) round($montant * 100);

        if ($nombre < 1 || $totalCentimes < $nombre) {
            return [];
        }

        $part = intdiv($totalCentimes, $nombre);
        $reste = $totalCentimes % $nombre;
        $montants = [];

        for ($index = 0; $index < $nombre; $index++) {
            $centimes = $part;

            if ($index < $reste) {
                $centimes++;
            }

            $montants[] = $centimes / 100;
        }

        return $montants;
    }

    public function preparerPlan(
        array $typeOperation,
        array $beneficiaires,
        array $montants,
        int $clientId,
        int $operateurId,
        bool $inclureFraisRetrait
    ): array {
        $configurationModel = new ConfigurationsTransaction();
        $commissionModel = new ConfigurationsCommission();
        $promotion = new Promotion();
        $estTransfert = $typeOperation['libelle'] === 'Transfert';
        $typeRetrait = null;

        if ($inclureFraisRetrait && $estTransfert) {
            $typeRetrait = (new TypeOperations())->getByLibelle('Retrait');
        }

        $operations = [];
        $commissions = [];
        $totalADebiter = 0.0;
        $totalFrais = 0.0;
        $totalCommission = 0.0;

        foreach ($beneficiaires as $index => $beneficiaire) {
            $montantPur = $montants[$index];
            $fraisRetrait = 0.0;
            $commission = 0.0;

            if ($typeRetrait && $beneficiaire['meme_operateur']) {
                $fraisRetrait = $configurationModel->getFrais(
                    (int) $typeRetrait['id'],
                    $montantPur
                );
            }

            if (
                $estTransfert
                && !$beneficiaire['meme_operateur']
                && $beneficiaire['operateur_id'] !== null
            ) {
                $pourcentage = $commissionModel->getPourcentage(
                    $beneficiaire['operateur_id']
                );
                $commission = round(
                    $montantPur * $pourcentage / 100,
                    2
                );
            }

            if (
                $beneficiaire['meme_operateur']
            ) {
                $promot = $promotion->getPourcetangeReduction();
                $reduc = $promot['pourcetange_promotion'];

                $promotB = true;
            }

            if ($promotB) {
                $fraisOperationTemp = $configurationModel->getFrais(
                    (int) $typeOperation['id'],
                    $montantPur
                );
                $fraisOperation = $fraisOperationTemp - ($fraisOperationTemp * ($reduc / 100));
                
            } else {
                // Frais habituels calculés sur le montant pur (pas gonflé)
                $fraisOperation = $configurationModel->getFrais(
                    (int) $typeOperation['id'],
                    $montantPur
                );
            }
            

            // Le bénéficiaire reçoit TOUJOURS le montant pur
            $operations[] = [
                'id_emetteur' => $clientId,
                'id_beneficiaire' => $beneficiaire['id'],
                'id_operateur' => $operateurId,
                'id_type_operation' => (int) $typeOperation['id'],
                'montant' => $montantPur,
            ];

            // La commission est trackée à part, pour insertion dans commissions_percues
            if ($commission > 0) {
                $commissions[] = [
                    'index_operation' => $index, // pour relier après coup à l'id inséré
                    'id_operateur_beneficiaire' => $beneficiaire['operateur_id'],
                    'montant_commission' => $commission,
                ];
            }

            if ($typeOperation['libelle'] === 'Retrait' || $estTransfert) {
                $totalADebiter += $montantPur + $fraisOperation + $fraisRetrait + $commission;
            }

            $totalFrais += $fraisOperation + $fraisRetrait;
            $totalCommission += $commission;
        }

        return [
            'operations' => $operations,
            'commissions' => $commissions,
            'total_a_debiter' => $totalADebiter,
            'total_frais' => $totalFrais,
            'total_commission' => $totalCommission,
        ];
    }

    public function soldeSuffisant(int $clientId, float $montant): bool
    {
        return $this->getSoldeByClientId($clientId) >= $montant;
    }

    public function enregistrerTout(array $operations, array $commissions = []): ?string
    {
        $this->db->transBegin();

        try {
            $idsInseres = [];

            foreach ($operations as $index => $operation) {
                $idInsere = $this->insert($operation);

                if ($idInsere === false) {
                    $this->db->transRollback();

                    return implode(' ', $this->errors())
                        ?: 'Impossible d’enregistrer l’opération.';
                }

                $idsInseres[$index] = $idInsere;
            }

            if (!empty($commissions)) {
                $commissionModel = new CommissionsPercues();

                foreach ($commissions as $commissionData) {
                    $idOperation = $idsInseres[$commissionData['index_operation']];

                    $inséré = $commissionModel->insert([
                        'id_operation_mouvement' => $idOperation,
                        'id_operateur_beneficiaire' => $commissionData['id_operateur_beneficiaire'],
                        'montant_commission' => $commissionData['montant_commission'],
                        'date_commission' => date('Y-m-d H:i:s'),
                    ]);

                    if ($inséré === false) {
                        $this->db->transRollback();

                        return 'Impossible d’enregistrer la commission.';
                    }
                }
            }

            if (!$this->db->transCommit()) {
                $this->db->transRollback();

                return 'Impossible de terminer l’envoi.';
            }
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            log_message('error', $exception->getMessage());

            return 'Une erreur est survenue pendant l’envoi.';
        }

        return null;
    }
}
