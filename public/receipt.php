<?php
// Controller for printing receipts

$payment_id = filter_input(INPUT_GET, 'payment_id', FILTER_VALIDATE_INT);
if (!$payment_id) die('Invalid Payment ID.');

$db = Database::getInstance()->getConnection();
$sql = "SELECT p.*, sfr.total_amount, s.first_name, s.last_name, s.admission_id, c.class_name, fs.fee_name
        FROM payments p
        JOIN student_fee_records sfr ON p.student_fee_record_id = sfr.id
        JOIN students s ON sfr.student_id = s.id
        JOIN class_fees cf ON sfr.class_fee_id = cf.id
        JOIN classes c ON cf.class_id = c.id
        JOIN fee_structures fs ON cf.fee_structure_id = fs.id
        WHERE p.id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$payment_id]);
$receipt_data = $stmt->fetch();

if (!$receipt_data) die('Receipt not found.');

$page_title = 'Print Receipt';
$view_to_load = ROOT_PATH . '/templates/views/receipt_view.php';
require_once ROOT_PATH . '/templates/layouts/receipt_layout.php'; // A different layout for printing
