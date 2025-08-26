<?php
header('Content-Type: application/json');

define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

if (!is_logged_in() || !in_array(get_user_role(), ['Admin', 'Staff'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access Denied']);
    exit;
}

$response = ['success' => false, 'message' => 'Invalid request.'];
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $record_id = filter_input(INPUT_POST, 'student_fee_record_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount_paid', FILTER_VALIDATE_FLOAT);
    $method = $_POST['payment_method'];
    $notes = trim($_POST['notes']);

    if (!$record_id || !$amount || $amount <= 0) {
        $response['message'] = 'Invalid data provided.';
        echo json_encode($response);
        exit;
    }

    try {
        $db->beginTransaction();

        // 1. Get the fee record details
        $stmt = $db->prepare("SELECT * FROM student_fee_records WHERE id = ? FOR UPDATE");
        $stmt->execute([$record_id]);
        $fee_record = $stmt->fetch();

        if (!$fee_record) throw new Exception("Fee record not found.");

        $new_paid_amount = $fee_record['amount_paid'] + $amount;
        if ($new_paid_amount > $fee_record['total_amount']) {
            throw new Exception("Payment exceeds amount due.");
        }

        // 2. Insert into payments table
        $receipt_number = 'RCPT-' . time() . '-' . $record_id;
        $payment_sql = "INSERT INTO payments (student_fee_record_id, amount_paid, payment_date, payment_method, receipt_number, notes, processed_by_user_id) VALUES (?, ?, CURDATE(), ?, ?, ?, ?)";
        $payment_stmt = $db->prepare($payment_sql);
        $payment_stmt->execute([$record_id, $amount, $method, $receipt_number, $notes, $_SESSION['user_id']]);
        $payment_id = $db->lastInsertId();

        // 3. Update student_fee_records table
        $new_status = ($new_paid_amount >= $fee_record['total_amount']) ? 'Paid' : 'Partially Paid';
        $update_sql = "UPDATE student_fee_records SET amount_paid = ?, status = ? WHERE id = ?";
        $update_stmt = $db->prepare($update_sql);
        $update_stmt->execute([$new_paid_amount, $new_status, $record_id]);

        $db->commit();
        $response['success'] = true;
        $response['message'] = 'Payment recorded successfully!';
        $response['payment_id'] = $payment_id;

    } catch (Exception $e) {
        $db->rollBack();
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
