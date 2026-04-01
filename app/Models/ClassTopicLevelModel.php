<?php

namespace App\Models;

use CodeIgniter\Model;

class ClassTopicLevelModel extends Model
{
    protected $table = 'classes_topics_levels';
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
    protected $allowedFields = ['class_id', 'topic_id', 'level'];
}
