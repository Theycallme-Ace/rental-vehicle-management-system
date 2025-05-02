<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Get database connection
$db = Database::getInstance()->getConnection();

// Get user details
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username harus diisi";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid";
    }
    
    // Check if username or email already exists
    $stmt = $db->prepare("
        SELECT id FROM users 
        WHERE (username = ? OR email = ?) 
        AND id != ?
    ");
    $stmt->execute([$username, $email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $errors[] = "Username atau email sudah digunakan";
    }
    
    // If changing password
    if (!empty($current_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Password saat ini tidak sesuai";
        }
        if (empty($new_password)) {
            $errors[] = "Password baru harus diisi";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "Konfirmasi password tidak sesuai";
        }
    }
    
    if (empty($errors)) {
        try {
            if (!empty($new_password)) {
                // Update with new password
                $stmt = $db->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, password = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $username,
                    $email,
                    password_hash($new_password, PASSWORD_DEFAULT),
                    $_SESSION['user_id']
                ]);
            } else {
                // Update without changing password
                $stmt = $db->prepare("
                    UPDATE users 
                    SET username = ?, email = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $username,
                    $email,
                    $_SESSION['user_id']
                ]);
            }
            
            // Update session
            $_SESSION['username'] = $username;
            
            $_SESSION['success'] = "Profil berhasil diperbarui";
            header('Location: profile.php');
            exit();
            
        } catch (PDOException $e) {
            error_log("Update Profile Error: " . $e->getMessage());
            $errors[] = "Gagal memperbarui profil";
        }
    }
}

// Page title
$pageTitle = "Profil Saya";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Rental Bis & Mobil</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="admin-sidebar bg-dark text-light" style="min-width: 250px; min-height: 100vh;">
            <div class="p-3">
                <h5 class="text-light mb-3 d-flex align-items-center">
                    <i class="fas fa-car-alt me-2"></i>
                    Rental Admin
                </h5>
                <hr class="bg-light">
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vehicles.php">
                            <i class="fas fa-car me-2"></i>
                            Kelola Kendaraan
                        </a>
                    </li>
                    <?php if (in_array($_SESSION['role'], ['superadmin', 'admin'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-2"></i>
                            Kelola Pengguna
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">
                            <i class="fas fa-user-circle me-2"></i>
                            Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-link" id="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="d-flex align-items-center">
                        <span class="me-2">
                            Welcome, <?= htmlspecialchars($_SESSION['username']) ?>
                        </span>
                        <span class="badge bg-primary">
                            <?= ucfirst($_SESSION['role']) ?>
                        </span>
                    </div>
                </div>
            </nav>

            <!-- Content -->
            <div class="container-fluid p-4">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0">Profil Saya</h4>
                            </div>
                            <div class="card-body">
                                <?php if (isset($_SESSION['success'])): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <?= $_SESSION['success'] ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    <?php unset($_SESSION['success']); ?>
                                <?php endif; ?>

                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <ul class="mb-0">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?= $error ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username *</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="username" 
                                               name="username" 
                                               value="<?= htmlspecialchars($user['username']) ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Silakan masukkan username
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               value="<?= htmlspecialchars($user['email']) ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Silakan masukkan email yang valid
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <h5 class="mb-3">Ubah Password</h5>
                                    <p class="text-muted mb-3">
                                        Kosongkan bagian ini jika tidak ingin mengubah password
                                    </p>

                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Password Saat Ini</label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="current_password" 
                                               name="current_password">
                                    </div>

                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Password Baru</label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="new_password" 
                                               name="new_password">
                                    </div>

                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="confirm_password" 
                                               name="confirm_password">
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Account Info Card -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h4 class="mb-0">Informasi Akun</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong>Role:</strong> 
                                            <span class="badge bg-primary">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Bergabung Sejak:</strong> 
                                            <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong>Terakhir Login:</strong>
                                            <?= isset($_SESSION['last_login']) ? date('d/m/Y H:i', strtotime($_SESSION['last_login'])) : 'N/A' ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Terakhir Diperbarui:</strong>
                                            <?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="../js/main.js"></script>
</body>
</html>
