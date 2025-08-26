<?php
// Main Dashboard Layout

if (!is_logged_in()) {
    redirect('login.php');
}

$user_role = get_user_role();

include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="d-flex">
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div>
            <a href="<?php echo APP_URL; ?>/dashboard" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <i class="fas fa-school fa-2x me-2"></i>
                <span class="fs-4">SchoolMS</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="<?php echo APP_URL; ?>/dashboard" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                
                <?php if ($user_role === 'Admin' || $user_role === 'Staff'): ?>
                <li>
                    <a href="<?php echo APP_URL; ?>/admin/students.php" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], 'students') ? 'active' : ''; ?>">
                        <i class="fas fa-user-graduate"></i> Students
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($user_role === 'Admin'): ?>
                <li>
                    <a href="#academics-submenu" data-bs-toggle="collapse" class="nav-link">
                        <i class="fas fa-book"></i> Academics
                    </a>
                    <div class="collapse" id="academics-submenu">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ms-4">
                            <li><a href="<?php echo APP_URL; ?>/admin/sessions.php" class="nav-link">Sessions</a></li>
                            <li><a href="<?php echo APP_URL; ?>/admin/classes.php" class="nav-link">Classes</a></li>
                            <li><a href="<?php echo APP_URL; ?>/admin/subjects.php" class="nav-link">Subjects</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if ($user_role === 'Student'): ?>
                <li>
                    <a href="<?php echo APP_URL; ?>/student/attendance.php" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], 'attendance') ? 'active' : ''; ?>">
                        <i class="fas fa-check-circle"></i> Attendance
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user me-2"></i>
                <strong><?php echo escape($_SESSION['user_fname']); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                <li><a class="dropdown-item" href="#">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/logout.php">Sign out</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 main-content">
        <?php 
            if (isset($view_to_load)) {
                include $view_to_load;
            }
        ?>
    </main>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
