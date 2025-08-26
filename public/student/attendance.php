<?php
// Controller for Attendance

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

$db = Database::getInstance()->getConnection();
$active_session = $db->query("SELECT * FROM sessions WHERE is_active = 1 LIMIT 1")->fetch();

$page_title = 'Take Attendance';
$view_to_load = ROOT_PATH . '/templates/views/student/attendance_view.php';
require_once ROOT_PATH . '/templates/layouts/dashboard.php';
