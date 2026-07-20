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

    public function getPourcentage(int $idOperateur): float
    {
        $config = $this->where('id_operateur', $idOperateur)->first();

        return $config ? (float) $config['pourcentage_commission'] : 0.0;
    }
}