<?php

namespace App\Models;

use CodeIgniter\Model;

class GradeTopicsModel extends Model
{
    protected $table = 'grades_topics';
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
    protected $allowedFields = ['grade_id', 'topic_id'];
}
