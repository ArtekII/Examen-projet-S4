<?php

namespace App\Models;

use CodeIgniter\Model;

class SoldeClientModel extends Model
{
    protected $table      = 'v_get_solde_client';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
}