<?php
header('Content-Type: application/json');

// This is a simplified bootstrap. In a real app, this would be more robust.
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

// Basic security check
if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access Denied']);
    exit;
}

$db = Database::getInstance()->getConnection();
$response = [];

try {
    // Widget Data: Total Students
    $response['total_students'] = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();

    // Widget Data: Total Staff
    $response['total_staff'] = $db->query("SELECT COUNT(*) FROM users WHERE role_id = 2")->fetchColumn(); // Role 2 = Staff

    // Widget Data: Fees Collected This Month
    $response['fees_this_month'] = $db->query("SELECT SUM(amount_paid) FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())")->fetchColumn() ?? 0;

    // Chart Data: Income vs Expense for last 6 months
    $income_expense_data = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $month_label = date('M Y', strtotime("-$i months"));

        $income = $db->query("SELECT SUM(amount_paid) FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$month'")->fetchColumn() ?? 0;
        $expense = $db->query("SELECT SUM(amount) FROM expenses WHERE DATE_FORMAT(expense_date, '%Y-%m') = '$month'")->fetchColumn() ?? 0;
        
        $income_expense_data['labels'][] = $month_label;
        $income_expense_data['income'][] = $income;
        $income_expense_data['expense'][] = $expense;
    }
    $response['income_expense_chart'] = $income_expense_data;

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
