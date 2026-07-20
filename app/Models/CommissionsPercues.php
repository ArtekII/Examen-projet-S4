<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionsPercues extends Model
{
    protected $table = 'commissions_percues';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id_operation_mouvement',
        'id_operateur_beneficiaire',
        'montant_commission',
        'date_commission',
    ];
    protected $useTimestamps = false;
}