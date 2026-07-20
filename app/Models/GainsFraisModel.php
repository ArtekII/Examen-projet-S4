<?php

namespace App\Models;

use CodeIgniter\Model;

class GainsFraisModel extends Model
{
    protected $table = 'v_gains_frais';
    protected $primaryKey = 'id_operation';
    protected $returnType = 'array';
    protected $useTimestamps = false;
}