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
    $user_id = (int)$_POST['user_id'];
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
    if (empty($role) || !in_array($role, ['admin', 'manager', 'staff', 'user'])) {
        $errors[] = "Role tidak valid";
    }
    // Only superadmin can modify admin users
    if ($role === 'admin' && $_SESSION['role'] !== 'superadmin') {
        $errors[] = "Anda tidak memiliki izin untuk mengubah user admin";
    }
    
    if (empty($errors)) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Check if username or email already exists (excluding current user)
            $stmt = $db->prepare("
                SELECT id FROM users 
                WHERE (username = ? OR email = ?) 
                AND id != ?
            ");
            $stmt->execute([$username, $email, $user_id]);
            
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Username atau email sudah digunakan";
            } else {
                // Update user
                if (!empty($password)) {
                    // Update with new password
                    $stmt = $db->prepare("
                        UPDATE users 
                        SET username = ?, email = ?, password = ?, role = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $username,
                        $email,
                        password_hash($password, PASSWORD_DEFAULT),
                        $role,
                        $user_id
                    ]);
                } else {
                    // Update without changing password
                    $stmt = $db->prepare("
                        UPDATE users 
                        SET username = ?, email = ?, role = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $username,
                        $email,
                        $role,
                        $user_id
                    ]);
                }
                
                $_SESSION['success'] = "Data pengguna berhasil diperbarui";
                
                // If user updates their own account, update session data
                if ($user_id === $_SESSION['user_id']) {
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                }
            }
        } catch (PDOException $e) {
            error_log("Edit User Error: " . $e->getMessage());
            $_SESSION['error'] = "Gagal memperbarui data pengguna";
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Redirect back to users page
header('Location: users.php');
exit();
