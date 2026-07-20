<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionsParOperateurModel extends Model
{
    protected $table = 'v_commissions_par_operateur';
    protected $primaryKey = 'id_operateur';
    protected $returnType = 'array';
    protected $useTimestamps = false;
}