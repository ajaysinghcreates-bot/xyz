<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF Validation
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'CSRF token validation failed. Please try again.';
    }

    // 2. Passcode Validation
    if (empty($_POST['passcode']) || trim($_POST['passcode']) !== ADMIN_REGISTRATION_PASSCODE) {
        $errors[] = 'Invalid registration passcode.';
    }

    // 3. Input Validation
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $errors[] = 'All fields except passcode are required.';
    }
    if (!$email) {
        $errors[] = 'Invalid email format.';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    // 4. Check if email already exists
    if (empty($errors)) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with this email already exists.';
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    // 5. Create User
    if (empty($errors)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $admin_role_id = 1; // From roles table

            $sql = "INSERT INTO users (role_id, first_name, last_name, email, password_hash, is_active) VALUES (:role_id, :first_name, :last_name, :email, :password_hash, 1)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':role_id' => $admin_role_id,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':email' => $email,
                ':password_hash' => $password_hash
            ]);
            
            $new_user_id = $db->lastInsertId();
            log_audit('ADMIN_REGISTRATION_SUCCESS', 'New admin created: ' . $email, $new_user_id);

            $success_message = "Admin account created successfully! You can now <a href='login.php'>log in</a>.";

        } catch (PDOException $e) {
            $errors[] = "Failed to create admin user. " . $e->getMessage();
            log_audit('ADMIN_REGISTRATION_FAILURE', 'Reason: ' . $e->getMessage());
        }
    }
}

$page_title = 'Admin Registration';
include_once ROOT_PATH . '/templates/layouts/header.php';

?>

<div class="auth-container">
    <h2 class="text-center mb-4">Admin Registration</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p class="mb-0"><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php else: ?>
        <form action="secure_admin_register.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
            </div>
            <div class="mb-3">
                <label for="passcode" class="form-label">Registration Passcode</label>
                <input type="text" class="form-control" id="passcode" name="passcode" required>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Register</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
