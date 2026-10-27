<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TopicModel;
use App\Models\TopicQuestionsModel;
use App\Models\QuestionModel;
use App\Models\QuestionAnswersModel;
use App\Models\GradeModel;

class AdminQuestions extends BaseController
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
        $topicModel = new TopicModel();
        $topicQuestionsModel = new TopicQuestionsModel();
        $gradeModel = new GradeModel();

        $topics = $topicModel->findAll();

        $questionModel = new QuestionModel();

        if ($this->user['user_type'] == 'admin') {
            $questions = $questionModel->where('owner_id', $this->user['id'])->orderBy('id', 'DESC')->paginate(10);
        }
        else {
            $adminUser = $userModel->where('user_type', 'admin')->first();
            $questions = $questionModel->where('owner_id', $this->user['id'])->orWhere('owner_id', $adminUser['id'])->orderBy('id', 'DESC')->paginate(10);
        }

        foreach ($questions as &$question) {
            $question['topics'] = $topicQuestionsModel->where('question_id', $question['id'])->findAll();

            if ($question['grade_id']) {
                $question['grade'] = $gradeModel->find($question['grade_id']);
            }
            else {
                $question['grade'] = null;
            }
        }

        return view('admin/questions', [
            'pageTitle' => 'Questions',
            'questions' => $questions,
            'pager' => $questionModel->pager,
            'topics' => $topics,
            'grades' => $gradeModel->findAll(),
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

        $topicModel = new TopicModel();
        $gradeModel = new GradeModel();

        $topics = $topicModel->where('owner_id', $this->user['id'])->findAll();
        $grades = $gradeModel->findAll();

        return view('admin/questions_new', [
            'pageTitle' => 'New Question',
            'topics' => $topics,
            'grades' => $grades,
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

        $topicId = $this->request->getPost('topicId');
        $difficulty = $this->request->getPost('difficulty');
        $gradeId = $this->request->getPost('gradeId');
        $questionType = $this->request->getPost('questionType');
        $question = $this->request->getPost('question');
        $solution = $this->request->getPost('solution');
        $answers = json_decode(base64_decode($this->request->getPost('answers')));
        $correctAnswerIndex = (int) $this->request->getPost('correctAnswerIndex');
        $calculatorEnabled = $this->request->getPost('calculatorEnabled');

        $questionModel = new QuestionModel();
        $topicQuestionsModel = new TopicQuestionsModel();
        $questionAnswersModel = new QuestionAnswersModel();

        $questionId = $questionModel->insert([
            'level' => $difficulty,
            'question_type' => $questionType,
            'question_html' => $question,
            'solution_html' => $solution,
            'owner_id' => $this->user['id'],
            'grade_id' => $gradeId,
            'calculator_enabled' => $calculatorEnabled
        ]);

        $topicQuestionsModel->insert([
            'topic_id' => $topicId,
            'question_id' => $questionId
        ]);

        foreach ($answers as $index => $answer) {
            $questionAnswersModel->insert([
                'question_id' => $questionId,
                'answer' => $answer,
                'is_correct' => $questionType == "text" || $correctAnswerIndex == $index ? 1 : 0
            ]);
        }

        $this->session->setFlashdata('status', 'question_created');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Question saved successfully']);
    }

    public function editPage($id)
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin') {
            return redirect()->to(base_url('/'));
        }

        $questionModel = new QuestionModel();
        $gradeModel = new GradeModel();

        $question = $questionModel->find($id);

        $grades = $gradeModel->findAll();

        if (!$question || $question['owner_id'] != $this->user['id']) {
            return redirect()->to(base_url('/admin/questions'));
        }

        $questionAnswersModel = new QuestionAnswersModel();
        $question['answers'] = $questionAnswersModel->where('question_id', $id)->findAll();

        return view('admin/questions_edit', [
            'pageTitle' => 'Edit Question',
            'question' => $question,
            'grades' => $grades,
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

        $questionId = $this->request->getPost('questionId');
        $difficulty = $this->request->getPost('difficulty');
        $gradeId = $this->request->getPost('gradeId');
        $questionType = $this->request->getPost('questionType');
        $question_html = $this->request->getPost('question');
        $solution = $this->request->getPost('solution');
        $answers = json_decode(base64_decode($this->request->getPost('answers')));
        $correctAnswerIndex = (int) $this->request->getPost('correctAnswerIndex');
        $calculatorEnabled = $this->request->getPost('calculatorEnabled');

        $questionModel = new QuestionModel();
        $questionAnswersModel = new QuestionAnswersModel();

        $question = $questionModel->find($questionId);

        if (!$question || $question['owner_id'] != $this->user['id']) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $updated = $questionModel->update($questionId, [
            'level' => $difficulty,
            'question_type' => $questionType,
            'question_html' => $question_html,
            'solution_html' => $solution,
            'grade_id' => $gradeId,
            'calculator_enabled' => $calculatorEnabled
        ]);

        if (!$updated) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update question']);
        }

        $questionAnswersModel->where('question_id', $questionId)->delete();

        foreach ($answers as $index => $answer) {
            $questionAnswersModel->insert([
                'question_id' => $questionId,
                'answer' => $answer,
                'is_correct' => $questionType == "text" || $correctAnswerIndex == $index ? 1 : 0
            ]);
        }

        $this->session->setFlashdata('status', 'question_updated');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Question updated successfully']);
    }

    public function delete()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $questionId = $this->request->getPost('questionId');

        $questionModel = new QuestionModel();

        $question = $questionModel->find($questionId);

        if (!$question || $question['owner_id'] != $this->user['id']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Question not found']);
        }

        $topicQuestionsModel = new TopicQuestionsModel();
        $topicQuestionsModel->where('question_id', $questionId)->delete();

        $questionAnswersModel = new QuestionAnswersModel();
        $questionAnswersModel->where('question_id', $questionId)->delete();

        $deleted = $questionModel->delete($questionId);

        if (!$deleted) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete question']);
        }

        $this->session->setFlashdata('status', 'question_deleted');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Question deleted successfully']);
    }

    public function importPackage()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Your session has expired. Sign in again.']);
        }
        if ($this->user['user_type'] !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Only admins may import question packages.']);
        }
        try {
            $file = $this->request->getFile('package');
            if (!$file || !$file->isValid() || $file->hasMoved() || strtolower($file->getClientExtension()) !== 'zip'
                || $file->getSize() > \App\Libraries\ProblemPackageImporter::MAX_ZIP_BYTES) {
                throw new \App\Libraries\PackageValidationException(['Upload a valid ZIP package of at most 25 MB. Check the server upload limit if the file could not be received.']);
            }
            $counts = (new \App\Libraries\ProblemPackageImporter())->import($file->getTempName(), (int) $this->user['id'],
                (string) $this->request->getPost('topicId'), (string) $this->request->getPost('difficulty'), (string) $this->request->getPost('gradeId'));
            return $this->response->setJSON(['status' => 'success', 'message' => "Imported {$counts['questions']} question(s) and {$counts['images']} image(s).",
                'counts' => $counts, 'csrfHash' => csrf_hash()]);
        } catch (\Throwable $error) {
            $errors = $error instanceof \App\Libraries\PackageValidationException ? $error->errors : ['The package could not be imported. Check the migration, server ZIP support and storage permissions.'];
            log_message('error', 'Package request failed: {error}', ['error' => $error->getMessage()]);
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'No questions or images were imported.', 'errors' => $errors, 'csrfHash' => csrf_hash()]);
        }
    }

    public function sampleCsv()
    {
        if (!$this->user || $this->user['user_type'] !== 'admin') {
            return $this->response->setStatusCode($this->user ? 403 : 401)->setBody('Admin access required.');
        }
        return $this->response->download(FCPATH . 'public/downloads/problem-package-sample.csv', null)->setFileName('problems.csv');
    }

    public function updateTopics() {
        
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $questionId = $this->request->getPost('questionId');
        $topicsIds = explode(',', $this->request->getPost('topicsIds'));

        $topicQuestionsModel = new TopicQuestionsModel();
        $topicQuestionsModel->where('question_id', $questionId)->delete();

        foreach ($topicsIds as $topicId) {
            $topicQuestionsModel->insert([
                'topic_id' => $topicId,
                'question_id' => $questionId
            ]);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Topics updated successfully']);
    }

    public function getQuestionDetails()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $questionIds = $this->request->getPost('questionIds');
        if (!$questionIds) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No question IDs provided']);
        }

        $questionIdsArray = explode(',', $questionIds);
        $questionModel = new QuestionModel();
        $questions = $questionModel->whereIn('id', $questionIdsArray)->findAll();

        // Filter questions to only show those owned by the current user
        $userQuestions = [];
        foreach ($questions as $question) {
            if ($question['owner_id'] == $this->user['id']) {
                $userQuestions[] = $question;
            }
        }

        return $this->response->setJSON(['status' => 'success', 'questions' => $userQuestions]);
    }

    public function basketPage()
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin') {
            return redirect()->to(base_url('/'));
        }

        return view('admin/questions_basket', [
            'pageTitle' => 'Questions Basket',
            'flashData' => $this->session->getFlashdata(),
            'user' => $this->user
        ]);
    }
}
