<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\QuestionModel;
use App\Models\QuestionAnswersModel;

use CodeIgniter\Exceptions\PageNotFoundException;

class Reports extends BaseController
{
    public function index()
    {
        return;
    }

    public function reportMissingQuestions()
    {
        $studentId = $this->request->getGet('st');
        $startDate = $this->request->getGet('sd');
        $endDate = $this->request->getGet('ed');

        if (empty($studentId) || empty($startDate) || empty($endDate)) {
            throw PageNotFoundException::forPageNotFound('Student ID or start/end date is missing');
        }

        $userModel = new UserModel();
        $questionModel = new QuestionModel();
        $questionAnswersModel = new QuestionAnswersModel();

        $student = $userModel->find($studentId);
        
        if (empty($student)) {
            throw PageNotFoundException::forPageNotFound('Student not found');
        }

        helper('student_reports');

        $missingQuestionsResults = getMissingQuestions($studentId, $startDate, $endDate);

        $missingQuestions = [];

        foreach ($missingQuestionsResults as $missingQuestionResult) {
            $missingQuestion = $questionModel->find($missingQuestionResult['question_id']);
            $missingQuestion['incorrect_count'] = $missingQuestionResult['incorrect_count'];
            $missingQuestion['student_answers'] = $missingQuestionResult['student_answers'];
            $missingQuestion['correct_answer'] = $questionAnswersModel->where('question_id', $missingQuestionResult['question_id'])->where('is_correct', 1)->first()['answer'];
            $missingQuestions[] = $missingQuestion;
        }

        // Convert dates in 'M d, Y' format
        $startDate = date('M d, Y', strtotime($startDate));
        $endDate = date('M d, Y', strtotime($endDate));

        return view('reports/student_missing_questions', [
            'student' => $student,
            'missingQuestions' => $missingQuestions,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}