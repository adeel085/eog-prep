<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClassModel;
use App\Models\ClassTopicLevelModel;
use App\Models\TopicModel;
use App\Models\UserModel;

class AdminClasses extends BaseController
{
    public function index()
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return redirect()->to(base_url('/'));
        }

        $classModel = new ClassModel();

        $classes = $classModel->where('owner_id', $this->user['id'])->orderBy('name', 'ASC')->findAll();
        $topics = $this->getAssignableTopics();
        $assignmentsByClassId = $this->getAssignmentsByClassId(array_column($classes, 'id'));

        foreach ($classes as &$class) {
            $class['assignments'] = $assignmentsByClassId[$class['id']] ?? [];
        }

        return view('admin/classes', [
            'pageTitle' => 'Classes',
            'flashData' => $this->session->getFlashdata(),
            'classes' => $classes,
            'topics' => $topics,
            'user' => $this->user
        ]);
    }

    public function studentsPage($id)
    {
        if (!$this->user) {
            return redirect()->to(base_url('/admin'));
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return redirect()->to(base_url('/'));
        }

        $classModel = new ClassModel();
        $userModel = new UserModel();

        $class = $classModel->find($id);

        if (!$class || $class['owner_id'] != $this->user['id']) {
            return redirect()->to(base_url('/admin/classes'));
        }

        $students = $userModel->filterByStudentOf($this->user['id'])->findAll();
        $filteredStudents = [];

        foreach ($students as &$student) {
            $studentClassId = $userModel->getUserMeta('studentClassId', $student['id'], true);

            if ($studentClassId == $id) {
                $filteredStudents[] = $student;
            }
        }

        return view('admin/classes_students', [
            'pageTitle' => 'Students',
            'flashData' => $this->session->getFlashdata(),
            'class' => $class,
            'students' => $filteredStudents,
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

        $classModel = new ClassModel();

        if ($classModel->where(['name' => $name, 'owner_id' => $this->user['id']])->first()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Class with this name already exists']);
        }

        $classModel->insert(['name' => $name, 'owner_id' => $this->user['id']]);

        $this->session->setFlashdata('status', 'class_created');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Class created successfully']);
    }

    public function delete()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $classId = $this->request->getPost('class_id');

        $classModel = new ClassModel();

        $class = $classModel->find($classId);

        if (!$class || $class['owner_id'] != $this->user['id']) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Class not found']);
        }

        (new ClassTopicLevelModel())->where('class_id', $classId)->delete();
        $classModel->delete($classId);

        $this->session->setFlashdata('status', 'class_deleted');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Class deleted successfully']);
    }

    public function update()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $classId = $this->request->getPost('class_id');
        $name = $this->request->getPost('name');

        $classModel = new ClassModel();

        $class = $classModel->find($classId);

        if (!$class || $class['owner_id'] != $this->user['id']) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Class not found']);
        }
        
        // Get class by name
        $class = $classModel->where(['name' => $name, 'owner_id' => $this->user['id']])->first();

        if ($class && $class['id'] != $classId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Class with this name already exists']);
        }

        $classModel->set(['name' => $name])->update($classId);

        $this->session->setFlashdata('status', 'class_updated');

        return $this->response->setJSON(['status' => 'success', 'message' => 'Class updated successfully']);
    }

    public function assignTopicLevel()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $classId = $this->request->getPost('class_id');
        $topicId = $this->request->getPost('topic_id');
        $level = trim((string) $this->request->getPost('level'));

        if (!$classId || !$topicId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Class and topic are required']);
        }

        $class = $this->getOwnedClass($classId);

        if (!$class) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Class not found']);
        }

        $assignableTopicIds = array_map('intval', array_column($this->getAssignableTopics(), 'id'));

        if (!in_array((int) $topicId, $assignableTopicIds, true)) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Topic not found']);
        }

        $normalizedLevel = null;

        if ($level !== '') {
            if (!in_array($level, ['1', '2', '3'], true)) {
                return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid level selected']);
            }

            $normalizedLevel = (int) $level;
        }

        $classTopicLevelModel = new ClassTopicLevelModel();
        $existingAllLevelsAssignment = $classTopicLevelModel
            ->where('class_id', $classId)
            ->where('topic_id', $topicId)
            ->where('level IS NULL', null, false)
            ->first();

        if ($normalizedLevel === null) {
            $existingTopicAssignments = (new ClassTopicLevelModel())
                ->where('class_id', $classId)
                ->where('topic_id', $topicId)
                ->first();

            if ($existingTopicAssignments) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'This topic already has assignments for the class. Remove them first to allow all levels.'
                ]);
            }
        }
        else {
            if ($existingAllLevelsAssignment) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'This topic is already assigned for all levels.'
                ]);
            }

            $existingLevelAssignment = (new ClassTopicLevelModel())
                ->where('class_id', $classId)
                ->where('topic_id', $topicId)
                ->where('level', $normalizedLevel)
                ->first();

            if ($existingLevelAssignment) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'This topic and level is already assigned to the class.'
                ]);
            }
        }

        $saved = $classTopicLevelModel->insert([
            'class_id' => $classId,
            'topic_id' => $topicId,
            'level' => $normalizedLevel
        ]);

        if (!$saved) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Unable to save assignment']);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Assignment saved successfully']);
    }

    public function removeTopicLevel()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $assignmentId = $this->request->getPost('assignment_id');

        if (!$assignmentId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Assignment is required']);
        }

        $classTopicLevelModel = new ClassTopicLevelModel();
        $assignment = $classTopicLevelModel->find($assignmentId);

        if (!$assignment) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Assignment not found']);
        }

        $class = $this->getOwnedClass($assignment['class_id']);

        if (!$class) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Class not found']);
        }

        $classTopicLevelModel->delete($assignmentId);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Assignment removed successfully']);
    }

    public function sendEmailToParents()
    {
        if (!$this->user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->user['user_type'] != 'admin' && $this->user['user_type'] != 'teacher') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $classId = $this->request->getPost('class_id');
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');

        $classModel = new ClassModel();

        $class = $classModel->find($classId);

        if (!$class || $class['owner_id'] != $this->user['id']) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Class not found']);
        }

        $userModel = new UserModel();

        $students = $userModel->filterByStudentOf($this->user['id'])->findAll();

        foreach ($students as $student) {
            if ($userModel->getUserMeta('studentClassId', $student['id'], true) === $classId) {
                $parentEmails = $userModel->getUserMeta('parentEmails', $student['id'], true);

                if (empty($parentEmails)) {
                    continue;
                }

                helper('student_reports');

                $missingQuestions = getMissingQuestions($student['id'], $startDate, $endDate);

                if (empty($missingQuestions)) {
                    continue;
                }

                $parentEmails = explode(',', $parentEmails);

                foreach ($parentEmails as &$parentEmail) {
                    $parentEmail = trim($parentEmail);
                }

                $this->sendMissingQuestionsEmail($parentEmails, $student, $startDate, $endDate);
            }
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Emails sent successfully']);
    }

    private function getOwnedClass($classId)
    {
        $classModel = new ClassModel();
        $class = $classModel->find($classId);

        if (!$class || $class['owner_id'] != $this->user['id']) {
            return null;
        }

        return $class;
    }

    private function getAssignableTopics()
    {
        $topicModel = new TopicModel();

        if ($this->user['user_type'] == 'admin') {
            return $topicModel->where('owner_id', $this->user['id'])->orderBy('name', 'ASC')->findAll();
        }

        $userModel = new UserModel();
        $adminUser = $userModel->where('user_type', 'admin')->first();

        if ($adminUser) {
            $topicModel
                ->groupStart()
                ->where('owner_id', $adminUser['id'])
                ->orWhere('owner_id', $this->user['id'])
                ->groupEnd();
        }
        else {
            $topicModel->where('owner_id', $this->user['id']);
        }

        return $topicModel->orderBy('name', 'ASC')->findAll();
    }

    private function getAssignmentsByClassId($classIds)
    {
        if (empty($classIds)) {
            return [];
        }

        $classTopicLevelModel = new ClassTopicLevelModel();
        $assignments = $classTopicLevelModel
            ->select('classes_topics_levels.id, classes_topics_levels.class_id, classes_topics_levels.topic_id, classes_topics_levels.level, topics.name AS topic_name')
            ->join('topics', 'topics.id = classes_topics_levels.topic_id')
            ->whereIn('classes_topics_levels.class_id', $classIds)
            ->orderBy('topics.name', 'ASC')
            ->orderBy('classes_topics_levels.level', 'ASC')
            ->findAll();

        $assignmentsByClassId = [];

        foreach ($assignments as $assignment) {
            $assignmentsByClassId[$assignment['class_id']][] = $assignment;
        }

        return $assignmentsByClassId;
    }

    private function sendMissingQuestionsEmail($parentEmails, $student, $startDate, $endDate) {
        
        $subject = "Report for " . $student['full_name'];
        $message = "Please click on the following link to view the questions that <b>" . $student['full_name'] . "</b> needs to work on:<br><br>";

        $questionsUrl = base_url('/report-questions?st=' . $student['id'] . '&sd=' . $startDate . '&ed=' . $endDate);

        $message .= "<a href='" . $questionsUrl . "'>" . $questionsUrl . "</a>";
        
        $email = \Config\Services::email();
        $email->setTo(array_shift($parentEmails));

         // Add remaining emails as CC
        if (!empty($parentEmails)) {
            $email->setCC($parentEmails);
        }

        $email->setCC($student['email']);

        $email->setFrom(env('email.SMTPUser'), 'MyQuickMath');
        $email->setSubject($subject);
        $email->setMessage($message);
        
        return $email->send();
    }
}
