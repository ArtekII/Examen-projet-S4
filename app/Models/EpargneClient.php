<?php

namespace App\Models;

use CodeIgniter\Model;

class EpargneClient extends Model
{
    protected $table            = 'epargne_client';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_client',
        'ipourcentage_epargne',
        'montant_epargne',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
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

    public function updatePourcentage(int $clientId, float $pourcentage, float $montant) {
        $montantEpargne = $montant * $pourcentage / 100;
        $montantRestant = $montant - $montantEpargne;

        $insertion = $this->db->table('epargne_client')->insert(
            [
                'id_client' => $clientId,
                'montant_epargne' => $montantEpargne,
                'ipourcentage_epargne' => $pourcentage,
            ]);

        if(!$insertion) {
            throw new RuntimeException('Erreur insertin');
        }

        return[
            
        ]
    } 
}
