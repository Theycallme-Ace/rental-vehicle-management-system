<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Check if user has permission
if (!in_array($_SESSION['role'], ['superadmin', 'admin'])) {
    header('HTTP/1.1 403 Forbidden');
    die('Access Denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $role = sanitizeInput($_POST['role']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username harus diisi";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid";
    }
    if (empty($password)) {
        $errors[] = "Password harus diisi";
    }
    if (empty($role) || !in_array($role, ['admin', 'manager', 'staff', 'user'])) {
        $errors[] = "Role tidak valid";
    }
    // Only superadmin can create admin users
    if ($role === 'admin' && $_SESSION['role'] !== 'superadmin') {
        $errors[] = "Anda tidak memiliki izin untuk membuat user admin";
    }
    
    if (empty($errors)) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Check if username or email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Username atau email sudah digunakan";
            } else {
                // Insert new user
                $stmt = $db->prepare("
                    INSERT INTO users (username, email, password, role) 
                    VALUES (?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $username,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $role
                ]);
                
                $_SESSION['success'] = "Pengguna baru berhasil ditambahkan";
            }
        } catch (PDOException $e) {
            error_log("Add User Error: " . $e->getMessage());
            $_SESSION['error'] = "Gagal menambahkan pengguna";
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Redirect back to users page
header('Location: users.php');
exit();
