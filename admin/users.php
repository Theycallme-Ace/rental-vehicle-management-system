<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Check if user has permission to access this page
if (!in_array($_SESSION['role'], ['superadmin', 'admin'])) {
    header('HTTP/1.1 403 Forbidden');
    die('Access Denied');
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Prevent deletion of own account
    if ($user_id === $_SESSION['user_id']) {
        $_SESSION['error'] = "Anda tidak dapat menghapus akun Anda sendiri";
    } else {
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success'] = "Pengguna berhasil dihapus";
        } catch (PDOException $e) {
            error_log("Delete User Error: " . $e->getMessage());
            $_SESSION['error'] = "Gagal menghapus pengguna";
        }
    }
    
    header('Location: users.php');
    exit();
}

// Get all users
$stmt = $db->query("SELECT * FROM users ORDER BY role, username");
$users = $stmt->fetchAll();

// Page title
$pageTitle = "Kelola Pengguna";
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
                        <a class="nav-link active" href="users.php">
                            <i class="fas fa-users me-2"></i>
                            Kelola Pengguna
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Kelola Pengguna</h2>
                    <button type="button" 
                            class="btn btn-primary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus me-2"></i>
                        Tambah Pengguna
                    </button>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Terakhir Diubah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                switch ($user['role']) {
                                                    case 'superadmin':
                                                        echo 'danger';
                                                        break;
                                                    case 'admin':
                                                        echo 'primary';
                                                        break;
                                                    case 'manager':
                                                        echo 'success';
                                                        break;
                                                    case 'staff':
                                                        echo 'info';
                                                        break;
                                                    default:
                                                        echo 'secondary';
                                                }
                                            ?>">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editUserModal"
                                                        data-user-id="<?= $user['id'] ?>"
                                                        data-username="<?= htmlspecialchars($user['username']) ?>"
                                                        data-email="<?= htmlspecialchars($user['email']) ?>"
                                                        data-role="<?= $user['role'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                    <form method="POST" 
                                                          action="" 
                                                          class="d-inline" 
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <button type="submit" 
                                                                name="delete_user" 
                                                                class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_user.php" method="POST" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <div class="invalid-feedback">
                                Silakan masukkan username
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">
                                Silakan masukkan email yang valid
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback">
                                Silakan masukkan password
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Pilih Role</option>
                                <?php if ($_SESSION['role'] === 'superadmin'): ?>
                                    <option value="admin">Admin</option>
                                <?php endif; ?>
                                <option value="manager">Manager</option>
                                <option value="staff">Staff</option>
                                <option value="user">User</option>
                            </select>
                            <div class="invalid-feedback">
                                Silakan pilih role
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="edit_user.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                            <div class="invalid-feedback">
                                Silakan masukkan username
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                            <div class="invalid-feedback">
                                Silakan masukkan email yang valid
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                            <div class="form-text">
                                Kosongkan jika tidak ingin mengubah password
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role *</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="">Pilih Role</option>
                                <?php if ($_SESSION['role'] === 'superadmin'): ?>
                                    <option value="admin">Admin</option>
                                <?php endif; ?>
                                <option value="manager">Manager</option>
                                <option value="staff">Staff</option>
                                <option value="user">User</option>
                            </select>
                            <div class="invalid-feedback">
                                Silakan pilih role
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="../js/main.js"></script>

    <script>
        // Edit User Modal Population
        document.getElementById('editUserModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const username = button.getAttribute('data-username');
            const email = button.getAttribute('data-email');
            const role = button.getAttribute('data-role');
            
            const modal = this;
            modal.querySelector('#edit_user_id').value = userId;
            modal.querySelector('#edit_username').value = username;
            modal.querySelector('#edit_email').value = email;
            modal.querySelector('#edit_role').value = role;
        });
    </script>
</body>
</html>
