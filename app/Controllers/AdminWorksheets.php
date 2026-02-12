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

        $filterGradeId = $this->request->getGet('gradeId');

        $userModel = new UserModel();
        $topicModel = new TopicModel();
        $gradeModel = new GradeModel();

        $grades = $gradeModel->findAll();
        
        if ($this->user['user_type'] == 'admin') {
            $topics = $topicModel->where('owner_id', $this->user['id']);
        }
        else {
            $adminUser = $userModel->where('user_type', 'admin')->first();
            $topics = $topicModel->where('owner_id', $adminUser['id'])->orWhere('owner_id', $this->user['id']);
        }

        if ($filterGradeId) {
            $topics = $topics->where('grade_id', $filterGradeId)->findAll();
        }
        else {
            $topics = $topics->findAll();
        }

        return view('admin/worksheets', [
            'pageTitle' => 'Worksheets',
            'topics' => $topics,
            'grades' => $grades,
            'filterGradeId' => $filterGradeId,
            'flashData' => $this->session->getFlashdata(),
            'user' => $this->user
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

        $gradeId = $this->request->getGet('gradeId');
        $topicId = $this->request->getGet('topicId');
        $level = $this->request->getGet('level');
        $columns = $this->request->getGet('columns');
        $paperSize = $this->request->getGet('paperSize');
        $worksheetTitle = $this->request->getGet('title');
        $includeAnswers = $this->request->getGet('answers');

        if (empty($columns)) {
            $columns = 1;
        }

        if (empty($paperSize)) {
            $paperSize = 'letter';
        }

        if (empty($worksheetTitle)) {
            $worksheetTitle = 'Worksheet';
        }

        if (!$topicId || !$level) {
            return redirect()->to(base_url('/admin/worksheets'));
        }

        $topicModel = new TopicModel();
        $topic = $topicModel->find($topicId);

        if (!$topic) {
            return redirect()->to(base_url('/admin/worksheets'));
        }

        $questionModel = new QuestionModel();
        $topicQuestionsModel = new TopicQuestionsModel();
        $questionAnswersModel = new QuestionAnswersModel();

        $topicQuestions = $topicQuestionsModel->where('topic_id', $topicId)->findAll();

        $questionsIds = [];
        foreach ($topicQuestions as $topicQuestion) {
            $questionsIds[] = $topicQuestion['question_id'];
        }

        if ($gradeId) {
            $questions = $questionModel->whereIn('id', $questionsIds)->where('level', $level)->where('grade_id', $gradeId)->findAll();
        }
        else {
            $questions = $questionModel->whereIn('id', $questionsIds)->where('level', $level)->findAll();
        }

        if (count($questions) == 0) {
            $this->session->setFlashdata('status', 'no_questions_found_worksheet');
            return redirect()->to(base_url('/admin/worksheets'));
        }

        foreach ($questions as &$question) {
            $answers = $questionAnswersModel->where('question_id', $question['id'])->findAll();
            $question['answers'] = $answers;
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
