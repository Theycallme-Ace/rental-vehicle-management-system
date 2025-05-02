<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Change this according to your cPanel database username
define('DB_PASS', '');      // Change this according to your cPanel database password
define('DB_NAME', 'rental_bis');

// GPS API configuration
define('GPS_API_URL', 'https://open-gps.example.com/api?device_id=');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Base URL - Change this according to your cPanel domain
define('BASE_URL', 'http://localhost:8000');

// Upload directory
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/vehicles/');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();
