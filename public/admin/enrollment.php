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

$active_session = $db->query("SELECT * FROM sessions WHERE is_active = 1 LIMIT 1")->fetch();
$selected_class_id = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $active_session) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'CSRF validation failed.';
    }
    $class_id = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);
    $student_ids = $_POST['student_ids'] ?? [];

    if ($class_id && empty($errors)) {
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("INSERT IGNORE INTO student_enrollment (student_id, class_id, session_id, enrollment_date) VALUES (?, ?, ?, NOW())");
            foreach ($student_ids as $student_id) {
                $stmt->execute([$student_id, $class_id, $active_session['id']]);
            }
            $db->commit();
            $_SESSION['success_message'] = 'Students enrolled successfully!';
            redirect('enrollment.php?class_id=' . $class_id);
        } catch (PDOException $e) {
            $db->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

$classes = $db->query("SELECT * FROM classes ORDER BY numeric_level ASC")->fetchAll();
$students_not_enrolled = $db->query("SELECT id, first_name, last_name, admission_id FROM students ORDER BY first_name ASC")->fetchAll();
$enrolled_students = [];

if ($selected_class_id && $active_session) {
    $stmt = $db->prepare("SELECT s.id, s.first_name, s.last_name, s.admission_id FROM students s JOIN student_enrollment se ON s.id = se.student_id WHERE se.class_id = ? AND se.session_id = ?");
    $stmt->execute([$selected_class_id, $active_session['id']]);
    $enrolled_students = $stmt->fetchAll();
}

$page_title = 'Student Enrollment';
include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4">Student Enrollment</h1>
    <p>Enrolling for active session: <strong><?php echo $active_session ? escape($active_session['session_name']) : 'None'; ?></strong></p>

    <!-- ... messages ... -->

    <?php if ($active_session): ?>
        <div class="card mb-4">
            <div class="card-header">Select Class</div>
            <div class="card-body">
                <form action="enrollment.php" method="GET">
                    <select class="form-select" name="class_id" onchange="this.form.submit()">
                        <option value="">-- Select a Class --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo ($selected_class_id == $class['id']) ? 'selected' : ''; ?>><?php echo escape($class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <?php if ($selected_class_id): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Available Students</div>
                        <div class="card-body">
                            <form action="enrollment.php?class_id=<?php echo $selected_class_id; ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                                <div style="height: 400px; overflow-y: auto;">
                                    <?php foreach ($students_not_enrolled as $student): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>" id="student-<?php echo $student['id']; ?>">
                                            <label class="form-check-label" for="student-<?php echo $student['id']; ?>"><?php echo escape($student['first_name'] . ' ' . $student['last_name']); ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Enroll Selected</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Students in this Class</div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php foreach ($enrolled_students as $student): ?>
                                    <li class="list-group-item"><?php echo escape($student['first_name'] . ' ' . $student['last_name']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-warning">Set an active session to manage enrollment.</div>
    <?php endif; ?>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
