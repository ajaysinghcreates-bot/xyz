<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

// If user is already logged in, redirect to dashboard
if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'CSRF token validation failed. Please try again.';
    }

    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$email || empty($password)) {
        $errors[] = 'Email and password are required.';
    }

    if (empty($errors)) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.id WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['is_active'] == 1) {
                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role_name'];
                    $_SESSION['user_fname'] = $user['first_name'];

                    // Update last login timestamp
                    $update_stmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
                    $update_stmt->execute([':id' => $user['id']]);

                    log_audit('LOGIN_SUCCESS');
                    redirect('dashboard.php');
                } else {
                    $errors[] = 'Your account is inactive. Please contact an administrator.';
                    log_audit('LOGIN_FAILURE', 'Inactive account: ' . $email);
                }
            } else {
                $errors[] = 'Invalid email or password.';
                log_audit('LOGIN_FAILURE', 'Invalid credentials for: ' . $email);
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

$page_title = 'Login';
include_once ROOT_PATH . '/templates/layouts/header.php';

?>

<div class="auth-container">
    <h2 class="text-center mb-4"><?php echo APP_NAME; ?> Login</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p class="mb-0"><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
