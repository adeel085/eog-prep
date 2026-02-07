<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GradeModel;
use App\Models\UserModel;
use App\Models\TopicModel;
use App\Models\QuestionModel;

class AdminGrades extends BaseController
{
    public function index()
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return redirect()->to(base_url('/'));
        }

        $gradeModel = new GradeModel();

        $grades = $gradeModel->findAll();

        return view('admin/grades', [
            'pageTitle' => 'Grades',
            'flashData' => $this->session->getFlashdata(),
            'grades' => $grades,
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

        if (!$name || trim($name) == '') {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        $gradeModel = new GradeModel();

        if ($gradeModel->where('name', $name)->first()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Grade with this name already exists']);
        }

        $gradeModel->insert(['name' => $name]);

        $this->session->setFlashdata('status', 'grade_created');

        return $this->response->setStatusCode(200)->setJSON(['status' => 'success', 'message' => 'Grade created successfully']);
    }

    public function delete()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $gradeId = $this->request->getPost('grade_id');

        if (!$gradeId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        $gradeModel = new GradeModel();
        $userModel = new UserModel();
        $questionModel = new QuestionModel();
        $topicModel = new TopicModel();

        if (!$gradeModel->find($gradeId)) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Grade not found']);
        }

        $gradeModel->delete($gradeId);

        $topicModel->set('grade_id', null)->where('grade_id', $gradeId)->update();
        $questionModel->set('grade_id', null)->where('grade_id', $gradeId)->update();

        $students = $userModel->getStudentsByGradeId($gradeId);

        foreach ($students as $student) {
            $userModel->deleteUserMeta('studentGradeId', $student['id'], true);
        }

        $this->session->setFlashdata('status', 'grade_deleted');

        return $this->response->setStatusCode(200)->setJSON(['status' => 'success', 'message' => 'Grade deleted successfully']);
    }

    public function update()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $gradeId = $this->request->getPost('grade_id');

        if (!$gradeId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        $gradeModel = new GradeModel();

        if (!$gradeModel->find($gradeId)) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Grade not found']);
        }

        $name = $this->request->getPost('name');

        if (!$name || trim($name) == '') {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        if ($gradeModel->where('name', $name)->where('id !=', $gradeId)->first()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Grade with this name already exists']);
        }

        $gradeModel->update($gradeId, ['name' => $name]);

        $this->session->setFlashdata('status', 'grade_updated');

        return $this->response->setStatusCode(200)->setJSON(['status' => 'success', 'message' => 'Grade updated successfully']);
    }
}
