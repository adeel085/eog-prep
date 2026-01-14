<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
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
    protected $allowedFields = ['username', 'password', 'user_type', 'remember_token', 'full_name', 'email'];

    protected $studentOfId = null;
    protected $studentJoinApplied = false;

    public function filterByStudentOf($teacherId)
    {
        $this->studentJoinApplied = false;
        $this->studentOfId = $teacherId;
        return $this;
    }

    public function builder($table = null)
    {
        $builder = parent::builder($table ?? $this->table);

        if ($this->studentOfId !== null && !$this->studentJoinApplied) {
            $builder
                ->select('users.*')
                ->join('users_meta', 'users_meta.user_id = users.id')
                ->where('users.user_type', 'student')
                ->where('users_meta.meta_key', 'studentOf')
                ->where('users_meta.meta_value', $this->studentOfId);
            
            // mark that join was added
            $this->studentJoinApplied = true;
        }

        return $builder;
    }

    public function getStudentsByTeacherId($teacherId)
    {
        $db = db_connect();

        $sql = "SELECT * FROM users INNER JOIN users_meta ON users.id = users_meta.user_id WHERE users_meta.meta_key = 'studentOf' AND users_meta.meta_value = ?";

        $results = $db->query($sql, [$teacherId])->getResultArray();

        return $results;
    }

    public function insertUserMeta($metaKey, $metaValue, $userId)
    {

        $db = db_connect();

        $sql = "INSERT INTO users_meta (meta_key, meta_value, user_id) VALUES (?,?,?)";

        $db->query($sql, [$metaKey, $metaValue, $userId]);

        return $db->insertID();
    }

    public function getUserMeta($metaKey, $userId, $single = false)
    {
        $db = db_connect();

        $sql = "SELECT * FROM users_meta WHERE meta_key = ? AND user_id = ?";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $query = $db->query($sql, [$metaKey, $userId]);

        $resultsArray = $query->getResultArray();

        if ($single) {
            if (count($resultsArray) > 0) {
                return $resultsArray[0]['meta_value'];
            } else {
                return null;
            }
        } else {
            $result = [];
            foreach ($resultsArray as $row) {
                $result[] = $row['meta_value'];
            }
            return $result;
        }
    }

    public function deleteUserMeta($metaKey, $userId, $single = false)
    {
        $db = db_connect();

        $sql = "DELETE FROM users_meta WHERE meta_key = ? AND user_id = ?";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $db->query($sql, [$metaKey, $userId]);
    }

    public function deleteAllUserMeta($userId)
    {
        $db = db_connect();

        $sql = "DELETE FROM users_meta WHERE user_id = ?";

        $db->query($sql, [$userId]);
    }

    public function updateUserMeta($metaKey, $metaValue, $userId, $single = false)
    {
        $db = db_connect();

        if ($this->getUserMeta($metaKey, $userId, true) === null) {
            $this->insertUserMeta($metaKey, $metaValue, $userId);
            return;
        }

        $sql = "UPDATE users_meta SET meta_value = ? WHERE meta_key = ? AND user_id = ?";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $db->query($sql, [$metaValue, $metaKey, $userId]);
    }
}
