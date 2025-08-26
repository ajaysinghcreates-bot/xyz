<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

if (!is_logged_in() || !in_array(get_user_role(), ['Admin', 'Staff'])) {
    die('Access Denied.');
}

$db = Database::getInstance()->getConnection();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_expense'])) {
    // ... POST handling logic for adding/editing expenses ...
    $category = trim($_POST['expense_category']);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $expense_date = $_POST['expense_date'];
    
    if(empty($category) || empty($amount) || empty($expense_date)) {
        $errors[] = "Category, Amount, and Date are required.";
    }

    $description = trim($_POST['description']);
    $vendor = trim($_POST['vendor']);

    if(empty($errors)) {
        try {
            $sql = "INSERT INTO expenses (expense_category, amount, expense_date, description, vendor, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $category, $amount, $expense_date, 
                $description, 
                $vendor, 
                $_SESSION['user_id']
            ]);
            $_SESSION['success_message'] = 'Expense recorded successfully!';
            redirect('expenses.php');
        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}

$expenses = $db->query("SELECT * FROM expenses ORDER BY expense_date DESC")->fetchAll();

$page_title = 'Track Expenses';
include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4">Expense Tracking</h1>

    <!-- ... messages ... -->

    <div class="card mb-4">
        <div class="card-header">Record New Expense</div>
        <div class="card-body">
            <form action="expenses.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Category</label>
                        <input type="text" class="form-control" name="expense_category" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Date</label>
                        <input type="date" class="form-control" name="expense_date" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Description</label>
                        <textarea class="form-control" name="description"></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Vendor (Optional)</label>
                        <input type="text" class="form-control" name="vendor">
                    </div>
                </div>
                <button type="submit" name="save_expense" class="btn btn-primary">Save Expense</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Recorded Expenses</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>Date</th><th>Category</th><th>Amount</th><th>Description</th><th>Vendor</th></tr></thead>
                <tbody>
                    <?php foreach($expenses as $expense): ?>
                    <tr>
                        <td><?php echo escape($expense['expense_date']); ?></td>
                        <td><?php echo escape($expense['expense_category']); ?></td>
                        <td><?php echo escape($expense['amount']); ?></td>
                        <td><?php echo escape($expense['description']); ?></td>
                        <td><?php echo escape($expense['vendor']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
