<?php

namespace App\Controllers;

use App\Models\StudentProgressModel;
use App\Models\QuestionAnswersModel;
use App\Models\TopicModel;
use App\Models\QuestionModel;
use App\Models\ClassTopicLevelModel;
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

        $sessionOptions = $this->getStudentSessionOptions();
        $topic = $sessionOptions['topicsById'][(int) $topicId] ?? null;

        if (!$topic) {
            return redirect()->to(base_url('/page-selection'));
        }

        $userModel = new UserModel();
        $questionModel = new QuestionModel();
        $topicQuestionsModel = new TopicQuestionsModel();
        $questionAnswersModel = new QuestionAnswersModel();

        if (!$this->isLevelAllowedForTopic($topic, $difficultyLevel)) {
            return redirect()->to(base_url('/page-selection'));
        }

        $topicQuestions = $topicQuestionsModel->where('topic_id', $topicId)->findAll();

        $questionsIds = [];
        foreach ($topicQuestions as $topicQuestion) {
            $questionsIds[] = $topicQuestion['question_id'];
        }

        $userGradeId = $userModel->getUserMeta('studentGradeId', $this->user['id'], true);

        $questions = $questionModel->whereIn('id', $questionsIds)->where('level', $difficultyLevel)->where('grade_id', $userGradeId)->findAll();

        if (count($questions) == 0) {
            return redirect()->to(base_url('/page-selection'));
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

        $sessionOptions = $this->getStudentSessionOptions();
        $autoStartSessionUrl = null;

        if (
            $sessionOptions['hasAssignedTopics']
            && count($sessionOptions['topics']) === 1
            && empty($sessionOptions['topics'][0]['allows_all_levels'])
            && count($sessionOptions['topics'][0]['assigned_levels']) === 1
        ) {
            $autoStartSessionUrl = base_url('home') . '?topic_id=' . $sessionOptions['topics'][0]['id'] . '&difficulty_level=' . $sessionOptions['topics'][0]['assigned_levels'][0];
        }

        return view('page_selection', [
            'pageTitle' => 'Page Selection',
            'user' => $this->user,
            'topics' => $sessionOptions['topics'],
            'hasAssignedTopics' => $sessionOptions['hasAssignedTopics'],
            'autoStartSessionUrl' => $autoStartSessionUrl
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
        $answeredQuestionsJson = $this->request->getPost('answered_questions');

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

        // Save individual question results
        if ($answeredQuestionsJson) {
            $answeredQuestions = json_decode($answeredQuestionsJson, true);
            if (is_array($answeredQuestions)) {
                $studentQuestionsResultsModel = new StudentQuestionsResultsModel();
                foreach ($answeredQuestions as $answered) {
                    $studentQuestionsResultsModel->insert([
                        'student_id' => $this->user['id'],
                        'question_id' => $answered['question_id'],
                        'student_answer' => $answered['student_answer'],
                        'is_correct' => $answered['is_correct']
                    ]);
                }
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'correct_answers' => $correctCount,
                'total_questions' => $totalQuestions,
                'percentage' => round($percentage, 2)
            ]
        ]);
    }

    private function getStudentSessionOptions()
    {
        $userModel = new UserModel();
        $topicModel = new TopicModel();

        $studentGradeId = $userModel->getUserMeta('studentGradeId', $this->user['id'], true);
        $studentClassId = $userModel->getUserMeta('studentClassId', $this->user['id'], true);

        $topics = [];
        $hasAssignedTopics = false;

        if ($studentClassId) {
            $classTopicLevelModel = new ClassTopicLevelModel();
            $assignments = $classTopicLevelModel
                ->select('classes_topics_levels.topic_id, classes_topics_levels.level, topics.name, topics.grade_id')
                ->join('topics', 'topics.id = classes_topics_levels.topic_id')
                ->where('classes_topics_levels.class_id', $studentClassId)
                ->orderBy('topics.name', 'ASC')
                ->orderBy('classes_topics_levels.level', 'ASC')
                ->findAll();

            if (!empty($assignments)) {
                $hasAssignedTopics = true;

                foreach ($assignments as $assignment) {
                    $topicId = (int) $assignment['topic_id'];

                    if (!isset($topics[$topicId])) {
                        $topics[$topicId] = [
                            'id' => $topicId,
                            'name' => $assignment['name'],
                            'grade_id' => $assignment['grade_id'],
                            'allows_all_levels' => false,
                            'assigned_levels' => []
                        ];
                    }

                    if ($assignment['level'] === null) {
                        $topics[$topicId]['allows_all_levels'] = true;
                        $topics[$topicId]['assigned_levels'] = [];
                        continue;
                    }

                    $topics[$topicId]['assigned_levels'][] = (int) $assignment['level'];
                }
            }
        }

        if (!$hasAssignedTopics) {
            $gradeTopics = $topicModel->where('grade_id', $studentGradeId)->orderBy('name', 'ASC')->findAll();

            foreach ($gradeTopics as $topic) {
                $topics[(int) $topic['id']] = [
                    'id' => (int) $topic['id'],
                    'name' => $topic['name'],
                    'grade_id' => $topic['grade_id'],
                    'allows_all_levels' => true,
                    'assigned_levels' => []
                ];
            }
        }

        foreach ($topics as &$topic) {
            $topic['assigned_levels'] = array_values(array_unique($topic['assigned_levels']));
            sort($topic['assigned_levels']);
        }

        return [
            'topics' => array_values($topics),
            'topicsById' => $topics,
            'hasAssignedTopics' => $hasAssignedTopics
        ];
    }

    private function isLevelAllowedForTopic($topic, $difficultyLevel)
    {
        if (!in_array((string) $difficultyLevel, ['1', '2', '3'], true)) {
            return false;
        }

        if (!empty($topic['allows_all_levels'])) {
            return true;
        }

        return in_array((int) $difficultyLevel, $topic['assigned_levels'], true);
    }
}
