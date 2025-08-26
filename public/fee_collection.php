<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

if (!is_logged_in() || !in_array(get_user_role(), ['Admin', 'Staff'])) {
    die('Access Denied.');
}

$db = Database::getInstance()->getConnection();
$student_id = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);
$student_details = null;
$fee_records = [];

if ($student_id) {
    $stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student_details = $stmt->fetch();

    $sql = "SELECT sfr.*, fs.fee_name FROM student_fee_records sfr JOIN class_fees cf ON sfr.class_fee_id = cf.id JOIN fee_structures fs ON cf.fee_structure_id = fs.id WHERE sfr.student_id = ? ORDER BY sfr.due_date ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$student_id]);
    $fee_records = $stmt->fetchAll();
}

$page_title = 'Fee Collection';
include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4">Fee Collection</h1>

    <div class="card mb-4">
        <div class="card-header">Find Student</div>
        <div class="card-body">
            <form action="fee_collection.php" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Enter Student ID or Name...">
                    <button class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($student_details): ?>
        <h2>Fee Records for <?php echo escape($student_details['first_name'] . ' ' . $student_details['last_name']); ?></h2>
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Fee Type</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($fee_records as $record): ?>
                            <tr>
                                <td><?php echo escape($record['fee_name']); ?></td>
                                <td><?php echo escape($record['total_amount']); ?></td>
                                <td><?php echo escape($record['amount_paid']); ?></td>
                                <td><?php echo escape($record['total_amount'] - $record['amount_paid']); ?></td>
                                <td><span class="badge bg-info"><?php echo escape($record['status']); ?></span></td>
                                <td>
                                    <?php if ($record['status'] !== 'Paid'): ?>
                                        <button class="btn btn-sm btn-success">Collect Payment</button>
                                    <?php else: ?>
                                        <a href="receipt.php?record_id=<?php echo $record['id']; ?>" class="btn btn-sm btn-secondary">View Receipts</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
