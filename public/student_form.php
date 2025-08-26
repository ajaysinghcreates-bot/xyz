<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

// Protect page and check roles
if (!is_logged_in()) {
    redirect('login.php');
}
$user_role = get_user_role();
if ($user_role !== 'Admin' && $user_role !== 'Staff') {
    die('Access Denied.');
}

$page_title = 'Add Student';
$errors = [];
$student = [
    'id' => null, 'first_name' => '', 'last_name' => '', 'dob' => '', 'gender' => '',
    'address' => '', 'parent_name' => '', 'parent_contact' => '', 'admission_date' => date('Y-m-d'),
    'photo_path' => ''
];

$is_edit_mode = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_edit_mode = true;
    $page_title = 'Edit Student';
    $student_id = $_GET['id'];

    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->execute([':id' => $student_id]);
        $student = $stmt->fetch();
        if (!$student) {
            $_SESSION['error_message'] = "Student not found.";
            redirect('students.php');
        }
    } catch (PDOException $e) {
        $errors[] = "Error fetching student data: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'CSRF token mismatch.';
    }

    // Sanitize and validate inputs
    $student['first_name'] = trim($_POST['first_name']);
    $student['last_name'] = trim($_POST['last_name']);
    // ... add all other fields from the form

    // Basic validation
    if (empty($student['first_name']) || empty($student['last_name'])) {
        $errors[] = 'First and Last name are required.';
    }

    // Photo upload handling
    $photo_path = $student['photo_path']; // Keep old photo if new one isn't uploaded
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = ROOT_PATH . '/public/uploads/photos/';
        $filename = uniqid() . '-' . basename($_FILES['photo']['name']);
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            // If successful, remove old photo in edit mode
            if ($is_edit_mode && !empty($student['photo_path']) && file_exists($upload_dir . $student['photo_path'])) {
                unlink($upload_dir . $student['photo_path']);
            }
            $photo_path = $filename;
        } else {
            $errors[] = 'Failed to upload photo.';
        }
    }

    if (empty($errors)) {
        try {
            $db = Database::getInstance()->getConnection();
            if ($is_edit_mode) {
                // Update existing student
                $sql = "UPDATE students SET first_name = ?, last_name = ?, dob = ?, gender = ?, address = ?, parent_name = ?, parent_contact = ?, admission_date = ?, photo_path = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['dob'], $_POST['gender'], $_POST['address'], $_POST['parent_name'], $_POST['parent_contact'], $_POST['admission_date'], $photo_path, $student_id]);
                log_audit('STUDENT_UPDATE', 'Updated student ID: ' . $student_id);
                $_SESSION['success_message'] = 'Student record updated successfully!';
            } else {
                // Insert new student
                $admission_id = 'ADM-' . date('Y') . '-' . rand(1000, 9999);
                $sql = "INSERT INTO students (admission_id, first_name, last_name, dob, gender, address, parent_name, parent_contact, admission_date, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$admission_id, $_POST['first_name'], $_POST['last_name'], $_POST['dob'], $_POST['gender'], $_POST['address'], $_POST['parent_name'], $_POST['parent_contact'], $_POST['admission_date'], $photo_path]);
                $new_student_id = $db->lastInsertId();
                log_audit('STUDENT_CREATE', 'Created student ID: ' . $new_student_id);
                $_SESSION['success_message'] = 'New student added successfully!';
            }
            redirect('students.php');
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="container">
    <h1 class="mb-4"><?php echo $page_title; ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error) echo "<p class='mb-0'>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="student_form.php<?php echo $is_edit_mode ? '?id=' . $student_id : ''; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo escape($student['first_name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo escape($student['last_name']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="dob" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="dob" value="<?php echo escape($student['dob']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="" disabled <?php echo empty($student['gender']) ? 'selected' : ''; ?>>Select Gender</option>
                            <option value="Male" <?php echo ($student['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($student['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($student['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo escape($student['address']); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="parent_name" class="form-label">Parent/Guardian Name</label>
                        <input type="text" class="form-control" id="parent_name" name="parent_name" value="<?php echo escape($student['parent_name']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="parent_contact" class="form-label">Parent/Guardian Contact</label>
                        <input type="text" class="form-control" id="parent_contact" name="parent_contact" value="<?php echo escape($student['parent_contact']); ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="admission_date" class="form-label">Admission Date</label>
                        <input type="date" class="form-control" id="admission_date" name="admission_date" value="<?php echo escape($student['admission_date']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="photo" class="form-label">Student Photo</label>
                        <input class="form-control" type="file" id="photo" name="photo">
                        <?php if ($is_edit_mode && !empty($student['photo_path'])): ?>
                            <small class="form-text text-muted">Current photo:</small>
                            <img src="uploads/photos/<?php echo escape($student['photo_path']); ?>" alt="Student Photo" style="max-height: 80px; margin-top: 10px;">
                        <?php endif; ?>
                    </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-success"><?php echo $is_edit_mode ? 'Update Student' : 'Add Student'; ?></button>
                <a href="students.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
