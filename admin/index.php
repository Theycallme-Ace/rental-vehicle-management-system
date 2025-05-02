<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Get database connection
$db = Database::getInstance()->getConnection();

// Get counts for dashboard
$vehicleCount = $db->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$busCount = $db->query("SELECT COUNT(*) FROM vehicles WHERE type = 'bus'")->fetchColumn();
$carCount = $db->query("SELECT COUNT(*) FROM vehicles WHERE type = 'car'")->fetchColumn();
$userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Get recent vehicles
$stmt = $db->query("SELECT * FROM vehicles ORDER BY created_at DESC LIMIT 5");
$recentVehicles = $stmt->fetchAll();

// Page title
$pageTitle = "Dashboard Admin";
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
<body class="bg-light">
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
                        <a class="nav-link active" href="index.php">
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
                <h2 class="mb-4">Dashboard</h2>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="card-icon bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-car-alt text-primary fa-2x"></i>
                                    </div>
                                </div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Kendaraan</h6>
                                <h2 class="card-title mb-0"><?= $vehicleCount ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="card-icon bg-success bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-bus text-success fa-2x"></i>
                                    </div>
                                </div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Bus</h6>
                                <h2 class="card-title mb-0"><?= $busCount ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="card-icon bg-info bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-car text-info fa-2x"></i>
                                    </div>
                                </div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Mobil</h6>
                                <h2 class="card-title mb-0"><?= $carCount ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="card-icon bg-warning bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-users text-warning fa-2x"></i>
                                    </div>
                                </div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Pengguna</h6>
                                <h2 class="card-title mb-0"><?= $userCount ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Vehicles -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Kendaraan Terbaru</h5>
                        <a href="vehicles.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>
                            Tambah Kendaraan
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tipe</th>
                                        <th>Merek & Model</th>
                                        <th>Tahun</th>
                                        <th>GPS ID</th>
                                        <th>Tanggal Ditambahkan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentVehicles)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="fas fa-car fa-3x text-muted mb-3"></i>
                                                <p class="mb-0">Belum ada kendaraan yang ditambahkan</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentVehicles as $vehicle): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-<?= $vehicle['type'] === 'bus' ? 'success' : 'info' ?>">
                                                        <?= ucfirst($vehicle['type']) ?>
                                                    </span>
                                                </td>
                                                <td><?= $vehicle['brand'] . ' ' . $vehicle['model'] ?></td>
                                                <td><?= $vehicle['year'] ?></td>
                                                <td>
                                                    <?php if ($vehicle['gps_device_id']): ?>
                                                        <span class="text-success">
                                                            <i class="fas fa-check-circle"></i>
                                                            <?= $vehicle['gps_device_id'] ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">
                                                            <i class="fas fa-times-circle"></i>
                                                            Tidak Ada
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($vehicle['created_at'])) ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="edit_vehicle.php?id=<?= $vehicle['id'] ?>" 
                                                           class="btn btn-sm btn-primary"
                                                           title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="view_vehicle.php?id=<?= $vehicle['id'] ?>" 
                                                           class="btn btn-sm btn-info"
                                                           title="Lihat Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
