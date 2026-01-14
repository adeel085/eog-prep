<?php

namespace App\Controllers;

use App\Models\UserModel;

class AdminLogin extends BaseController
{
    public function index()
    {
        if ($this->user) {
            if ($this->user['user_type'] == 'admin' || $this->user['user_type'] == 'teacher') {
                return redirect()->to(base_url('/admin/dashboard'));
            }

            return redirect()->to(base_url('/'));
        }

        return view('admin/login', [
            'pageTitle' => 'Admin Login',
            'flashData' => $this->session->getFlashdata()
        ]);
    }

    public function login()
    {
        if ($this->user) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'You are already logged in']);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $rememberMe = $this->request->getPost('rememberMe');

        $userModel = new UserModel();

        $user = $userModel->where([
            'username' => $username
        ])->groupStart()->where('user_type', 'admin')->orWhere('user_type', 'teacher')->groupEnd()->first();

        if (!$user) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid username or password']);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid username or password']);
        }

        $this->session->set('user_id', $user['id']);

        if ($rememberMe == 'true') {
            $rememberToken = bin2hex(random_bytes(16));

            $userModel->set('remember_token', $rememberToken)->where('id', $user['id'])->update();

            $this->response->setCookie('remember_me', $rememberToken, '604800', '/', '', '', false, true);
        }

        $this->session->setFlashdata('status', 'user_logged_in');

        return $this->response->setJSON(['status' => 'success']);
    }

    public function teacherRegistrationPage()
    {
        if ($this->user) {
            if ($this->user['user_type'] == 'admin' || $this->user['user_type'] == 'teacher') {
                return redirect()->to(base_url('/admin/dashboard'));
            }

            return redirect()->to(base_url('/'));
        }

        return view('admin/teacher_registration', [
            'pageTitle' => 'Teacher Registration'
        ]);
    }
}
