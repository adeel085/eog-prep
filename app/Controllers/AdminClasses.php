<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClassModel;
use App\Models\UserModel;
use App\Models\GradeModel;
use App\Models\StudentGradeModel;

class AdminClasses extends BaseController
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

        $classes = $classModel->where('owner_id', $this->user['id'])->findAll();

        return view('admin/classes', [
            'pageTitle' => 'Classes',
            'flashData' => $this->session->getFlashdata(),
            'classes' => $classes,
            'user' => $this->user
        ]);
    }

    public function studentsPage($id)
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return redirect()->to(base_url('/'));
        }

        $classModel = new ClassModel();
        $userModel = new UserModel();
        $gradeModel = new GradeModel();
        $studentGradeModel = new StudentGradeModel();

        $class = $classModel->find($id);

        if (!$class || $class['owner_id'] != $this->user['id']) {
            return redirect()->to(base_url('/admin/classes'));
        }

        $students = $userModel->filterByStudentOf($this->user['id'])->findAll();
        $filteredStudents = [];

        foreach ($students as &$student) {
            $studentClassId = $userModel->getUserMeta('studentClassId', $student['id'], true);

            if ($studentClassId == $id) {
                $filteredStudents[] = $student;
            }
        }

        foreach ($filteredStudents as &$student) {
            $studentGrade = $studentGradeModel->where('student_id', $student['id'])->first();

            $grade = $gradeModel->find($studentGrade['grade_id']);
            $student['grade'] = $grade;
        }

        return view('admin/classes_students', [
            'pageTitle' => 'Students',
            'flashData' => $this->session->getFlashdata(),
            'class' => $class,
            'students' => $filteredStudents,
            'user' => $this->user
        ]);
    }

    public function saveNew()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $name = $this->request->getPost('name');

        if (!$name || trim($name) == '') {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        $classModel = new ClassModel();

        if ($classModel->where(['name' => $name, 'owner_id' => $this->user['id']])->first()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Class with this name already exists']);
        }

        $classModel->insert(['name' => $name, 'owner_id' => $this->user['id']]);

        $this->session->setFlashdata('status', 'class_created');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Class created successfully']);
    }

    public function delete()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $classId = $this->request->getPost('class_id');

        $classModel = new ClassModel();

        $class = $classModel->find($classId);

        if (!$class || $class['owner_id'] != $this->user['id']) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Class not found']);
        }

        $classModel->delete($classId);

        $this->session->setFlashdata('status', 'class_deleted');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Class deleted successfully']);
    }

    public function update()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $classId = $this->request->getPost('class_id');
        $name = $this->request->getPost('name');

        $classModel = new ClassModel();

        $class = $classModel->find($classId);

        if (!$class || $class['owner_id'] != $this->user['id']) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Class not found']);
        }
        
        // Get class by name
        $class = $classModel->where(['name' => $name, 'owner_id' => $this->user['id']])->first();

        if ($class && $class['id'] != $classId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Class with this name already exists']);
        }

        $classModel->set(['name' => $name])->update($classId);

        $this->session->setFlashdata('status', 'class_updated');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Class updated successfully']);
    }

    public function sendEmailToParents()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $classId = $this->request->getPost('class_id');
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');

        $classModel = new ClassModel();

        $class = $classModel->find($classId);

        if (!$class || $class['owner_id'] != $this->user['id']) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Class not found']);
        }

        $userModel = new UserModel();

        $students = $userModel->filterByStudentOf($this->user['id'])->findAll();

        foreach ($students as $student) {
            if ($userModel->getUserMeta('studentClassId', $student['id'], true) === $classId) {
                $parentEmails = $userModel->getUserMeta('parentEmails', $student['id'], true);

                if (empty($parentEmails)) {
                    continue;
                }

                helper('student_reports');

                $missingQuestions = getMissingQuestions($student['id'], $startDate, $endDate);

                if (empty($missingQuestions)) {
                    continue;
                }

                $parentEmails = explode(',', $parentEmails);

                foreach ($parentEmails as &$parentEmail) {
                    $parentEmail = trim($parentEmail);
                }

                $this->sendMissingQuestionsEmail($parentEmails, $student, $startDate, $endDate);
            }
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Emails sent successfully']);
    }

    private function sendMissingQuestionsEmail($parentEmails, $student, $startDate, $endDate) {
        
        $subject = "Report for " . $student['full_name'];
        $message = "Please click on the following link to view the questions that <b>" . $student['full_name'] . "</b> needs to work on:<br><br>";

        $questionsUrl = base_url('/report-questions?st=' . $student['id'] . '&sd=' . $startDate . '&ed=' . $endDate);

        $message .= "<a href='" . $questionsUrl . "'>" . $questionsUrl . "</a>";
        
        $email = \Config\Services::email();
        $email->setTo(array_shift($parentEmails));

         // Add remaining emails as CC
        if (!empty($parentEmails)) {
            $email->setCC($parentEmails);
        }

        $email->setCC($student['email']);

        $email->setFrom(env('email.SMTPUser'), 'MyQuickMath');
        $email->setSubject($subject);
        $email->setMessage($message);
        
        return $email->send();
    }
}
