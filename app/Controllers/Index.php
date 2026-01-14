<?php

namespace App\Controllers;

class Index extends BaseController
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

        return view('index');
    }
}
