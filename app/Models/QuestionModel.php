<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionModel extends Model
{
    protected $table = 'questions';
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
    protected $allowedFields = ['level', 'question_type', 'question_html', 'solution_html', 'owner_id'];
}
