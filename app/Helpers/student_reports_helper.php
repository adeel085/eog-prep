<?php

use App\Models\StudentQuestionsResultsModel;

function getMissingQuestions($studentId, $startDate, $endDate) {
    $studentQuestionsResultsModel = new StudentQuestionsResultsModel();

    $startDateTime = $startDate . " 00:00:00";
    $endDateTime = $endDate . " 23:59:59";

    $missingQuestions = $studentQuestionsResultsModel->select('question_id, COUNT(*) AS incorrect_count')
        ->where('student_id', $studentId)
        ->where('is_correct', 0)
        ->where('created_at >=', $startDateTime)
        ->where('created_at <=', $endDateTime)
        ->groupBy('question_id')
        ->findAll();
    
    foreach ($missingQuestions as &$missingQuestion) {
        $answers = $studentQuestionsResultsModel->select('student_answer, created_at')
            ->where('student_id', $studentId)
            ->where('question_id', $missingQuestion['question_id'])
            ->where('is_correct', 0)
            ->where('created_at >=', $startDateTime)
            ->where('created_at <=', $endDateTime)
            ->findAll();

        // Fetch student_answers and created_at
        $missingQuestion['student_answers'] = [];
        foreach ($answers as $answer) {
            $missingQuestion['student_answers'][] = [
                'student_answer' => $answer['student_answer'],
                'created_at' => $answer['created_at']
            ];
        }
    }

    return $missingQuestions;
}