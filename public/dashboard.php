<?php
// Controller for the main dashboard page

$user_role = get_user_role();

if ($user_role === 'Viewer') {
    // --- Viewer Dashboard Logic ---
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT s.*, c.class_name FROM students s LEFT JOIN student_enrollment se ON s.id = se.student_id LEFT JOIN classes c ON se.class_id = c.id WHERE s.user_id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $student_data = $stmt->fetch();

    $page_title = 'Student Dashboard';
    $view_to_load = ROOT_PATH . '/templates/views/dashboard_viewer_view.php';

} else {
    // --- Admin/Staff Dashboard Logic ---
    $page_title = 'Admin Dashboard';
    $view_to_load = ROOT_PATH . '/templates/views/dashboard_view.php';
}

// Load the main dashboard layout, which will in turn load the correct view
require_once ROOT_PATH . '/templates/layouts/dashboard.php';
