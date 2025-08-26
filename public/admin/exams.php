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
$edit_item = null;

// POST handling for Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (CSRF check, etc.)
    if (isset($_POST['save'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $name = trim($_POST['name']);
        $session_id = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
        $exam_type_id = filter_input(INPUT_POST, 'exam_type_id', FILTER_VALIDATE_INT);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        if (empty($name) || !$session_id || !$exam_type_id || empty($start_date)) {
            $errors[] = 'All fields are required.';
        }

        if (empty($errors)) {
            try {
                if ($id) { // Update
                    $stmt = $db->prepare("UPDATE exams SET name=?, session_id=?, exam_type_id=?, start_date=?, end_date=? WHERE id=?");
                    $stmt->execute([$name, $session_id, $exam_type_id, $start_date, $end_date, $id]);
                } else { // Insert
                    $stmt = $db->prepare("INSERT INTO exams (name, session_id, exam_type_id, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $session_id, $exam_type_id, $start_date, $end_date]);
                }
                $_SESSION['success_message'] = 'Exam saved successfully!';
                redirect('exams');
            } catch (PDOException $e) {
                $errors[] = 'Database Error: ' . $e->getMessage();
            }
        }
    }
    // ... (Delete logic here)
}

// GET handling for editing
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $db->prepare("SELECT * FROM exams WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_item = $stmt->fetch();
    }
}

// Data for forms and lists
$sessions = $db->query("SELECT * FROM sessions ORDER BY is_active DESC, start_date DESC")->fetchAll();
$exam_types = $db->query("SELECT * FROM exam_types ORDER BY name ASC")->fetchAll();
$exams = $db->query("SELECT e.*, s.session_name, et.name as type_name FROM exams e JOIN sessions s ON e.session_id = s.id JOIN exam_types et ON e.exam_type_id = et.id ORDER BY e.start_date DESC")->fetchAll();

$page_title = 'Manage Exams';
include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4">Manage Exams</h1>
    <!-- ... (Error/Success messages) ... -->

    <div class="card mb-4">
        <div class="card-header"><?php echo $edit_item ? 'Edit Exam' : 'Create New Exam'; ?></div>
        <div class="card-body">
            <form action="exams" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="id" value="<?php echo $edit_item['id'] ?? ''; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Exam Name</label>
                        <input type="text" class="form-control" name="name" value="<?php echo escape($edit_item['name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Session</label>
                        <select name="session_id" class="form-select" required>
                            <?php foreach($sessions as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php if($edit_item && $edit_item['session_id']==$s['id']) echo 'selected'; elseif($s['is_active']) echo 'selected'; ?>><?php echo escape($s['session_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Exam Type</label>
                        <select name="exam_type_id" class="form-select" required>
                            <?php foreach($exam_types as $et): ?>
                                <option value="<?php echo $et['id']; ?>" <?php if($edit_item && $edit_item['exam_type_id']==$et['id']) echo 'selected'; ?>><?php echo escape($et['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo escape($edit_item['start_date'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>End Date</label>
                        <input type="date" class="form-control" name="end_date" value="<?php echo escape($edit_item['end_date'] ?? ''); ?>">
                    </div>
                </div>
                <button type="submit" name="save" class="btn btn-primary">Save Exam</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Scheduled Exams</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>Name</th><th>Session</th><th>Type</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($exams as $exam): ?>
                    <tr>
                        <td><?php echo escape($exam['name']); ?></td>
                        <td><?php echo escape($exam['session_name']); ?></td>
                        <td><?php echo escape($exam['type_name']); ?></td>
                        <td><?php echo escape($exam['start_date']); ?></td>
                        <td>
                            <a href="exam_schedule?exam_id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-info">Schedule</a>
                            <a href="exams?edit=<?php echo $exam['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
