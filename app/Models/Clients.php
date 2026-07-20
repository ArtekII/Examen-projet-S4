<?php

namespace App\Models;

use CodeIgniter\Model;

class Clients extends Model
{
    protected $table            = 'clients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nom_client',
        'numero'
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
    protected $validationRules      = [
        'nom_client' => 'required|min_length[2]|max_length[255]',
        'numero' => 'required|min_length[10]|max_length[40]|is_unique[clients.numero,id,{id}]'
    ];
    protected $validationMessages   = [
        'nom_client' => [
            'required' => 'Le nom du client est requis.',
            'min_length' => 'Le nom du client doit contenir au moins 2 caractères.',
            'max_length' => 'Le nom du client ne peut pas dépasser 255 caractères.'
        ],
        'numero' => [
            'required' => 'Le numéro de téléphone est requis.',
            'min_length' => 'Le numéro de téléphone doit contenir au moins 10 caractères.',
            'max_length' => 'Le numéro de téléphone ne peut pas dépasser 40 caractères.',
            'is_unique' => 'Ce numéro de téléphone est déjà utilisé.'
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

    public function getClientByNumero(string $numero): ?array
    {
        return $this->where('numero', $numero)->first();
    }

    public function existsByNumero(string $numero): bool
    {
        return $this->where('numero', $numero)->countAllResults() > 0;
    }

    
}
