<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\StudentGradeModel;

use CodeIgniter\Exceptions\PageNotFoundException;

class AdminTeachers extends BaseController
{
    public function index()
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin') {
            return redirect()->to(base_url('/'));
        }

        $userModel = new UserModel();
        $teachers = $userModel->where('user_type', 'teacher')->paginate(10);

        return view('admin/teachers', [
            'pageTitle' => 'Teachers',
            'teachers' => $teachers,
            'pager' => $userModel->pager,
            'flashData' => $this->session->getFlashdata(),
            'user' => $this->user
        ]);
    }

    public function newPage()
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin') {
            return redirect()->to(base_url('/'));
        }

        return view('admin/teachers_new', [
            'pageTitle' => 'New Teacher',
            'flashData' => $this->session->getFlashdata(),
            'user' => $this->user
        ]);
    }

    public function saveNew()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $name = $this->request->getPost('name');
        $username = $this->request->getPost('username');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (!$name || !$username || !$email || !$password) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        $userModel = new UserModel();

        $user = $userModel->where('username', $username)->first();

        if ($user) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Username already exists']);
        }

        $userId = $userModel->insert([
            'full_name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_type' => 'teacher'
        ], true);

        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Something went wrong']);
        }

        $this->session->setFlashdata('status', 'teacher_created');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Teacher created successfully']);
    }

    public function register()
    {
        $name = $this->request->getPost('name');
        $username = $this->request->getPost('username');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (!$name || !$username || !$email || !$password) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        $userModel = new UserModel();

        $user = $userModel->where('username', $username)->first();

        if ($user) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Username already exists']);
        }

        $userId = $userModel->insert([
            'full_name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_type' => 'teacher'
        ], true);

        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Something went wrong']);
        }

        $this->session->setFlashdata('status', 'teacher_registered');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Teacher account created successfully']);
    }

    public function editPage($id)
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin') {
            return redirect()->to(base_url('/'));
        }

        $userModel = new UserModel();
        $teacher = $userModel->find($id);

        if (!$teacher) {
            return redirect()->to(base_url('/admin/teachers'));
        }

        return view('admin/teachers_edit', [
            'pageTitle' => 'Edit Teacher',
            'teacher' => $teacher,
            'flashData' => $this->session->getFlashdata(),
            'user' => $this->user
        ]);
    }

    public function update()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $id = $this->request->getPost('id');
        $name = $this->request->getPost('name');
        $username = $this->request->getPost('username');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (!$id || !$name || !$username || !$email) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        $userModel = new UserModel();

        $user = $userModel->where('username', $username)->first();

        if ($user && $user['id'] != $id) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Username already exists']);
        }

        $userModel->update($id, [
            'full_name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password ? password_hash($password, PASSWORD_DEFAULT) : $userModel->find($id)['password']
        ]);

        $this->session->setFlashdata('status', 'teacher_updated');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Student updated successfully']);
    }

    public function delete()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $id = $this->request->getPost('id');

        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        $userModel = new UserModel();
        $studentGradeModel = new StudentGradeModel();
        $teacherDeleted = $userModel->delete($id);

        if ($teacherDeleted) {
            $userModel->deleteAllUserMeta($id);

            // Delete all the students of this teacher
            $students = $userModel->filterByStudentOf($id)->findAll();

            foreach ($students as $student) {
                $studentDeleted = $userModel->delete($student['id']);

                if ($studentDeleted) {
                    $userModel->deleteAllUserMeta($student['id']);
                    $studentGradeModel->where('student_id', $student['id'])->delete();
                }
            }

            $this->session->setFlashdata('status', 'teacher_deleted');
            return $this->response->setJSON(['status' => 'success', 'message' => 'Teacher deleted successfully']);
        }
        else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete teacher']);
        }
    }
}