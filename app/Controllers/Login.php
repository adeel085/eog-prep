<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserLoginSessionModel;

class Login extends BaseController
{
    public function index()
    {
        if ($this->user) {
            if ($this->user['user_type'] == 'admin' || $this->user['user_type'] == 'teacher') {
                return redirect()->to('/admin');
            }
            else {
                return redirect()->to('/page-selection');
            }
        }

        return view('login', [
            'pageTitle' => 'Login',
            'flashData' => $this->session->getFlashdata()
        ]);
    }

    public function login() {
        if ($this->user) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'You are already logged in']);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();

        $user = $userModel->where([
            'username' => $username,
            'user_type' => 'student'
        ])->first();

        if (!$user) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid username or password']);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid username or password']);
        }

        $this->session->set('user_id', $user['id']);

        // If a session already exists in database, delete it.
        $userLoginSessionModel = new UserLoginSessionModel();
        $userLoginSessionModel->where('user_id', $user['id'])->delete();

        $userLoginSessionModel->insert([
            'session_id' => session_id(),
            'user_id' => $user['id']
        ]);

        $this->session->setFlashdata('status', 'user_logged_in');

        return $this->response->setJSON(['status' => 'success']);
    }
}
