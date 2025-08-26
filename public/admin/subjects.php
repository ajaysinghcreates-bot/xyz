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
$edit_subject = null;

// Handle POST requests (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'CSRF validation failed.';
    }

    if (isset($_POST['save_subject'])) {
        $subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT);
        $subject_name = trim($_POST['subject_name']);
        $subject_code = trim($_POST['subject_code']);

        if (empty($subject_name)) {
            $errors[] = 'Subject name is required.';
        }

        if (empty($errors)) {
            try {
                if ($subject_id) { // Update
                    $stmt = $db->prepare("UPDATE subjects SET subject_name = ?, subject_code = ? WHERE id = ?");
                    $stmt->execute([$subject_name, $subject_code, $subject_id]);
                    $_SESSION['success_message'] = 'Subject updated successfully!';
                } else { // Insert
                    $stmt = $db->prepare("INSERT INTO subjects (subject_name, subject_code) VALUES (?, ?)");
                    $stmt->execute([$subject_name, $subject_code]);
                    $_SESSION['success_message'] = 'Subject added successfully!';
                }
                redirect('subjects.php');
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }

    if (isset($_POST['delete_subject'])) {
        $subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT);
        if ($subject_id) {
            try {
                $stmt = $db->prepare("DELETE FROM subjects WHERE id = ?");
                $stmt->execute([$subject_id]);
                $_SESSION['success_message'] = 'Subject deleted successfully!';
                redirect('subjects.php');
            } catch (PDOException $e) {
                $errors[] = 'Database error: Could not delete subject. It might be in use.';
            }
        }
    }
}

// Handle GET request for editing
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $db->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_subject = $stmt->fetch();
    }
}

// Fetch all subjects for display
$subjects = $db->query("SELECT * FROM subjects ORDER BY subject_name ASC")->fetchAll();

$page_title = 'Manage Subjects';
include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4">Manage Subjects</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?php foreach ($errors as $error) echo "<p class='mb-0'>$error</p>"; ?></div>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <div class="card mb-4">
        <div class="card-header"><?php echo $edit_subject ? 'Edit Subject' : 'Add New Subject'; ?></div>
        <div class="card-body">
            <form action="subjects.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="subject_id" value="<?php echo $edit_subject['id'] ?? ''; ?>">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label for="subject_name">Subject Name (e.g., Mathematics, History)</label>
                        <input type="text" class="form-control" name="subject_name" value="<?php echo escape($edit_subject['subject_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="subject_code">Subject Code (e.g., MATH101)</label>
                        <input type="text" class="form-control" name="subject_code" value="<?php echo escape($edit_subject['subject_code'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2 align-self-end mb-3">
                        <button type="submit" name="save_subject" class="btn btn-success w-100">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Subjects List -->
    <div class="card">
        <div class="card-header">Existing Subjects</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>Subject Name</th><th>Subject Code</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td><?php echo escape($subject['subject_name']); ?></td>
                        <td><?php echo escape($subject['subject_code']); ?></td>
                        <td>
                            <a href="subjects.php?edit=<?php echo $subject['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <form action="subjects.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                <button type="submit" name="delete_subject" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
