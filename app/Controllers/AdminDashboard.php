<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TopicModel;
use App\Models\UserModel;
use App\Models\ClassModel;
use App\Models\StudentGradeModel;
use App\Models\GradeModel;

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
        $studentGradeModel = new StudentGradeModel();
        $gradeModel = new GradeModel();
        $topicModel = new TopicModel();

        $classes = $classModel->where('owner_id', $this->user['id'])->findAll();

        foreach ($classes as &$class) {
            $class['students'] = [];
        }

        $students = $userModel->filterByStudentOf($this->user['id'])->findAll();

        foreach ($students as &$student) {
            $classId = $userModel->getUserMeta("studentClassId", $student['id'], true);

            // Get student's grade
            $studentGrade = $studentGradeModel->where('student_id', $student['id'])->first();

            if ($studentGrade) {
                $student['grade'] = $gradeModel->where('id', $studentGrade['grade_id'])->first();
            }

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
}
