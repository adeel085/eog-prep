<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TopicModel;
use App\Models\UserModel;
use App\Models\ClassModel;
use App\Models\StudentSessionResultModel;

class AdminDashboard extends BaseController
{
    public function index()
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return redirect()->to(base_url('/'));
        }

        $classModel = new ClassModel();
        $userModel = new UserModel();

        $classes = $classModel->where('owner_id', $this->user['id'])->findAll();

        foreach ($classes as &$class) {
            $class['students'] = [];
        }

        $students = $userModel->filterByStudentOf($this->user['id'])->findAll();

        foreach ($students as &$student) {
            $classId = $userModel->getUserMeta("studentClassId", $student['id'], true);

            foreach ($classes as &$class) {
                if ($class['id'] == $classId) {
                    $class['students'][] = $student;
                    break;
                }
            }
        }
        
        return view('admin/dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'flashData' => $this->session->getFlashdata(),
            'classes' => $classes,
            'user' => $this->user
        ]);
    }

    public function getClassTopicsReport()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $classId = $this->request->getPost('class_id');

        if (!$classId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Class ID is required']);
        }

        // Verify the class belongs to this teacher/admin
        $classModel = new ClassModel();
        $class = $classModel->where(['id' => $classId, 'owner_id' => $this->user['id']])->first();

        if (!$class) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Class not found']);
        }

        $userModel = new UserModel();
        $topicModel = new TopicModel();
        $studentSessionResultModel = new StudentSessionResultModel();

        // Get all students in this class
        $db = db_connect();
        $studentsQuery = $db->query("
            SELECT u.id, u.full_name 
            FROM users u 
            INNER JOIN users_meta um ON u.id = um.user_id 
            WHERE um.meta_key = 'studentClassId' 
            AND um.meta_value = ?
            AND u.user_type = 'student'
        ", [$classId]);
        
        $students = $studentsQuery->getResultArray();
        $studentIds = array_column($students, 'id');

        if (empty($studentIds)) {
            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'topics' => [],
                    'students_count' => 0
                ]
            ]);
        }

        // Get all topics owned by this teacher
        $topics = $topicModel->where('owner_id', $this->user['id'])->findAll();

        // Get all session results for students in this class
        $results = $studentSessionResultModel->whereIn('student_id', $studentIds)->findAll();

        // Calculate average scores per topic
        $topicScores = [];
        foreach ($results as $result) {
            $topicId = $result['topic_id'];
            if (!isset($topicScores[$topicId])) {
                $topicScores[$topicId] = [
                    'total_score' => 0,
                    'count' => 0,
                    'level_scores' => [1 => [], 2 => [], 3 => []]
                ];
            }
            $topicScores[$topicId]['total_score'] += floatval($result['percentage']);
            $topicScores[$topicId]['count']++;
            
            $level = intval($result['level']);
            if ($level >= 1 && $level <= 3) {
                $topicScores[$topicId]['level_scores'][$level][] = floatval($result['percentage']);
            }
        }

        // Build topics data with scores
        $topicsData = [];
        foreach ($topics as $topic) {
            $topicId = $topic['id'];
            $avgScore = 0;
            $sessionsCount = 0;
            $levelAverages = [1 => null, 2 => null, 3 => null];

            if (isset($topicScores[$topicId])) {
                $avgScore = $topicScores[$topicId]['total_score'] / $topicScores[$topicId]['count'];
                $sessionsCount = $topicScores[$topicId]['count'];
                
                // Calculate level averages
                for ($level = 1; $level <= 3; $level++) {
                    $levelScores = $topicScores[$topicId]['level_scores'][$level];
                    if (!empty($levelScores)) {
                        $levelAverages[$level] = array_sum($levelScores) / count($levelScores);
                    }
                }
            }

            $topicsData[] = [
                'id' => $topicId,
                'name' => $topic['name'],
                'average_score' => round($avgScore, 2),
                'sessions_count' => $sessionsCount,
                'level_averages' => $levelAverages
            ];
        }

        // Sort topics by average score (best to worst)
        usort($topicsData, function($a, $b) {
            return $b['average_score'] <=> $a['average_score'];
        });

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'topics' => $topicsData,
                'students_count' => count($studentIds)
            ]
        ]);
    }
}
