<?php

namespace App\Models;

use CodeIgniter\Model;

class OperationMouvement extends Model
{
    protected $table            = 'operation_mouvement';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
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
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'date_operation';
    protected $updatedField  = '';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'id_emetteur' => 'required|integer',
        'id_beneficiaire' => 'required|integer',
        'id_operateur' => 'required|integer',
        'id_type_operation' => 'required|integer',
        'montant' => 'required|decimal|greater_than[0]'
    ];
    protected $validationMessages   = [
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
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

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
                $row['type_operation'] === 'Depot'     => 'Recu',
                $row['type_operation'] === 'Retrait'   => 'Envoye',
                $row['type_operation'] === 'Transfert' && (int)$row['id_beneficiaire'] === $clientId => 'Recu',
                $row['type_operation'] === 'Transfert' && (int)$row['id_emetteur'] === $clientId     => 'Envoye',
                default => null,
            };
        }

        return $rows;
    }

    public function normaliserMontant($montant): ?float
    {
        if (! is_numeric($montant) || (float) $montant <= 0) {
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
        $estTransfert = $typeOperation['libelle'] === 'Transfert';
        $typeRetrait = null;

        if ($inclureFraisRetrait && $estTransfert) {
            $typeRetrait = (new TypeOperations())->getByLibelle('Retrait');
        }

        $operations = [];
        $totalADebiter = 0.0;
        $totalFrais = 0.0;

        foreach ($beneficiaires as $index => $beneficiaire) {
            $fraisRetrait = 0.0;
            $commission = 0.0;

            if ($typeRetrait && $beneficiaire['meme_operateur']) {
                $fraisRetrait = $configurationModel->getFrais(
                    (int) $typeRetrait['id'],
                    $montants[$index]
                );
            }

            if ($estTransfert
                && ! $beneficiaire['meme_operateur']
                && $beneficiaire['operateur_id'] !== null) {
                $pourcentage = $commissionModel->getPourcentage(
                    $beneficiaire['operateur_id']
                );
                $commission = round(
                    $montants[$index] * $pourcentage / 100,
                    2
                );
            }

            $montantOperation = $montants[$index]
                + $fraisRetrait
                + $commission;
            $fraisOperation = $configurationModel->getFrais(
                (int) $typeOperation['id'],
                $montantOperation
            );

            $operations[] = [
                'id_emetteur' => $clientId,
                'id_beneficiaire' => $beneficiaire['id'],
                'id_operateur' => $operateurId,
                'id_type_operation' => (int) $typeOperation['id'],
                'montant' => $montantOperation,
            ];

            if ($typeOperation['libelle'] === 'Retrait' || $estTransfert) {
                $totalADebiter += $montantOperation + $fraisOperation;
            }

            $totalFrais += $fraisOperation + $fraisRetrait + $commission;
        }

        return [
            'operations' => $operations,
            'total_a_debiter' => $totalADebiter,
            'total_frais' => $totalFrais,
        ];
    }

    public function soldeSuffisant(int $clientId, float $montant): bool
    {
        return $this->getSoldeByClientId($clientId) >= $montant;
    }

    public function enregistrerTout(array $operations): ?string
    {
        $this->db->transBegin();

        try {
            foreach ($operations as $operation) {
                if ($this->insert($operation) === false) {
                    $this->db->transRollback();

                    return implode(' ', $this->errors())
                        ?: 'Impossible d’enregistrer l’opération.';
                }
            }

            if (! $this->db->transCommit()) {
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
