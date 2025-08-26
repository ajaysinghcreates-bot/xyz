<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/src/includes/Database.php';
require_once ROOT_PATH . '/src/includes/functions.php';

// If user is already logged in, redirect to dashboard
if (is_logged_in()) {
    // Redirect based on role
    $role = get_user_role();
    if ($role === 'Admin' || $role === 'Staff') {
        redirect('admin/dashboard.php');
    } else {
        redirect('student/dashboard.php');
    }
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
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role_name'];
                    $_SESSION['user_fname'] = $user['first_name'];

                    $update_stmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
                    $update_stmt->execute([':id' => $user['id']]);

                    log_audit('LOGIN_SUCCESS');
                    // Redirect based on role
                    if ($user['role_name'] === 'Admin' || $user['role_name'] === 'Staff') {
                        redirect('admin/dashboard.php');
                    } else {
                        redirect('student/dashboard.php');
                    }
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
// We don't include the standard header/footer for the login page to have a custom layout.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/public_style.css">
    <style>
        body {
            background-color: #F3F4F6;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 900px;
            border-radius: 1rem;
            overflow: hidden;
        }
        .login-image {
            background: url('https://images.unsplash.com/photo-1509062522246-3755977927d7?q=80&w=2132&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center;
            background-size: cover;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="card login-card shadow-lg">
        <div class="row g-0">
            <div class="col-md-6 d-none d-md-block login-image"></div>
            <div class="col-md-6">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4"><b><?php echo APP_NAME; ?></b> Login</h2>

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
                        <div class="text-center mt-3">
                            <a href="#">Forgot Password?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
