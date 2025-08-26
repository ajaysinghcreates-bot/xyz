<?php

// ----------------------------
// Database Configuration
// ----------------------------
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'sms_db');
define('DB_USER', 'root'); // Replace with your database username
define('DB_PASS', '');     // Replace with your database password
define('DB_CHARSET', 'utf8mb4');

// ----------------------------
// Application Settings
// ----------------------------
define('APP_NAME', 'School Management System');
define('APP_URL', 'http://localhost'); // Change to your actual URL
define('DEBUG_MODE', true); // Set to false in production

// ----------------------------
// Security & Session
// ----------------------------
define('SESSION_NAME', 'SMSSESSION');
define('CSRF_TOKEN_NAME', 'csrf_token');

// ----------------------------
// Admin Registration Passcode
// ----------------------------
define('ADMIN_REGISTRATION_PASSCODE', '62326 420 59');

// ----------------------------
-- Error Reporting
// ----------------------------
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// ----------------------------
// Set Timezone
// ----------------------------
date_default_timezone_set('UTC'); // Set to your school's timezone, e.g., 'America/New_York'

// ----------------------------
// Start Session
// ----------------------------
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
