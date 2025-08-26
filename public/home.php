<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/public_style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
    <div class="container">
        <a class="navbar-brand" href="home.php">School Logo</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#about">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="#announcements">Announcements</a></li>
                <li class="nav-item"><a class="nav-link" href="#gallery">Gallery</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                <li class="nav-item"><a class="btn btn-primary ms-2" href="login.php">Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<header class="hero-section text-white text-center">
    <div class="container">
        <h1 class="display-4">Welcome to Our School</h1>
        <p class="lead">Nurturing future leaders through excellence in education.</p>
    </div>
</header>

<main class="container my-5">
    <section id="about" class="mb-5">
        <h2>About Us</h2>
        <p>Details about the school's history, mission, and vision.</p>
    </section>

    <section id="announcements" class="mb-5">
        <h2>Announcements</h2>
        <div class="card">
            <div class="card-body">Latest news and updates will appear here.</div>
        </div>
    </section>

    <section id="gallery" class="mb-5">
        <h2>Gallery</h2>
        <div class="row">
            <!-- Gallery images will be loaded here -->
            <div class="col-md-4"><div class="bg-secondary text-white p-5 mb-2">Image</div></div>
            <div class="col-md-4"><div class="bg-secondary text-white p-5 mb-2">Image</div></div>
            <div class="col-md-4"><div class="bg-secondary text-white p-5 mb-2">Image</div></div>
        </div>
    </section>

    <section id="contact" class="mb-5">
        <h2>Contact Us</h2>
        <p>School Address, Phone, Email. An inquiry form and map will be here.</p>
    </section>
</main>

<footer class="bg-dark text-white text-center p-4">
    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
