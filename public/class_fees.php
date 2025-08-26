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

// Get active session
$active_session = $db->query("SELECT * FROM sessions WHERE is_active = 1 LIMIT 1")->fetch();
if (!$active_session) {
    $_SESSION['error_message'] = "No active session found. Please set one first.";
}

$selected_class_id = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $active_session) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'CSRF validation failed.';
    }
    $class_id = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);
    $amounts = $_POST['amounts'] ?? [];

    if ($class_id && empty($errors)) {
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("REPLACE INTO class_fees (session_id, class_id, fee_structure_id, amount) VALUES (?, ?, ?, ?)");
            foreach ($amounts as $fee_structure_id => $amount) {
                if (!empty($amount)) {
                    $stmt->execute([
                        $active_session['id'],
                        $class_id,
                        $fee_structure_id,
                        $amount
                    ]);
                }
            }
            $db->commit();
            $_SESSION['success_message'] = 'Fee amounts updated for the selected class.';
            redirect('class_fees.php?class_id=' . $class_id);
        } catch (PDOException $e) {
            $db->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch data for the form
$classes = $db->query("SELECT * FROM classes ORDER BY numeric_level, class_name ASC")->fetchAll();
$fee_structures = $db->query("SELECT * FROM fee_structures ORDER BY fee_name ASC")->fetchAll();
$class_fees = [];

if ($selected_class_id && $active_session) {
    $stmt = $db->prepare("SELECT fee_structure_id, amount FROM class_fees WHERE class_id = ? AND session_id = ?");
    $stmt->execute([$selected_class_id, $active_session['id']]);
    $class_fees = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

$page_title = 'Assign Class Fees';
include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4">Assign Fees to Classes</h1>
    <p>Showing assignments for active session: <strong><?php echo $active_session ? escape($active_session['session_name']) : 'None'; ?></strong></p>

    <!-- Messages -->
    <?php if (isset($_SESSION['success_message'])) { /* ... success message ... */ } ?>
    <?php if (isset($_SESSION['error_message'])) { /* ... error message ... */ } ?>
    <?php if (!empty($errors)) { /* ... errors ... */ } ?>

    <?php if (!$active_session): ?>
        <div class="alert alert-warning">Please <a href="sessions.php">set an active session</a> first.</div>
    <?php else: ?>
        <!-- Class Selection -->
        <div class="card mb-4">
            <div class="card-header">Select a Class</div>
            <div class="card-body">
                <form action="class_fees.php" method="GET">
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

        <!-- Fee Assignment Form -->
        <?php if ($selected_class_id): ?>
        <div class="card">
            <div class="card-header">Set Fee Amounts</div>
            <div class="card-body">
                <form action="class_fees.php?class_id=<?php echo $selected_class_id; ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                    
                    <table class="table">
                        <thead><tr><th>Fee Type</th><th>Amount</th></tr></thead>
                        <tbody>
                            <?php foreach ($fee_structures as $fs): ?>
                            <tr>
                                <td><?php echo escape($fs['fee_name']); ?></td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control" name="amounts[<?php echo $fs['id']; ?>]" value="<?php echo escape($class_fees[$fs['id']] ?? ''); ?>">
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <hr>
                    <button type="submit" class="btn btn-primary">Save Amounts</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
