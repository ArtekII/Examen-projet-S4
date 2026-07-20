<?php

namespace App\Models;

use CodeIgniter\Model;

class Operateurs extends Model
{
    protected $table            = 'operateurs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nom_operateur',
        'prefixe',
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
    protected $validationRules = [
        'prefixe' => [
            'label' => 'Préfixe',
            'rules' => 'required|regex_match[/^[0-9]{3}$/]',
        ],
    ];
    protected $validationMessages = [
        'prefixe' => [
            'required' => 'Le préfixe est obligatoire.',
            'regex_match' => 'Le préfixe doit contenir exactement trois chiffres.',
        ],
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

    public function getOperateurByNumero(string $numero): ?array
    {
        $prefixe = substr(trim($numero), 0, 3);

        return $this->where('prefixe', $prefixe)->first();
    }

    public function getOthersOrderedByName(int $excludedOperatorId): array
    {
        return $this
            ->where('id !=', $excludedOperatorId)
            ->orderBy('nom_operateur', 'ASC')
            ->findAll();
    }

    public function prefixeUtiliseParUnAutre(string $prefixe, int $idOperateur): bool
    {
        return $this
            ->where('prefixe', $prefixe)
            ->where('id !=', $idOperateur)
            ->first() !== null;
    }
}
