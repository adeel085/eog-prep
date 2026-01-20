<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Index::index');
$routes->get('/login', 'Login::index');
$routes->get('/admin/teacher-registration', 'AdminLogin::teacherRegistrationPage');
$routes->get('/signup', 'Signup::index');
$routes->get('/home', 'Home::index');
$routes->get('/admin', 'AdminLogin::index');
$routes->get('/admin/dashboard', 'AdminDashboard::index');
$routes->get('/logout', 'Logout::index');
$routes->get('/admin/students', 'AdminStudents::index');
$routes->get('/admin/students/new', 'AdminStudents::newPage');
$routes->get('/admin/students/edit/(:any)', 'AdminStudents::editPage/$1');
$routes->get('/admin/students/reports/(:any)', 'AdminStudents::reportsPage/$1');

$routes->get('/admin/teachers', 'AdminTeachers::index');
$routes->get('/admin/teachers/new', 'AdminTeachers::newPage');
$routes->get('/admin/teachers/edit/(:any)', 'AdminTeachers::editPage/$1');

$routes->get('/admin/classes', 'AdminClasses::index');
$routes->get('/admin/classes/(:any)/students', 'AdminClasses::studentsPage/$1');

$routes->get('/admin/topics', 'AdminTopics::index');
$routes->get('/admin/topics/new', 'AdminTopics::newPage');
$routes->get('/admin/topics/edit/(:any)', 'AdminTopics::editPage/$1');
$routes->get('/admin/topics/(:any)/questions', 'AdminTopics::questionsPage/$1');

$routes->get('/admin/questions', 'AdminQuestions::index');
$routes->get('/admin/questions/new', 'AdminQuestions::newPage');
$routes->get('/admin/questions/edit/(:any)', 'AdminQuestions::editPage/$1');

$routes->get('/admin/timer', 'AdminTimer::index');
$routes->get('/admin/goals', 'AdminGoals::index');

$routes->get('/admin/questions-basket', 'AdminQuestions::basketPage');

$routes->get('/evaluation', 'Home::evaluationPage');

$routes->get('/report-questions', 'Reports::reportMissingQuestions');

$routes->get('/page-selection', 'Home::pageSelection');

$routes->get('/progress', 'Home::progressPage');
$routes->get('/history', 'Home::historyPage');
$routes->get('/admin/preferences', 'AdminPreferences::index');

$routes->get('/cron/weekly-points-reminder-email', 'CronController::weeklyPointsReminderEmail');
$routes->get('/cron/send-monday-missing-questions-report-to-parents', 'CronController::sendMondayMissingQuestionsReportToParents');

$routes->post('/admin/login', 'AdminLogin::login');
$routes->post('/admin/students/saveNew', 'AdminStudents::saveNew');
$routes->post('/admin/students/update', 'AdminStudents::update');
$routes->post('/admin/students/delete', 'AdminStudents::delete');
$routes->post('/admin/students/resetPoints', 'AdminStudents::resetPoints');
$routes->post('/admin/students/send-missed-questions-email', 'AdminStudents::sendMissedQuestionsEmail');
$routes->post('/admin/students/import-csv', 'AdminStudents::importCsv');
$routes->post('/admin/topics/saveNew', 'AdminTopics::saveNew');
$routes->post('/admin/topics/update', 'AdminTopics::update');
$routes->post('/admin/topics/delete', 'AdminTopics::delete');
$routes->post('/admin/questions/saveNew', 'AdminQuestions::saveNew');
$routes->post('/admin/questions/update', 'AdminQuestions::update');
$routes->post('/admin/questions/delete', 'AdminQuestions::delete');
$routes->post('/admin/questions/import-csv', 'AdminQuestions::importCsv');
$routes->post('/admin/topics/remove-question', 'AdminTopics::removeQuestion');
$routes->post('/admin/questions/updateTopics', 'AdminQuestions::updateTopics');
$routes->post('/admin/questions/get-details', 'AdminQuestions::getQuestionDetails');
$routes->post('/submit-answer', 'Home::submitAnswer');
$routes->post('/admin/classes/saveNew', 'AdminClasses::saveNew');
$routes->post('/admin/classes/delete', 'AdminClasses::delete');
$routes->post('/admin/classes/update', 'AdminClasses::update');
$routes->post('/admin/dashboard/viewReport', 'AdminDashboard::viewReport');
$routes->post('/get-question', 'Home::getQuestion');
$routes->post('/class/send-email', 'AdminClasses::sendEmailToParents');
$routes->post('/admin/timer/save', 'AdminTimer::save');
$routes->post('/admin/topics/create-from-wizard', 'AdminTopics::createTopicFromWizard');
$routes->post('/admin/goals/fetchWeeklyGoals', 'AdminGoals::fetchWeeklyGoals');
$routes->post('/admin/goals/addWeeklyGoal', 'AdminGoals::addWeeklyGoal');
$routes->post('/admin/goals/deleteWeeklyGoal', 'AdminGoals::deleteWeeklyGoal');
$routes->post('/home/getHistory', 'Home::getHistory');
$routes->post('/admin/students/clear-last-year-data', 'AdminStudents::clearLastYearData');

$routes->post('/admin/teachers/saveNew', 'AdminTeachers::saveNew');
$routes->post('/admin/teachers/update', 'AdminTeachers::update');
$routes->post('/admin/teachers/delete', 'AdminTeachers::delete');
$routes->post('/admin/teachers/register', 'AdminTeachers::register');

$routes->post('/login', 'Login::login');
$routes->post('/signup-user', 'Signup::signupUser');

$routes->post('/admin/preferences/save', 'AdminPreferences::save');