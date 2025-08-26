<?php
// Main Dashboard Layout
// This file wraps all admin-side content.

if (!is_logged_in()) {
    redirect('login');
}

include_once ROOT_PATH . '/templates/layouts/header.php';
?>

<div class="d-flex">
    <nav id="sidebar" class="bg-dark text-white vh-100 p-3 d-flex flex-column" style="width: 280px;">
        <div>
            <a href="<?php echo APP_URL; ?>/dashboard" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span class="fs-4">School System</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="<?php echo APP_URL; ?>/dashboard" class="nav-link text-white">Dashboard</a>
                </li>
                
                <?php if (in_array(get_user_role(), ['Admin', 'Staff'])): ?>
                <li>
                    <a href="<?php echo APP_URL; ?>/students" class="nav-link text-white">Students</a>
                </li>
                <li>
                    <a href="<?php echo APP_URL; ?>/attendance" class="nav-link text-white">Attendance</a>
                </li>
                <?php endif; ?>

                <?php if (get_user_role() === 'Admin'): ?>
                <li>
                    <a href="#academics-submenu" data-bs-toggle="collapse" class="nav-link text-white">Academics</a>
                    <div class="collapse" id="academics-submenu">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ms-4">
                            <li><a href="<?php echo APP_URL; ?>/sessions" class="nav-link text-white">Sessions</a></li>
                            <li><a href="<?php echo APP_URL; ?>/classes" class="nav-link text-white">Classes</a></li>
                            <li><a href="<?php echo APP_URL; ?>/subjects" class="nav-link text-white">Subjects</a></li>
                            <li><a href="<?php echo APP_URL; ?>/class_subject_assignment" class="nav-link text-white">Assign Subjects</a></li>
                            <li><a href="<?php echo APP_URL; ?>/teacher_assignment" class="nav-link text-white">Assign Teachers</a></li>
                        </ul>
                    </div>
                </li>
                <li>
                    <a href="#finance-submenu" data-bs-toggle="collapse" class="nav-link text-white">Financial</a>
                    <div class="collapse" id="finance-submenu">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ms-4">
                            <li><a href="<?php echo APP_URL; ?>/enrollment" class="nav-link text-white">Enrollment</a></li>
                            <li><a href="<?php echo APP_URL; ?>/fee_structures" class="nav-link text-white">Fee Structures</a></li>
                            <li><a href="<?php echo APP_URL; ?>/class_fees" class="nav-link text-white">Class Fees</a></li>
                            <li><a href="<?php echo APP_URL; ?>/fee_collection" class="nav-link text-white">Fee Collection</a></li>
                            <li><a href="<?php echo APP_URL; ?>/expenses" class="nav-link text-white">Expenses</a></li>
                        </ul>
                    </div>
                </li>
                <li>
                    <a href="#exams-submenu" data-bs-toggle="collapse" class="nav-link text-white">Exams</a>
                    <div class="collapse" id="exams-submenu">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ms-4">
                            <li><a href="<?php echo APP_URL; ?>/exam_types" class="nav-link text-white">Exam Types</a></li>
                            <li><a href="<?php echo APP_URL; ?>/exams" class="nav-link text-white">Exams</a></li>
                            <li><a href="<?php echo APP_URL; ?>/marks_entry" class="nav-link text-white">Marks Entry</a></li>
                        </ul>
                    </div>
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
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#themeModal">Change Theme</a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/logout">Sign out</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1 p-4" style="overflow-y: auto; height: 100vh;">
        <?php 
            // This is where the content of individual pages will be injected
            if (isset($view_to_load)) {
                include $view_to_load;
            }
        ?>
    </main>
</div>

<?php include_once ROOT_PATH . '/templates/layouts/footer.php'; ?>
