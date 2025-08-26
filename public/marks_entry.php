<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

if (!is_logged_in() || !in_array(get_user_role(), ['Admin', 'Staff'])) {
    die('Access Denied.');
}

$db = Database::getInstance()->getConnection();
// Logic for selecting exam, class, subject and then listing students for marks entry...

$page_title = 'Enter Marks';
include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4">Marks Entry</h1>

    <!-- Selection Form -->
    <div class="card mb-4">
        <div class="card-header">Select Details</div>
        <div class="card-body">
            <p>A multi-step form to select Exam -> Class -> Subject will go here.</p>
        </div>
    </div>

    <!-- Marks Entry Table -->
    <div class="card">
        <div class="card-header">Enter Student Marks</div>
        <div class="card-body">
            <p>A table of students in the selected class will be listed here with input fields for their marks.</p>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
