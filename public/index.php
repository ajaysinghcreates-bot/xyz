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


// 4. Define Publicly Accessible Routes
// This whitelist prevents arbitrary file inclusion
$public_routes = [
    '' => 'home.php',
    'home' => 'home.php',
    'login' => 'login.php',
    'logout' => 'logout.php',
    'secure_admin_register' => 'admin/secure_admin_register.php'
];

$admin_routes = [
    'admin/dashboard' => 'admin/dashboard.php',
    'admin/students' => 'admin/students.php',
    'admin/student_form' => 'admin/student_form.php',
    'admin/student_delete' => 'admin/student_delete.php',
    'admin/classes' => 'admin/classes.php',
    'admin/subjects' => 'admin/subjects.php',
    'admin/sessions' => 'admin/sessions.php',
    'admin/class_subject_assignment' => 'admin/class_subject_assignment.php',
    'admin/teacher_assignment' => 'admin/teacher_assignment.php',
    'admin/fee_structures' => 'admin/fee_structures.php',
    'admin/class_fees' => 'admin/class_fees.php',
    'admin/enrollment' => 'admin/enrollment.php',
    'admin/fee_collection' => 'admin/fee_collection.php',
    'admin/expenses' => 'admin/expenses.php',
    'admin/exam_types' => 'admin/exam_types.php',
    'admin/exams' => 'admin/exams.php',
    'admin/marks_entry' => 'admin/marks_entry.php'
];

$student_routes = [
    'student/dashboard' => 'student/dashboard.php',
    'student/attendance' => 'student/attendance.php'
];

// 5. Route Handling
if (array_key_exists($request, $public_routes)) {
    require_once ROOT_PATH . '/public/' . $public_routes[$request];
} elseif (array_key_exists($request, $admin_routes)) {
    // For admin routes, ensure user is logged in and is an admin
    if (!is_logged_in() || !is_admin()) {
        redirect($base_path . '/login');
    }
    require_once ROOT_PATH . '/public/' . $admin_routes[$request];
} elseif (array_key_exists($request, $student_routes)) {
    // For student routes, ensure user is logged in
    if (!is_logged_in()) {
        redirect($base_path . '/login');
    }
    require_once ROOT_PATH . '/public/' . $student_routes[$request];
} else {
    // 404 Not Found
    require_once ROOT_PATH . '/templates/layouts/404.php';
}


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