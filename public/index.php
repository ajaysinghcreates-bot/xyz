<?php

// --- School Management System - Front Controller ---

// 1. Define Root Path
define('ROOT_PATH', dirname(__DIR__));

// 2. Load Configuration & Core Functions
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

// 3. Basic Routing
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$request = str_replace($base_path, '', $request_uri);
$request = trim(parse_url($request, PHP_URL_PATH), '/');

// 4. Define Publicly Accessible Routes
// This whitelist prevents arbitrary file inclusion
$public_routes = [
    '' => 'home.php',
    'home' => 'home.php',
    'login' => 'login.php',
    'logout' => 'logout.php',
    'secure_admin_register' => 'secure_admin_register.php'
];

$protected_routes = [
    'dashboard' => 'dashboard.php',
    'students' => 'students.php',
    'student_form' => 'student_form.php',
    'student_delete' => 'student_delete.php',
    'classes' => 'classes.php',
    'subjects' => 'subjects.php',
    'sessions' => 'sessions.php',
    'class_subject_assignment' => 'class_subject_assignment.php',
    'teacher_assignment' => 'teacher_assignment.php',
    'fee_structures' => 'fee_structures.php',
    'class_fees' => 'class_fees.php',
    'enrollment' => 'enrollment.php',
    'fee_collection' => 'fee_collection.php',
    'expenses' => 'expenses.php',
    'exam_types' => 'exam_types.php',
    'exams' => 'exams.php',
    'marks_entry' => 'marks_entry.php'
];

// 5. Route Handling
if (array_key_exists($request, $public_routes)) {
    require_once ROOT_PATH . '/public/' . $public_routes[$request];
} elseif (array_key_exists($request, $protected_routes)) {
    // For protected routes, ensure user is logged in
    if (!is_logged_in()) {
        redirect($base_path . '/login');
    }
    require_once ROOT_PATH . '/public/' . $protected_routes[$request];
} else {
    // 404 Not Found
    require_once ROOT_PATH . '/templates/layouts/404.php';
}