<?php

namespace App\Models;

use CodeIgniter\Model;

class TotalGainsFraisModel extends Model
{
    protected $table = 'v_total_gains_frais';
    protected $returnType = 'array';
    protected $useTimestamps = false;
}