<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background-color: #f8f9fa; }
        .error-container { text-align: center; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="display-1">404</h1>
        <p class="lead">Oops! The page you're looking for could not be found.</p>
        <a href="/" class="btn btn-primary">Go Home</a>
    </div>
</body>
</html>
