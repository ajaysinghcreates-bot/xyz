<?php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

if (!is_logged_in() || get_user_role() !== 'Admin') {
    die('Access Denied.');
}

$db = Database::getInstance()->getConnection();
$errors = [];

// Get active session
$active_session_stmt = $db->query("SELECT * FROM sessions WHERE is_active = 1 LIMIT 1");
$active_session = $active_session_stmt->fetch();

if (!$active_session) {
    $_SESSION['error_message'] = "No active session found. Please set an active session first.";
    // redirect to sessions.php or show error
}

$selected_class_id = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $active_session) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'CSRF validation failed.';
    }
    $class_id = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);
    $assignments = $_POST['assignments'] ?? [];

    if ($class_id && empty($errors)) {
        try {
            $db->beginTransaction();
            // 1. Delete existing assignments for this class in this session
            $sql_delete = "DELETE ta FROM teacher_assignments ta JOIN class_subjects cs ON ta.class_subject_id = cs.id WHERE cs.class_id = ? AND ta.session_id = ?";
            $stmt_delete = $db->prepare($sql_delete);
            $stmt_delete->execute([$class_id, $active_session['id']]);

            // 2. Insert new assignments
            $insert_stmt = $db->prepare("INSERT INTO teacher_assignments (user_id, class_subject_id, session_id) VALUES (?, ?, ?)");
            foreach ($assignments as $class_subject_id => $user_id) {
                if (!empty($user_id)) { // Only insert if a teacher was selected
                    $insert_stmt->execute([$user_id, $class_subject_id, $active_session['id']]);
                }
            }
            $db->commit();
            $_SESSION['success_message'] = 'Teacher assignments have been updated for the selected class.';
            redirect('teacher_assignment.php?class_id=' . $class_id);
        } catch (PDOException $e) {
            $db->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch data for the form
$classes = $db->query("SELECT * FROM classes ORDER BY numeric_level, class_name ASC")->fetchAll();
$teachers = $db->query("SELECT id, first_name, last_name FROM users WHERE role_id = 2 ORDER BY first_name ASC")->fetchAll(); // Role 2 = Staff

$class_subjects_with_teachers = [];
if ($selected_class_id && $active_session) {
    $sql = "SELECT cs.id as class_subject_id, s.subject_name, ta.user_id as assigned_teacher_id
            FROM class_subjects cs
            JOIN subjects s ON cs.subject_id = s.id
            LEFT JOIN teacher_assignments ta ON cs.id = ta.class_subject_id AND ta.session_id = ?
            WHERE cs.class_id = ?
            ORDER BY s.subject_name ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$active_session['id'], $selected_class_id]);
    $class_subjects_with_teachers = $stmt->fetchAll();
}

$page_title = 'Assign Teachers';
include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4">Assign Teachers to Subjects</h1>
    <p>Showing assignments for active session: <strong><?php echo $active_session ? escape($active_session['session_name']) : 'None'; ?></strong></p>

    <?php if (isset($_SESSION['success_message'])) { /* ... success message ... */ } ?>
    <?php if (isset($_SESSION['error_message'])) { /* ... error message ... */ } ?>
    <?php if (!empty($errors)) { /* ... errors ... */ } ?>

    <?php if (!$active_session): ?>
        <div class="alert alert-warning">Please <a href="sessions.php">set an active session</a> before assigning teachers.</div>
    <?php else: ?>
        <!-- Class Selection -->
        <div class="card mb-4">
            <div class="card-header">Select a Class</div>
            <div class="card-body">
                <form action="teacher_assignment.php" method="GET">
                    <div class="input-group">
                        <select name="class_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Select a Class --</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo ($selected_class_id == $class['id']) ? 'selected' : ''; ?>>
                                    <?php echo escape($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Teacher Assignment Form -->
        <?php if ($selected_class_id): ?>
        <div class="card">
            <div class="card-header">Assign Teachers for Class: <?php
                $class_name_stmt = $db->prepare("SELECT class_name FROM classes WHERE id = ?");
                $class_name_stmt->execute([$selected_class_id]);
                echo escape($class_name_stmt->fetchColumn());
            ?></div>
            <div class="card-body">
                <form action="teacher_assignment.php?class_id=<?php echo $selected_class_id; ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                    
                    <table class="table">
                        <thead><tr><th>Subject</th><th>Assigned Teacher</th></tr></thead>
                        <tbody>
                            <?php foreach ($class_subjects_with_teachers as $item): ?>
                            <tr>
                                <td><?php echo escape($item['subject_name']); ?></td>
                                <td>
                                    <select name="assignments[<?php echo $item['class_subject_id']; ?>]" class="form-select">
                                        <option value="">-- Unassigned --</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?php echo $teacher['id']; ?>" <?php echo ($item['assigned_teacher_id'] == $teacher['id']) ? 'selected' : ''; ?>>
                                                <?php echo escape($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <hr>
                    <button type="submit" class="btn btn-primary">Save Assignments</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
