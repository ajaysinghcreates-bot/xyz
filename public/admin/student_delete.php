<?php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

// Protect page and check roles
if (!is_logged_in() || get_user_role() !== 'Admin') {
    // Only Admins can delete. Staff might have this permission removed.
    // For now, we are strict.
    die('Access Denied. You do not have permission to perform this action.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'CSRF token validation failed.';
        redirect('students.php');
    }

    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

    if ($student_id) {
        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            // First, get the photo path to delete the file
            $stmt = $db->prepare("SELECT photo_path FROM students WHERE id = :id");
            $stmt->execute([':id' => $student_id]);
            $student = $stmt->fetch();

            // Delete the student record
            $delete_stmt = $db->prepare("DELETE FROM students WHERE id = :id");
            $delete_stmt->execute([':id' => $student_id]);

            // If deletion is successful, commit and then delete the file
            if ($delete_stmt->rowCount() > 0) {
                $db->commit();

                if ($student && !empty($student['photo_path'])) {
                    $photo_file = ROOT_PATH . '/public/uploads/photos/' . $student['photo_path'];
                    if (file_exists($photo_file)) {
                        unlink($photo_file);
                    }
                }

                log_audit('STUDENT_DELETE', 'Deleted student ID: ' . $student_id);
                $_SESSION['success_message'] = 'Student record has been deleted successfully.';
            } else {
                $db->rollBack();
                $_SESSION['error_message'] = 'Could not delete student record. It may have already been deleted.';
            }

        } catch (PDOException $e) {
            $db->rollBack();
            // In a real app, you would log this error
            $_SESSION['error_message'] = 'Database error during deletion: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = 'Invalid student ID.';
    }
}

redirect('students.php');
