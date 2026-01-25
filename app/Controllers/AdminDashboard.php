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
        
        // Create a map of student id to name
        $studentMap = [];
        foreach ($students as $student) {
            $studentMap[$student['id']] = $student['full_name'];
        }

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
        $topics = $topicModel->findAll();

        // Get all session results for students in this class
        $results = $studentSessionResultModel->whereIn('student_id', $studentIds)->findAll();

        // Organize results by topic -> level -> student (best score per student)
        $topicData = [];
        foreach ($results as $result) {
            $topicId = $result['topic_id'];
            $level = intval($result['level']);
            $studentId = $result['student_id'];
            $percentage = floatval($result['percentage']);

            if (!isset($topicData[$topicId])) {
                $topicData[$topicId] = [
                    1 => [],
                    2 => [],
                    3 => []
                ];
            }

            if ($level >= 1 && $level <= 3) {
                // Keep only the best score for each student per topic/level
                if (!isset($topicData[$topicId][$level][$studentId]) || 
                    $topicData[$topicId][$level][$studentId] < $percentage) {
                    $topicData[$topicId][$level][$studentId] = $percentage;
                }
            }
        }

        // Build topics response with student rankings
        $topicsResponse = [];
        foreach ($topics as $topic) {
            $topicId = $topic['id'];
            $levels = [];

            for ($level = 1; $level <= 3; $level++) {
                $studentScores = [];
                
                if (isset($topicData[$topicId][$level])) {
                    foreach ($topicData[$topicId][$level] as $studentId => $score) {
                        $studentScores[] = [
                            'student_id' => $studentId,
                            'student_name' => $studentMap[$studentId] ?? 'Unknown',
                            'score' => round($score, 2)
                        ];
                    }
                    
                    // Sort by score descending (best to worst)
                    usort($studentScores, function($a, $b) {
                        return $b['score'] <=> $a['score'];
                    });
                }

                $levels[$level] = $studentScores;
            }

            $topicsResponse[] = [
                'id' => $topicId,
                'name' => $topic['name'],
                'levels' => $levels
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'topics' => $topicsResponse,
                'students_count' => count($studentIds)
            ]
        ]);
    }
}
