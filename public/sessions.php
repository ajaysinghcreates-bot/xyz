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

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'CSRF validation failed.';
    }

    // --- ADD/UPDATE SESSION ---
    if (isset($_POST['save_session'])) {
        $session_name = trim($_POST['session_name']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        if (empty($session_name) || empty($start_date) || empty($end_date)) {
            $errors[] = 'All fields are required.';
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare("INSERT INTO sessions (session_name, start_date, end_date) VALUES (?, ?, ?)");
                $stmt->execute([$session_name, $start_date, $end_date]);
                $_SESSION['success_message'] = 'New session created successfully!';
                redirect('sessions.php');
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }

    // --- SET ACTIVE SESSION ---
    if (isset($_POST['set_active'])) {
        $session_id = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
        if ($session_id) {
            try {
                $db->beginTransaction();
                // First, deactivate all other sessions
                $db->query("UPDATE sessions SET is_active = 0");
                // Then, activate the selected one
                $stmt = $db->prepare("UPDATE sessions SET is_active = 1 WHERE id = ?");
                $stmt->execute([$session_id]);
                $db->commit();
                $_SESSION['success_message'] = 'Active session has been set.';
                redirect('sessions.php');
            } catch (PDOException $e) {
                $db->rollBack();
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Fetch all sessions
$sessions = $db->query("SELECT * FROM sessions ORDER BY start_date DESC")->fetchAll();

$page_title = 'Manage Academic Sessions';
include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4">Manage Academic Sessions</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?php foreach ($errors as $error) echo "<p class='mb-0'>$error</p>"; ?></div>
    <?php endif; ?>

    <!-- Add Session Form -->
    <div class="card mb-4">
        <div class="card-header">Add New Session</div>
        <div class="card-body">
            <form action="sessions.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Session Name (e.g., 2024-2025)</label>
                        <input type="text" class="form-control" name="session_name" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Start Date</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>End Date</label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                    <div class="col-md-2 align-self-end mb-3">
                        <button type="submit" name="save_session" class="btn btn-primary w-100">Create</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sessions List -->
    <div class="card">
        <div class="card-header">Existing Sessions</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>Session Name</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                    <tr class="<?php echo $session['is_active'] ? 'table-success' : ''; ?>">
                        <td><?php echo escape($session['session_name']); ?></td>
                        <td><?php echo escape($session['start_date']); ?></td>
                        <td><?php echo escape($session['end_date']); ?></td>
                        <td><?php echo $session['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
                        <td>
                            <?php if (!$session['is_active']): ?>
                            <form action="sessions.php" method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                <button type="submit" name="set_active" class="btn btn-sm btn-info">Set Active</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
