<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TopicModel;
use App\Models\QuestionModel;
use App\Models\TopicQuestionsModel;

class AdminTopics extends BaseController
{
    public function index()
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin') {
            return redirect()->to(base_url('/'));
        }

        $topicModel = new TopicModel();
        $userModel = new UserModel();
        
        if ($this->user['user_type'] == 'admin') {
            $topics = $topicModel->where('owner_id', $this->user['id'])->findAll();
        }
        else {
            $adminUser = $userModel->where('user_type', 'admin')->first();
            $topics = $topicModel->where('owner_id', $adminUser['id'])->orWhere('owner_id', $this->user['id'])->findAll();
        }

        return view('admin/topics', [
            'pageTitle' => 'Topics',
            'topics' => $topics,
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

        return view('admin/topics_new', [
            'pageTitle' => 'New Topic',
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
        $tutorialLink = $this->request->getPost('tutorialLink');
        $questionIds = $this->request->getPost('questionIds');

        if (!$name) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        if (empty($tutorialLink)) {
            $tutorialLink = null;
        }

        $topicModel = new TopicModel();

        $topic = $topicModel->where('name', $name)->first();

        if ($topic) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Topic already exists']);
        }

        $topicId = $topicModel->insert([
            'name' => $name,
            'tutorial_link' => $tutorialLink,
            'owner_id' => $this->user['id']
        ], true);

        if ($questionIds) {
            $topicQuestionsModel = new TopicQuestionsModel();
            $questionIds = explode(',', $questionIds);
            foreach ($questionIds as $questionId) {
                $topicQuestionsModel->insert([
                    'topic_id' => $topicId,
                    'question_id' => $questionId
                ]);
            }
        }

        if (!$topicId) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Internal Server Error']);
        }

        $this->session->setFlashdata('status', 'topic_created');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Topic created successfully']);
    }

    public function createTopicFromWizard()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }
        
        $topics = $this->request->getPost('topics');
        $topicName = $this->request->getPost('topicName');

        if (!$topics || !$topicName) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        $topics = json_decode($topics, true);

        $topicModel = new TopicModel();
        $topicQuestionsModel = new TopicQuestionsModel();
        $questionModel = new QuestionModel();

        if ($topicModel->where('name', $topicName)->first()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Topic already exists']);
        }

        $newTopicId = $topicModel->insert([
            'name' => $topicName,
            'tutorial_link' => null,
            'owner_id' => $this->user['id']
        ], true);

        if (!$newTopicId) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Internal Server Error']);
        }

        $questions_ids = [];

        foreach ($topics as $topic) {

            $topicId = $topic['topicId'];
            $level = $topic['level'];
            $maxQuestionsCount = $topic['maxQuestionsCount'];

            // Get questions from questionModel that have the level and then filter the questions that have the topic_id in the topicQuestionsModel
            $questions = $questionModel->where('level', $level)->findAll();
            $questions = array_filter($questions, function($question) use ($topicId, $topicQuestionsModel) {
                return $topicQuestionsModel->where('question_id', $question['id'])->where('topic_id', $topicId)->first();
            });

            $questions = array_slice($questions, 0, $maxQuestionsCount);

            foreach ($questions as $question) {
                $questions_ids[] = $question['id'];
            }
        }

        foreach ($questions_ids as $question_id) {
            $topicQuestionsModel->insert([
                'topic_id' => $newTopicId,
                'question_id' => $question_id
            ]);
        }

        // Set flashdata
        $this->session->setFlashdata('status', 'topic_created');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Topic created successfully']);
    }

    public function editPage($id)
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin') {
            return redirect()->to(base_url('/'));
        }

        $topicModel = new TopicModel();

        $topic = $topicModel->find($id);

        if (!$topic || $topic['owner_id'] != $this->user['id']) {
            return redirect()->to(base_url('/admin/topics'));
        }

        return view('admin/topics_edit', [
            'pageTitle' => 'Edit Topic',
            'topic' => $topic,
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
        $tutorialLink = $this->request->getPost('tutorialLink');

        if (!$id || !$name) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        if (empty($tutorialLink)) {
            $tutorialLink = null;
        }

        $topicModel = new TopicModel();

        $topic = $topicModel->find($id);

        if (!$topic || $topic['owner_id'] != $this->user['id']) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $topic = $topicModel->where('name', $name)->first();

        if ($topic && $topic['id'] != $id) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Topic already exists']);
        }

        $topicModel->update($id, [
            'name' => $name,
            'tutorial_link' => $tutorialLink,
        ]);

        $this->session->setFlashdata('status', 'topic_updated');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Topic updated successfully']);
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

        $topicModel = new TopicModel();

        $topic = $topicModel->find($id);

        if (!$topic || $topic['owner_id'] != $this->user['id']) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $topicModel->delete($id);

        $this->session->setFlashdata('status', 'topic_deleted');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Topic deleted successfully']);
    }

    public function questionsPage($topicId)
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin') {
            return redirect()->to(base_url('/'));
        }

        $topicModel = new TopicModel();
        $questionModel = new QuestionModel();
        $topicQuestionsModel = new TopicQuestionsModel();

        $topics = $topicModel->findAll();

        $topic = $topicModel->find($topicId);

        if (!$topic) {
            return redirect()->to(base_url('/admin/topics'));
        }

        $questions = $topicQuestionsModel->where('topic_id', $topicId)->paginate(10);

        foreach ($questions as &$question) {
            $question = $questionModel->find($question['question_id']);
            if ($question) {
                $question['topics'] = $topicQuestionsModel->where('question_id', $question['id'])->findAll();
            }
        }

        return view('admin/topics_questions', [
            'pageTitle' => 'Questions for ' . $topic['name'],
            'topic' => $topic,
            'topics' => $topics,
            'questions' => $questions,
            'pager' => $topicQuestionsModel->pager,
            'flashData' => $this->session->getFlashdata(),
            'user' => $this->user
        ]);
    }

    public function removeQuestion()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $topicId = $this->request->getPost('topicId');
        $questionId = $this->request->getPost('questionId');

        if (!$topicId || !$questionId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Bad Request']);
        }

        $topicQuestionsModel = new TopicQuestionsModel();

        $topicQuestionsModel->where(['topic_id' => $topicId, 'question_id' => $questionId])->delete();

        return $this->response->setJSON(['status' => 'success', 'message' => 'Question removed successfully']);
    }
}
