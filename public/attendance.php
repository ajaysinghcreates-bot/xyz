<?php
// Controller for Attendance

$db = Database::getInstance()->getConnection();
$active_session = $db->query("SELECT * FROM sessions WHERE is_active = 1 LIMIT 1")->fetch();

$page_title = 'Take Attendance';
$view_to_load = ROOT_PATH . '/templates/views/attendance_view.php';
require_once ROOT_PATH . '/templates/layouts/dashboard.php';
