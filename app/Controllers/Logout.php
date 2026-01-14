<?php

namespace App\Controllers;

class Logout extends BaseController
{
    public function index()
    {
        if (!$this->user) {
            return redirect()->to('/');
        }

        $userType = $this->user['user_type'];

        // destroy session
        $this->session->destroy();
        // delete remember_me cookie
        $this->response->deleteCookie('remember_me');

        if ($userType == 'admin' || $userType == 'teacher') {
            return redirect()->to(base_url('/admin'));
        }

        return redirect()->to(base_url('/'));
    }
}
