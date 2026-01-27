<?php

namespace App\Controllers;

use App\Models\StudentProgressModel;
use App\Models\QuestionAnswersModel;
use App\Models\TopicModel;
use App\Models\QuestionModel;
use App\Models\UserLoginSessionModel;
use App\Models\TopicQuestionsModel;
use App\Models\StudentQuestionsResultsModel;
use App\Models\StudentSessionResultModel;
use App\Models\UserModel;

use DateTime;

class Home extends BaseController
{
    public function index()
    {
        if (!$this->user) {
            return redirect()->to('/');
        }

        if ($this->user['user_type'] != 'student') {
            return redirect()->to(base_url('/'));
        }

        $topicId = $this->request->getGet('topic_id');
        $difficultyLevel = $this->request->getGet('difficulty_level');

        $topicModel = new TopicModel();
        $topic = $topicModel->find($topicId);

        if (!$topic) {
            return redirect()->to(base_url('/'));
        }

        $userModel = new UserModel();
        $questionModel = new QuestionModel();
        $topicQuestionsModel = new TopicQuestionsModel();
        $questionAnswersModel = new QuestionAnswersModel();

        $topicQuestions = $topicQuestionsModel->where('topic_id', $topicId)->findAll();

        $questionsIds = [];
        foreach ($topicQuestions as $topicQuestion) {
            $questionsIds[] = $topicQuestion['question_id'];
        }

        $userGradeId = $userModel->getUserMeta('studentGradeId', $this->user['id'], true);

        $questions = $questionModel->whereIn('id', $questionsIds)->where('level', $difficultyLevel)->where('grade_id', $userGradeId)->findAll();

        if (count($questions) == 0) {
            return redirect()->to(base_url('/'));
        }

        foreach ($questions as &$question) {
            $answers = $questionAnswersModel->where('question_id', $question['id'])->findAll();
            $question['answers'] = $answers;
        }

        // Randomize the questions
        shuffle($questions);

        return view('home', [
            'pageTitle' => 'Home',
            'user' => $this->user,
            'currentTopic' => $topic,
            'currentLevel' => $difficultyLevel,
            'questions' => $questions
        ]);
    }

    public function pageSelection() {

        if (!$this->user) {
            return redirect()->to('/');
        }

        if ($this->user['user_type'] != 'student') {
            return redirect()->to(base_url('/'));
        }

        $topicModel = new TopicModel();
        $topics = $topicModel->findAll();

        return view('page_selection', [
            'pageTitle' => 'Page Selection',
            'user' => $this->user,
            'topics' => $topics
        ]);
    }

    public function storeSessionResult() {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'student') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $topicId = $this->request->getPost('topic_id');
        $level = $this->request->getPost('level');
        $correctCount = $this->request->getPost('correct_count');
        $totalQuestions = $this->request->getPost('total_questions');

        if (!$topicId || !$level || $correctCount === null || !$totalQuestions) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Missing required parameters']);
        }

        $percentage = ($correctCount / $totalQuestions) * 100;

        $studentSessionResultModel = new StudentSessionResultModel();
        $studentSessionResultModel->insert([
            'student_id' => $this->user['id'],
            'topic_id' => $topicId,
            'level' => $level,
            'percentage' => $percentage
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'correct_answers' => $correctCount,
                'total_questions' => $totalQuestions,
                'percentage' => round($percentage, 2)
            ]
        ]);
    }
}
