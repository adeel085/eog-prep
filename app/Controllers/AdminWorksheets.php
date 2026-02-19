<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TopicModel;
use App\Models\QuestionModel;
use App\Models\TopicQuestionsModel;
use App\Models\GradeModel;
use App\Models\QuestionAnswersModel;

class AdminWorksheets extends BaseController
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
        $gradeModel = new GradeModel();

        $grades = $gradeModel->findAll();

        foreach ($grades as &$grade) {
            $grade['topics'] = $topicModel->where('grade_id', $grade['id'])->findAll();
        }
        
        if ($this->user['user_type'] == 'admin') {
            $topics = $topicModel->where('owner_id', $this->user['id']);
        }
        else {
            $adminUser = $userModel->where('user_type', 'admin')->first();
            $topics = $topicModel->where('owner_id', $adminUser['id'])->orWhere('owner_id', $this->user['id']);
        }

        $topics = $topics->findAll();

        return view('admin/worksheets', [
            'pageTitle' => 'Worksheets',
            'topics' => $topics,
            'grades' => $grades,
            'flashData' => $this->session->getFlashdata(),
            'user' => $this->user
        ]);
    }

    public function getQuestions()
    {
        if (!$this->user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }

        if ($this->user['user_type'] != 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Forbidden'
            ]);
        }

        $criteria = $this->request->getPost('criteria') ?? [];

        if (!empty($criteria)) {
            $criteria = json_decode($criteria, true);
        }
        else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No criteria provided'
            ]);
        }

        $questionModel = new QuestionModel();
        $topicQuestionsModel = new TopicQuestionsModel();

        $questions = [];

        foreach ($criteria as $criterion) {

            $gradeId = $criterion['gradeId'];
            $topicId = $criterion['topicId'];
            $level1QuestionsCount = (int)$criterion['level1QuestionsCount'];
            $level2QuestionsCount = (int)$criterion['level2QuestionsCount'];
            $level3QuestionsCount = (int)$criterion['level3QuestionsCount'];

            $topicQuestions = $topicQuestionsModel->select('question_id')->where('topic_id', $topicId)->findAll();

            $questionsIds = [];
            foreach ($topicQuestions as $topicQuestion) {
                $questionsIds[] = $topicQuestion['question_id'];
            }

            if (empty($questionsIds)) {
                continue;
            }

            if (!empty($gradeId)) {
                $questions = array_merge($questions, $questionModel->select('id')->whereIn('id', $questionsIds)->where('grade_id', $gradeId)->where('level', 1)->limit($level1QuestionsCount)->findAll());
                $questions = array_merge($questions, $questionModel->select('id')->whereIn('id', $questionsIds)->where('grade_id', $gradeId)->where('level', 2)->limit($level2QuestionsCount)->findAll());
                $questions = array_merge($questions, $questionModel->select('id')->whereIn('id', $questionsIds)->where('grade_id', $gradeId)->where('level', 3)->limit($level3QuestionsCount)->findAll());
            }
            else {
                $questions = array_merge($questions, $questionModel->select('id')->whereIn('id', $questionsIds)->where('level', 1)->limit($level1QuestionsCount)->findAll());
                $questions = array_merge($questions, $questionModel->select('id')->whereIn('id', $questionsIds)->where('level', 2)->limit($level2QuestionsCount)->findAll());
                $questions = array_merge($questions, $questionModel->select('id')->whereIn('id', $questionsIds)->where('level', 3)->limit($level3QuestionsCount)->findAll());
            }   
        }

        if (count($questions) == 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No questions found for the given criteria'
            ]);
        }

        $questionsIds = [];
        foreach ($questions as $question) {
            $questionsIds[] = $question['id'];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'questionsIds' => $questionsIds
            ]
        ]);
    }

    public function print()
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin') {
            return redirect()->to(base_url('/'));
        }

        $questionsIds = $this->request->getGet('questionsIds');
        $worksheetTitle = $this->request->getGet('worksheetTitle');
        $paperSize = $this->request->getGet('paperSize');
        $columns = $this->request->getGet('columns');
        $includeAnswers = $this->request->getGet('includeAnswers');

        if (empty($questionsIds)) {
            return redirect()->to(base_url('/admin/worksheets'));
        }

        $questionsIdsArray = explode(',', $questionsIds);

        if (empty($columns)) {
            $columns = 1;
        }

        if (empty($paperSize)) {
            $paperSize = 'letter';
        }

        if (empty($worksheetTitle)) {
            $worksheetTitle = 'Worksheet';
        }
        
        $questionModel = new QuestionModel();
        $questionAnswersModel = new QuestionAnswersModel();

        $questions = $questionModel->whereIn('id', $questionsIdsArray)->findAll();

        foreach ($questions as &$question) {
            $question['answers'] = $questionAnswersModel->where('question_id', $question['id'])->findAll();
        }

        if (!empty($includeAnswers) && $includeAnswers == 1) {
            $view = "worksheets_print_answers";
        }
        else {
            $view = "worksheets_print";
        }

        return view('admin/' . $view, [
            'pageTitle' => 'Print Worksheet',
            'problems' => $questions,
            'columns' => $columns,
            'paperSize' => $paperSize,
            'worksheetTitle' => $worksheetTitle
        ]);
    }
}
