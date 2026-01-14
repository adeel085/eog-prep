<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionAnswersModel extends Model
{
    protected $table = 'questions_answers';
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
    protected $allowedFields = ['question_id', 'answer', 'is_correct'];
}
