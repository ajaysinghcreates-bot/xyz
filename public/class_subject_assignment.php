<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

if (!is_logged_in() || get_user_role() !== 'Admin') {
    die('Access Denied.');
}

$db = Database::getInstance()->getConnection();
$errors = [];
$selected_class_id = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'CSRF validation failed.';
    }
    $class_id = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);
    $assigned_subjects = $_POST['subjects'] ?? [];

    if ($class_id && empty($errors)) {
        try {
            $db->beginTransaction();
            // 1. Delete existing assignments for this class
            $stmt = $db->prepare("DELETE FROM class_subjects WHERE class_id = ?");
            $stmt->execute([$class_id]);

            // 2. Insert new assignments
            if (!empty($assigned_subjects)) {
                $insert_stmt = $db->prepare("INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)");
                foreach ($assigned_subjects as $subject_id) {
                    $insert_stmt->execute([$class_id, $subject_id]);
                }
            }
            $db->commit();
            $_SESSION['success_message'] = 'Subject assignments updated successfully for the selected class.';
            redirect('class_subject_assignment.php?class_id=' . $class_id);
        } catch (PDOException $e) {
            $db->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch data for the form
$classes = $db->query("SELECT * FROM classes ORDER BY numeric_level, class_name ASC")->fetchAll();
$subjects = $db->query("SELECT * FROM subjects ORDER BY subject_name ASC")->fetchAll();
$assigned_subject_ids = [];

if ($selected_class_id) {
    $stmt = $db->prepare("SELECT subject_id FROM class_subjects WHERE class_id = ?");
    $stmt->execute([$selected_class_id]);
    $assigned_subject_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$page_title = 'Assign Subjects to Class';
include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4">Assign Subjects to Class</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?php foreach ($errors as $error) echo "<p class='mb-0'>$error</p>"; ?></div>
    <?php endif; ?>

    <!-- Class Selection Form -->
    <div class="card mb-4">
        <div class="card-header">Select a Class</div>
        <div class="card-body">
            <form action="class_subject_assignment.php" method="GET">
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

    <!-- Subject Assignment Form -->
    <?php if ($selected_class_id): ?>
    <div class="card">
        <div class="card-header">Assign Subjects</div>
        <div class="card-body">
            <form action="class_subject_assignment.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                
                <div class="row">
                    <?php foreach ($subjects as $subject): ?>
                        <div class="col-md-4">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="subjects[]" value="<?php echo $subject['id']; ?>" id="subject-<?php echo $subject['id']; ?>" <?php echo in_array($subject['id'], $assigned_subject_ids) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="subject-<?php echo $subject['id']; ?>">
                                    <?php echo escape($subject['subject_name']); ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr>
                <button type="submit" class="btn btn-primary">Save Assignments</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
