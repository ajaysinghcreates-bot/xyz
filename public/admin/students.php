<?php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

// Protect page and check roles
if (!is_logged_in()) {
    redirect('login.php');
}
$user_role = get_user_role();
if ($user_role !== 'Admin' && $user_role !== 'Staff') {
    // Redirect to a generic access denied page or dashboard
    // For now, just a simple message and exit.
    die('Access Denied. You do not have permission to view this page.');
}

// Fetch all students
$students = [];
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM students ORDER BY first_name ASC");
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    // In a real app, log this error
    $error_message = "Error fetching student data: " . $e->getMessage();
}

$page_title = 'Student Management';
include_once ROOT_PATH . '/templates/layouts/header.php'; // We'll create a dashboard header later

?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php"><?php echo APP_NAME; ?></a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo escape($_SESSION['user_fname']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Student Management</h1>
        <a href="student_form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Student</a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <table id="students-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Admission ID</th>
                            <th>Name</th>
                            <th>Date of Birth</th>
                            <th>Parent Contact</th>
                            <th>Admission Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo escape($student['admission_id']); ?></td>
                                <td><?php echo escape($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td><?php echo escape($student['dob']); ?></td>
                                <td><?php echo escape($student['parent_contact']); ?></td>
                                <td><?php echo escape($student['admission_date']); ?></td>
                                <td>
                                    <a href="student_form.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $student['id']; ?>" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Form -->
<form id="delete-form" action="student_delete.php" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <input type="hidden" id="delete-id" name="student_id">
</form>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#students-table').DataTable();

    $('.delete-btn').on('click', function() {
        var studentId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#delete-id').val(studentId);
                $('#delete-form').submit();
            }
        })
    });
});
</script>

