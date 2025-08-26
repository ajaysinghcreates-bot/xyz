<?php

// Redirect to a specific page
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

// Escape HTML to prevent XSS attacks
function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}

// Generate a CSRF token and store it in the session
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate a CSRF token
function validate_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        // Token is valid, unset it to prevent reuse
        unset($_SESSION['csrf_token']);
        return true;
    }
    return false;
}

// Check if a user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if a user is an admin
function is_admin() {
    return get_user_role() === 'Admin';
}

// Get the role of the logged-in user
function get_user_role() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

// Log an audit trail event
function log_audit($action, $details = '', $user_id = null) {
    try {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (:user_id, :action, :details, :ip_address)";
        $stmt = $db->prepare($sql);

        $userIdToLog = $user_id ?? ($_SESSION['user_id'] ?? null);

        $stmt->execute([
            ':user_id' => $userIdToLog,
            ':action' => $action,
            ':details' => $details,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
    } catch (PDOException $e) {
        // Fail silently or log to a file to avoid breaking user-facing pages
        error_log('Audit Log Failed: ' . $e->getMessage());
    }
}
