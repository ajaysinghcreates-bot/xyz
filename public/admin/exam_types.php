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

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'CSRF validation failed.';
    }

    if (isset($_POST['save'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $name = trim($_POST['name']);

        if (empty($name)) {
            $errors[] = 'Exam type name is required.';
        }

        if (empty($errors)) {
            try {
                if ($id) { // Update
                    $stmt = $db->prepare("UPDATE exam_types SET name = ? WHERE id = ?");
                    $stmt->execute([$name, $id]);
                    $_SESSION['success_message'] = 'Exam type updated!';
                } else { // Insert
                    $stmt = $db->prepare("INSERT INTO exam_types (name) VALUES (?)");
                    $stmt->execute([$name]);
                    $_SESSION['success_message'] = 'Exam type added!';
                }
                redirect('exam_types');
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }

    if (isset($_POST['delete'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            try {
                $stmt = $db->prepare("DELETE FROM exam_types WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success_message'] = 'Exam type deleted!';
                redirect('exam_types');
            } catch (PDOException $e) {
                $errors[] = 'Database error: Could not delete. It might be in use.';
            }
        }
    }
}

// Handle GET for editing
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $db->prepare("SELECT * FROM exam_types WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_item = $stmt->fetch();
    }
}

$items = $db->query("SELECT * FROM exam_types ORDER BY name ASC")->fetchAll();

$page_title = 'Manage Exam Types';
include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4">Manage Exam Types</h1>

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
        <div class="card-header"><?php echo $edit_item ? 'Edit Exam Type' : 'Add New Exam Type'; ?></div>
        <div class="card-body">
            <form action="exam_types" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="id" value="<?php echo $edit_item['id'] ?? ''; ?>">
                <div class="input-group">
                    <input type="text" class="form-control" name="name" placeholder="e.g., Mid-Term, Final Exam" value="<?php echo escape($edit_item['name'] ?? ''); ?>" required>
                    <button type="submit" name="save" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- List of Exam Types -->
    <div class="card">
        <div class="card-header">Existing Exam Types</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>Name</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo escape($item['name']); ?></td>
                        <td>
                            <a href="exam_types?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <form action="exam_types" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
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
