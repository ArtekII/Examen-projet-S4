<?php

namespace App\Models;

use CodeIgniter\Model;

class SoldeClients extends Model
{
    protected $table            = 'soldeclients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_client',
        'solde'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'date_creation';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'id_client' => 'required|exists:clients,id',
        'solde' => 'required|numeric|min_value:0'
    ];
    protected $validationMessages   = [
        'id_client' => [
            'required' => 'L\'ID du client est requis.',
            'exists' => 'Le client n\'existe pas.'
        ],
        'solde' => [
            'required' => 'Le solde est requis.',
            'numeric' => 'Le solde doit être un nombre.',
            'min_value' => 'Le solde doit être un nombre positif.'
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

    public function getSoldeByClientId($id_client)
    {
        return $this->where('id_client', $id_client)->orderby('date_creation', 'DESC')->first();
    }
}
