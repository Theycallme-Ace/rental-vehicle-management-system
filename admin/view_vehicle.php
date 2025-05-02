<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Get vehicle ID from URL
$vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$vehicle_id) {
    header('Location: vehicles.php');
    exit();
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Get vehicle details
$stmt = $db->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->execute([$vehicle_id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    header('Location: vehicles.php');
    exit();
}

// Get GPS data if available
$gpsData = null;
if (!empty($vehicle['gps_device_id'])) {
    $gpsData = fetchGPSData($vehicle['gps_device_id']);
}

// Page title
$pageTitle = "Detail Kendaraan";
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
                        <a class="nav-link active" href="vehicles.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Detail Kendaraan</h2>
                    <div>
                        <a href="edit_vehicle.php?id=<?= $vehicle['id'] ?>" class="btn btn-primary me-2">
                            <i class="fas fa-edit me-2"></i>
                            Edit
                        </a>
                        <a href="vehicles.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Vehicle Image -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Foto Kendaraan</h5>
                                <?php if ($vehicle['image']): ?>
                                    <img src="../uploads/vehicles/<?= htmlspecialchars($vehicle['image']) ?>" 
                                         alt="<?= $vehicle['brand'] . ' ' . $vehicle['model'] ?>"
                                         class="img-fluid rounded">
                                <?php else: ?>
                                    <img src="https://images.pexels.com/photos/385998/pexels-photo-385998.jpeg" 
                                         alt="Default vehicle image"
                                         class="img-fluid rounded">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Details -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Informasi Kendaraan</h5>
                                <table class="table">
                                    <tr>
                                        <th style="width: 150px;">Tipe</th>
                                        <td>
                                            <span class="badge bg-<?= $vehicle['type'] === 'bus' ? 'success' : 'info' ?>">
                                                <?= ucfirst($vehicle['type']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Merek</th>
                                        <td><?= htmlspecialchars($vehicle['brand']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Model</th>
                                        <td><?= htmlspecialchars($vehicle['model']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tahun</th>
                                        <td><?= $vehicle['year'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>GPS Device ID</th>
                                        <td>
                                            <?php if ($vehicle['gps_device_id']): ?>
                                                <span class="text-success">
                                                    <i class="fas fa-check-circle"></i>
                                                    <?= htmlspecialchars($vehicle['gps_device_id']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <i class="fas fa-times-circle"></i>
                                                    Tidak Ada
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Ditambahkan</th>
                                        <td><?= date('d/m/Y H:i', strtotime($vehicle['created_at'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Terakhir Diubah</th>
                                        <td><?= date('d/m/Y H:i', strtotime($vehicle['updated_at'])) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Facilities -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Fasilitas</h5>
                                <?php if ($vehicle['facilities']): ?>
                                    <ul class="facilities-list">
                                        <?php 
                                        $facilities = explode("\n", $vehicle['facilities']);
                                        foreach ($facilities as $facility): 
                                            if (trim($facility)):
                                        ?>
                                            <li>
                                                <i class="fas fa-check-circle text-success"></i>
                                                <?= htmlspecialchars(trim($facility)) ?>
                                            </li>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Tidak ada fasilitas yang tercatat</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- GPS Location -->
                        <?php if ($gpsData && !isset($gpsData['error'])): ?>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    Lokasi GPS
                                </h5>
                                <div id="gps-data" data-vehicle-id="<?= $vehicle['id'] ?>">
                                    <div class="gps-info">
                                        <p class="mb-2">
                                            <strong>Latitude:</strong> 
                                            <?= $gpsData['latitude'] ?? 'N/A' ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Longitude:</strong> 
                                            <?= $gpsData['longitude'] ?? 'N/A' ?>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Terakhir diperbarui:</strong>
                                            <?= $gpsData['last_update'] ?? 'N/A' ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
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
