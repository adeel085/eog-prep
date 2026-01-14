<?php

namespace App\Models;

use CodeIgniter\Model;

class ClassModel extends Model
{
    protected $table = 'classes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $dateFormat = 'datetime';
    protected $allowedFields = ['name', 'owner_id'];

    // Removed unique index from name column and added a unique index on name and owner_id columns combined
}
