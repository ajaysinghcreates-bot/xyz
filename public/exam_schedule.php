<?php
// Controller for Exam Schedule

$db = Database::getInstance()->getConnection();
$exam_id = filter_input(INPUT_GET, 'exam_id', FILTER_VALIDATE_INT);

if(!$exam_id) die('Invalid Exam ID');

$exam = $db->query("SELECT * FROM exams WHERE id = $exam_id")->fetch();

$page_title = 'Schedule for ' . escape($exam['name']);
$view_to_load = ROOT_PATH . '/templates/views/exam_schedule_view.php';
require_once ROOT_PATH . '/templates/layouts/dashboard.php';
