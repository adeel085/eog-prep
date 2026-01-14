<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\StudentGradeModel;
use App\Models\GradeModel;
use App\Models\ClassModel;
use App\Models\UserSignupModel;

class Signup extends BaseController
{
    public function index()
    {
        if ($this->user) {
            if ($this->user['user_type'] == 'admin') {
                return redirect()->to('/admin');
            }
            else {
                return redirect()->to('/home');
            }
        }

        $gradeModel = new GradeModel();
        $grades = $gradeModel->findAll();

        return view('signup', [
            'pageTitle' => 'Signup',
            'grades' => $grades
        ]);
    }

    public function signupUser() {

        if ($this->user) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'You are already logged in']);
        }

        $ip_address = $this->request->getIPAddress();

        $userSignupModel = new UserSignupModel();

        // Get the number of signups for this ip address in the last 24 hours
        $signups = $userSignupModel->where('ip_address', $ip_address)->where('created_at >', date('Y-m-d H:i:s', strtotime('-24 hours')))->countAllResults();

        if ($signups >= 30) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'You have reached the maximum number of signups']);
        }

        $full_name = $this->request->getPost('full_name');
        $username = $this->request->getPost('username');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $grade_id = $this->request->getPost('grade_id');

        $userModel = new UserModel();
        $studentGradeModel = new StudentGradeModel();

        $user = $userModel->where([
            'username' => $username,
            'user_type' => 'student'
        ])->first();

        if ($user) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Username already exists']);
        }

        $userId = $userModel->insert([
            'full_name' => $full_name,
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_type' => 'student'
        ], true);

        $classModel = new ClassModel();
        $class = $classModel->first();

        $userModel->insertUserMeta('studentClassId', $class['id'], $userId);

        $studentGradeModel->insert([
            'student_id' => $userId,
            'grade_id' => $grade_id
        ]);

        $userSignupModel = new UserSignupModel();
        $userSignupModel->insert([
            'ip_address' => $ip_address
        ]);

        $this->session->setFlashdata('status', 'user_signed_up');

        return $this->response->setJSON(['status' => 'success']);
    }
}
