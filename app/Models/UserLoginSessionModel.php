<?php

namespace App\Models;

use CodeIgniter\Model;

class UserLoginSessionModel extends Model
{
    protected $table = 'users_login_sessions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $dateFormat = 'datetime';
    protected $allowedFields = ['session_id', 'user_id', 'login_time'];
}
