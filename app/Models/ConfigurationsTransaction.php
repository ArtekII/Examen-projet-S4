<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfigurationsTransaction extends Model
{
    protected $table = 'configurations_transaction';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['borne_min', 'borne_max', 'montant_frais', 'id_type_operation'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'borne_min' => [
            'label' => 'Borne minimale',
            'rules' => 'required|decimal|greater_than_equal_to[0]',
        ],
        'borne_max' => [
            'label' => 'Borne maximale',
            'rules' => 'required|decimal|greater_than_equal_to[0]',
        ],
        'montant_frais' => [
            'label' => 'Montant des frais',
            'rules' => 'required|decimal|greater_than_equal_to[0]',
        ],
        'id_type_operation' => [
            'label' => 'Type d\'opération',
            'rules' => 'required|integer|is_not_unique[type_operations.id]',
        ],
    ];

    protected $validationMessages = [
        'borne_min' => [
            'required' => 'La borne minimale est obligatoire.',
            'decimal' => 'La borne minimale doit être un nombre décimal.',
            'greater_than_equal_to' => 'La borne minimale doit être supérieure ou égale à 0.',
        ],
        'borne_max' => [
            'required' => 'La borne maximale est obligatoire.',
            'decimal' => 'La borne maximale doit être un nombre décimal.',
            'greater_than_equal_to' => 'La borne maximale doit être supérieure ou égale à 0.',
        ],
        'montant_frais' => [
            'required' => 'Le montant des frais est obligatoire.',
            'decimal' => 'Le montant des frais doit être un nombre décimal.',
            'greater_than_equal_to' => 'Le montant des frais doit être supérieur ou égal à 0.',
        ],
        'id_type_operation' => [
            'required' => 'Le type d\'opération est obligatoire.',
            'integer' => 'Le type d\'opération est invalide.',
            'is_not_unique' => 'Ce type d\'opération n\'existe pas.',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function getFrais(int $typeOperationId, float $montant): float
    {
        $configuration = $this
            ->where('id_type_operation', $typeOperationId)
            ->where('borne_min <=', $montant)
            ->where('borne_max >=', $montant)
            ->orderBy('id', 'ASC')
            ->first();

        return (float) ($configuration['montant_frais'] ?? 0);
    }
}
