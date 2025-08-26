<?php
// Controller for Manage Classes

$db = Database::getInstance()->getConnection();
$errors = [];

// Handle AJAX POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An error occurred.'];

    if (!validate_csrf_token($_POST['csrf_token'])) {
        $response['message'] = 'CSRF validation failed.';
        echo json_encode($response);
        exit;
    }

    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    try {
        if ($action === 'save') {
            $class_name = trim($_POST['class_name']);
            $numeric_level = filter_input(INPUT_POST, 'numeric_level', FILTER_VALIDATE_INT);
            if (empty($class_name)) {
                throw new Exception('Class name is required.');
            }

            if ($id) { // Update
                $stmt = $db->prepare("UPDATE classes SET class_name = ?, numeric_level = ? WHERE id = ?");
                $stmt->execute([$class_name, $numeric_level, $id]);
                $response['message'] = 'Class updated successfully!';
            } else { // Insert
                $stmt = $db->prepare("INSERT INTO classes (class_name, numeric_level) VALUES (?, ?)");
                $stmt->execute([$class_name, $numeric_level]);
                $response['message'] = 'Class added successfully!';
            }
            $response['success'] = true;
        } elseif ($action === 'delete') {
            if ($id) {
                $stmt = $db->prepare("DELETE FROM classes WHERE id = ?");
                $stmt->execute([$id]);
                $response['success'] = true;
                $response['message'] = 'Class deleted successfully!';
            }
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit; // Terminate script after AJAX response
}

// Standard page load logic
$edit_class = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $db->prepare("SELECT * FROM classes WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_class = $stmt->fetch();
    }
}
$classes = $db->query("SELECT * FROM classes ORDER BY numeric_level, class_name ASC")->fetchAll();

$page_title = 'Manage Classes';
$view_to_load = ROOT_PATH . '/templates/views/admin/classes_view.php';
require_once ROOT_PATH . '/templates/layouts/dashboard.php';