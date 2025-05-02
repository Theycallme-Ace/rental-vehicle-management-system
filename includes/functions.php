<?php
require_once 'config.php';
require_once 'db.php';

// Input sanitization
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "/admin/login.php");
        exit();
    }
}

// Check user role
function checkRole($allowedRoles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], (array)$allowedRoles)) {
        header("HTTP/1.1 403 Forbidden");
        die('Access Denied');
    }
}

// Redirect helper
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// File upload handler
function uploadFile($file) {
    $target_dir = UPLOAD_DIR;
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $fileExtension;
    $target_file = $target_dir . $newFileName;

    // Check if file is an actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['error' => 'File is not an image.'];
    }

    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return ['error' => 'File is too large. Maximum size is 5MB.'];
    }

    // Allow certain file formats
    if (!in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
        return ['error' => 'Only JPG, JPEG & PNG files are allowed.'];
    }

    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $newFileName];
    } else {
        return ['error' => 'Failed to upload file.'];
    }
}

// Get GPS Data
function fetchGPSData($gps_device_id) {
    $url = GPS_API_URL . urlencode($gps_device_id);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        error_log('GPS API Error: ' . curl_error($ch));
        curl_close($ch);
        return ['error' => 'GPS data currently unavailable'];
    }
    
    curl_close($ch);
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('GPS API JSON Error: ' . json_last_error_msg());
        return ['error' => 'Invalid GPS data received'];
    }
    
    return $data;
}

// Format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Get vehicle status (including GPS data if available)
function getVehicleStatus($vehicle) {
    if (empty($vehicle['gps_device_id'])) {
        return 'GPS tidak tersedia';
    }
    
    $gpsData = fetchGPSData($vehicle['gps_device_id']);
    if (isset($gpsData['error'])) {
        return $gpsData['error'];
    }
    
    return $gpsData;
}

// Log activity
function logActivity($user_id, $action, $details = '') {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $action, $details]);
}

// Generate random string
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 
        ceil($length/strlen($x)))), 1, $length);
}

// Check if email is valid
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Get user role name
function getRoleName($role) {
    $roles = [
        'superadmin' => 'Super Admin',
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'staff' => 'Staff',
        'user' => 'User'
    ];
    return isset($roles[$role]) ? $roles[$role] : 'Unknown Role';
}
