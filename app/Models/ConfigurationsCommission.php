<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfigurationsCommission extends Model
{
    protected $table = 'configurations_commission';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['id_operateur', 'pourcentage_commission'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'pourcentage_commission' => [
            'label' => 'Pourcentage de commission',
            'rules' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        ],
    ];

    protected $validationMessages = [
        'pourcentage_commission' => [
            'required' => 'Le pourcentage de commission est obligatoire.',
            'decimal' => 'Le pourcentage de commission doit être un nombre.',
            'greater_than_equal_to' => 'Le pourcentage de commission ne peut pas être négatif.',
            'less_than_equal_to' => 'Le pourcentage de commission ne peut pas dépasser 100.',
        ],
    ];

    public function getPourcentage(int $idOperateur): float
    {
        $config = $this->where('id_operateur', $idOperateur)->first();

        return $config ? (float) $config['pourcentage_commission'] : 0.0;
    }

    public function getConfigurations(): array
    {
        return $this->db
            ->table('v_operateur_comission')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }
}
