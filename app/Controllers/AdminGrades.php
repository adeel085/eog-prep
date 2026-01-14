<?php

namespace App\Controllers;

use App\Models\GradeModel;
use App\Models\TopicModel;
use App\Models\GradeTopicsModel;

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
        $topicModel = new TopicModel();
        $gradeTopicsModel = new GradeTopicsModel();

        $grades = $gradeModel->where('owner_id', $this->user['id'])->findAll();
        $topics = $topicModel->where('owner_id', $this->user['id'])->findAll();
        
        foreach ($grades as &$grade) {
            $grade['topics'] = $gradeTopicsModel->where('grade_id', $grade['id'])->findAll();
        }

        return view('admin/grades', [
            'pageTitle' => 'Grades',
            'grades' => $grades,
            'topics' => $topics,
            'flashData' => $this->session->getFlashdata(),
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

        $gradeModel = new GradeModel();

        if ($gradeModel->where(['name' => $name, 'owner_id' => $this->user['id']])->first()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Grade already exists']);
        }

        $gradeModel->insert(['name' => $name, 'owner_id' => $this->user['id']]);

        $this->session->setFlashdata('status', 'grade_created');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Grade created successfully']);
    }

    public function update()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $gradeName = $this->request->getPost('grade_name');
        $name = $this->request->getPost('new_name');

        if (!$gradeName || !$name || trim($name) == '') {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        $gradeModel = new GradeModel();

        $grade = $gradeModel->where(['name' => $name, 'owner_id' => $this->user['id']])->first();

        if ($grade && $grade['name'] != $gradeName) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Grade with this name already exists']);
        }

        $gradeModel->set(['name' => $name])->where('name', $gradeName)->update();

        $this->session->setFlashdata('status', 'grade_updated');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Grade updated successfully']);
    }

    public function delete()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $name = $this->request->getPost('name');

        if (!$name) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        $gradeModel = new GradeModel();

        $gradeModel->where(['name' => $name, 'owner_id' => $this->user['id']])->delete();

        $this->session->setFlashdata('status', 'grade_deleted');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Grade deleted successfully']);
    }

    public function updateTopics()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $gradeId = $this->request->getPost('gradeId');
        $topicsIds = explode(',', $this->request->getPost('topicsIds'));

        $gradeTopicsModel = new GradeTopicsModel();
        $gradeTopicsModel->where('grade_id', $gradeId)->delete();

        foreach ($topicsIds as $topicId) {
            $gradeTopicsModel->insert([
                'grade_id' => $gradeId,
                'topic_id' => $topicId
            ]);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Topics added/removed successfully']);
    }
}
