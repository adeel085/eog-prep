<?php

namespace App\Controllers;

use App\Models\StudentProgressModel;
use App\Models\QuestionAnswersModel;
use App\Models\GradeModel;
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
        $selectedLevels = $this->normalizeLevels([$difficultyLevel]);

        $sessionOptions = $this->getStudentSessionOptions();
        $topic = $sessionOptions['topicsById'][(int) $topicId] ?? null;

        if (!$topic || count($selectedLevels) !== 1) {
            return redirect()->to(base_url('/page-selection'));
        }

        if (!$this->isLevelAllowedForTopic($topic, $difficultyLevel)) {
            return redirect()->to(base_url('/page-selection'));
        }

        $questions = $this->getQuestionsForTopicAndLevels($topicId, $selectedLevels);

        if (count($questions) == 0) {
            return redirect()->to(base_url('/page-selection'));
        }

        return $this->renderSessionView($topic, $selectedLevels, 'Level ' . $selectedLevels[0], $questions);
    }

    public function customTest()
    {
        if (!$this->user) {
            return redirect()->to('/');
        }

        if ($this->user['user_type'] != 'student') {
            return redirect()->to(base_url('/'));
        }

        $gradeId = $this->request->getGet('grade_id');
        $topicId = $this->request->getGet('topic_id');
        $difficultyLevel = $this->request->getGet('difficulty_level');
        $selectedLevels = $this->normalizeLevels([$difficultyLevel]);

        if (!$gradeId || !$topicId || count($selectedLevels) !== 1) {
            return redirect()->to(base_url('/page-selection'));
        }

        $customTestOptions = $this->getCustomTestOptions();
        $topic = $customTestOptions['topicsById'][(int) $topicId] ?? null;

        if (!$topic || (string) ($topic['grade_id'] ?? '') !== (string) $gradeId) {
            return redirect()->to(base_url('/page-selection'));
        }

        $questions = $this->getQuestionsForTopicAndLevels($topicId, $selectedLevels, $gradeId);

        if (count($questions) == 0) {
            return redirect()->to(base_url('/page-selection'));
        }

        return $this->renderSessionView($topic, $selectedLevels, 'Level ' . $selectedLevels[0], $questions);
    }

    public function pageSelection() {

        if (!$this->user) {
            return redirect()->to('/');
        }

        if ($this->user['user_type'] != 'student') {
            return redirect()->to(base_url('/'));
        }

        $sessionOptions = $this->getStudentSessionOptions();
        $customTestOptions = $this->getCustomTestOptions();
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
            'customTestGrades' => $customTestOptions['grades'],
            'customTestTopics' => $customTestOptions['topics'],
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
        $levels = $this->normalizeLevels(json_decode((string) $this->request->getPost('levels'), true) ?? ($level !== null ? [$level] : []));
        $correctCount = $this->request->getPost('correct_count');
        $totalQuestions = $this->request->getPost('total_questions');
        $answeredQuestionsJson = $this->request->getPost('answered_questions');

        if (!$topicId || empty($levels) || $correctCount === null || !$totalQuestions) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Missing required parameters']);
        }

        $percentage = ($correctCount / $totalQuestions) * 100;
        $answeredQuestions = [];

        if ($answeredQuestionsJson) {
            $decodedAnsweredQuestions = json_decode($answeredQuestionsJson, true);
            if (is_array($decodedAnsweredQuestions)) {
                $answeredQuestions = $decodedAnsweredQuestions;
            }
        }

        $studentSessionResultModel = new StudentSessionResultModel();
        $sessionResultRows = $this->buildSessionResultRows($topicId, $levels, $answeredQuestions, $percentage);
        foreach ($sessionResultRows as $row) {
            $studentSessionResultModel->insert([
                'student_id' => $this->user['id'],
                'topic_id' => $topicId,
                'level' => $row['level'],
                'percentage' => $row['percentage']
            ]);
        }

        // Save individual question results
        if (!empty($answeredQuestions)) {
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
            return $this->getStudentGradeTopics(true);
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

    private function getStudentGradeTopics($hasAssignedTopics = false)
    {
        $userModel = new UserModel();
        $topicModel = new TopicModel();

        $studentGradeId = $userModel->getUserMeta('studentGradeId', $this->user['id'], true);
        $gradeTopics = $topicModel->where('grade_id', $studentGradeId)->orderBy('name', 'ASC')->findAll();

        $topics = [];
        foreach ($gradeTopics as $topic) {
            $topics[(int) $topic['id']] = [
                'id' => (int) $topic['id'],
                'name' => $topic['name'],
                'grade_id' => $topic['grade_id'],
                'allows_all_levels' => true,
                'assigned_levels' => []
            ];
        }

        return [
            'topics' => array_values($topics),
            'topicsById' => $topics,
            'hasAssignedTopics' => $hasAssignedTopics
        ];
    }

    private function getCustomTestOptions()
    {
        $gradeModel = new GradeModel();
        $topicModel = new TopicModel();

        $grades = $gradeModel->orderBy('name', 'ASC')->findAll();
        $customTestTopics = $topicModel
            ->where('grade_id IS NOT NULL', null, false)
            ->orderBy('grade_id', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        $topics = [];
        foreach ($customTestTopics as $topic) {
            $topics[(int) $topic['id']] = [
                'id' => (int) $topic['id'],
                'name' => $topic['name'],
                'grade_id' => $topic['grade_id'],
                'allows_all_levels' => true,
                'assigned_levels' => []
            ];
        }

        return [
            'grades' => $grades,
            'topics' => array_values($topics),
            'topicsById' => $topics
        ];
    }

    private function getQuestionsForTopicAndLevels($topicId, array $levels, $gradeId = null)
    {
        if (empty($levels)) {
            return [];
        }

        $userModel = new UserModel();
        $questionModel = new QuestionModel();
        $topicQuestionsModel = new TopicQuestionsModel();

        $topicQuestions = $topicQuestionsModel->where('topic_id', $topicId)->findAll();

        $questionsIds = [];
        foreach ($topicQuestions as $topicQuestion) {
            $questionsIds[] = $topicQuestion['question_id'];
        }

        if (empty($questionsIds)) {
            return [];
        }

        $userGradeId = $gradeId ?: $userModel->getUserMeta('studentGradeId', $this->user['id'], true);

        return $questionModel
            ->whereIn('id', $questionsIds)
            ->whereIn('level', $levels)
            ->where('grade_id', $userGradeId)
            ->findAll();
    }

    private function renderSessionView($topic, array $selectedLevels, $currentLevelLabel, array $questions)
    {
        $questionAnswersModel = new QuestionAnswersModel();

        foreach ($questions as &$question) {
            $answers = $questionAnswersModel->where('question_id', $question['id'])->findAll();
            $question['answers'] = $answers;
        }

        shuffle($questions);

        return view('home', [
            'pageTitle' => 'Home',
            'user' => $this->user,
            'currentTopic' => $topic,
            'currentLevelLabel' => $currentLevelLabel,
            'sessionLevels' => $selectedLevels,
            'questions' => $questions
        ]);
    }

    private function buildSessionResultRows($topicId, array $levels, array $answeredQuestions, $overallPercentage)
    {
        if (count($levels) === 1) {
            return [[
                'topic_id' => $topicId,
                'level' => $levels[0],
                'percentage' => $overallPercentage
            ]];
        }

        $statsByLevel = [];
        foreach ($answeredQuestions as $answeredQuestion) {
            $questionLevel = isset($answeredQuestion['level']) ? (int) $answeredQuestion['level'] : null;

            if (!$questionLevel || !in_array($questionLevel, $levels, true)) {
                continue;
            }

            if (!isset($statsByLevel[$questionLevel])) {
                $statsByLevel[$questionLevel] = [
                    'total' => 0,
                    'correct' => 0
                ];
            }

            $statsByLevel[$questionLevel]['total']++;

            if ((int) $answeredQuestion['is_correct'] === 1) {
                $statsByLevel[$questionLevel]['correct']++;
            }
        }

        $rows = [];
        foreach ($levels as $level) {
            if (empty($statsByLevel[$level]['total'])) {
                continue;
            }

            $rows[] = [
                'topic_id' => $topicId,
                'level' => $level,
                'percentage' => ($statsByLevel[$level]['correct'] / $statsByLevel[$level]['total']) * 100
            ];
        }

        return $rows;
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

    private function normalizeLevels($levels)
    {
        if (is_string($levels)) {
            $levels = explode(',', $levels);
        }

        if (!is_array($levels)) {
            return [];
        }

        $normalizedLevels = [];
        foreach ($levels as $level) {
            if (in_array((string) $level, ['1', '2', '3'], true)) {
                $normalizedLevels[] = (int) $level;
            }
        }

        $normalizedLevels = array_values(array_unique($normalizedLevels));
        sort($normalizedLevels);

        return $normalizedLevels;
    }
}
