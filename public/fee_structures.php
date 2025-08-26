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
$edit_item = null;

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'CSRF validation failed.';
    }

    if (isset($_POST['save'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $fee_name = trim($_POST['fee_name']);
        $description = trim($_POST['description']);

        if (empty($fee_name)) {
            $errors[] = 'Fee name is required.';
        }

        if (empty($errors)) {
            try {
                if ($id) { // Update
                    $stmt = $db->prepare("UPDATE fee_structures SET fee_name = ?, description = ? WHERE id = ?");
                    $stmt->execute([$fee_name, $description, $id]);
                    $_SESSION['success_message'] = 'Fee structure updated!';
                } else { // Insert
                    $stmt = $db->prepare("INSERT INTO fee_structures (fee_name, description) VALUES (?, ?)");
                    $stmt->execute([$fee_name, $description]);
                    $_SESSION['success_message'] = 'Fee structure added!';
                }
                redirect('fee_structures.php');
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }

    if (isset($_POST['delete'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            try {
                $stmt = $db->prepare("DELETE FROM fee_structures WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success_message'] = 'Fee structure deleted!';
                redirect('fee_structures.php');
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
        $stmt = $db->prepare("SELECT * FROM fee_structures WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_item = $stmt->fetch();
    }
}

$items = $db->query("SELECT * FROM fee_structures ORDER BY fee_name ASC")->fetchAll();

$page_title = 'Manage Fee Structures';
include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4">Manage Fee Structures</h1>

    <!-- Messages -->
    <?php if (isset($_SESSION['success_message'])) { /* ... success message ... */ } ?>
    <?php if (!empty($errors)) { /* ... errors ... */ } ?>

    <!-- Add/Edit Form -->
    <div class="card mb-4">
        <div class="card-header"><?php echo $edit_item ? 'Edit Fee Structure' : 'Add New Fee Structure'; ?></div>
        <div class="card-body">
            <form action="fee_structures.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="id" value="<?php echo $edit_item['id'] ?? ''; ?>">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label>Fee Name (e.g., Annual Tuition, Bus Fee)</label>
                        <input type="text" class="form-control" name="fee_name" value="<?php echo escape($edit_item['fee_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label>Description</label>
                        <input type="text" class="form-control" name="description" value="<?php echo escape($edit_item['description'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2 align-self-end mb-3">
                        <button type="submit" name="save" class="btn btn-success w-100">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="card">
        <div class="card-header">Existing Fee Structures</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>Fee Name</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo escape($item['fee_name']); ?></td>
                        <td><?php echo escape($item['description']); ?></td>
                        <td>
                            <a href="fee_structures.php?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <form action="fee_structures.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
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
