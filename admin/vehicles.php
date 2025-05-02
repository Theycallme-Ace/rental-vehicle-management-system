<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Get database connection
$db = Database::getInstance()->getConnection();

// Handle vehicle deletion
if (isset($_POST['delete_vehicle'])) {
    $vehicle_id = (int)$_POST['vehicle_id'];
    
    try {
        // Get vehicle image before deletion
        $stmt = $db->prepare("SELECT image FROM vehicles WHERE id = ?");
        $stmt->execute([$vehicle_id]);
        $vehicle = $stmt->fetch();

        // Delete vehicle from database
        $stmt = $db->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->execute([$vehicle_id]);

        // Delete vehicle image if exists
        if ($vehicle && $vehicle['image']) {
            $image_path = '../uploads/vehicles/' . $vehicle['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        $_SESSION['success'] = "Kendaraan berhasil dihapus";
    } catch (PDOException $e) {
        error_log("Delete Vehicle Error: " . $e->getMessage());
        $_SESSION['error'] = "Gagal menghapus kendaraan";
    }
    
    header('Location: vehicles.php');
    exit();
}

// Get all vehicles with sorting and filtering
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';

$query = "SELECT * FROM vehicles";
$params = [];

if ($filter_type) {
    $query .= " WHERE type = ?";
    $params[] = $filter_type;
}

$query .= " ORDER BY $sort $order";

$stmt = $db->prepare($query);
$stmt->execute($params);
$vehicles = $stmt->fetchAll();

// Page title
$pageTitle = "Kelola Kendaraan";
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
                    <h2 class="mb-0">Kelola Kendaraan</h2>
                    <a href="add_vehicle.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Tambah Kendaraan
                    </a>
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

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Filter Tipe</label>
                                <select name="type" class="form-select">
                                    <option value="">Semua Tipe</option>
                                    <option value="bus" <?= $filter_type === 'bus' ? 'selected' : '' ?>>Bus</option>
                                    <option value="car" <?= $filter_type === 'car' ? 'selected' : '' ?>>Mobil</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Urutkan</label>
                                <select name="sort" class="form-select">
                                    <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Tanggal Ditambahkan</option>
                                    <option value="brand" <?= $sort === 'brand' ? 'selected' : '' ?>>Merek</option>
                                    <option value="year" <?= $sort === 'year' ? 'selected' : '' ?>>Tahun</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Urutan</label>
                                <select name="order" class="form-select">
                                    <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Menurun</option>
                                    <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>Menaik</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i>
                                    Terapkan Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Vehicles Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Gambar</th>
                                        <th>Tipe</th>
                                        <th>Merek & Model</th>
                                        <th>Tahun</th>
                                        <th>GPS ID</th>
                                        <th>Tanggal Ditambahkan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehicles as $vehicle): ?>
                                    <tr>
                                        <td>
                                            <img src="<?= !empty($vehicle['image']) ? '../uploads/vehicles/' . $vehicle['image'] : 
                                                'https://images.pexels.com/photos/385998/pexels-photo-385998.jpeg' ?>" 
                                                 alt="<?= $vehicle['brand'] . ' ' . $vehicle['model'] ?>"
                                                 class="img-thumbnail"
                                                 style="width: 80px; height: 60px; object-fit: cover;">
                                        </td>
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
                                                <form method="POST" 
                                                      action="" 
                                                      class="d-inline" 
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus kendaraan ini?');">
                                                    <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">
                                                    <button type="submit" 
                                                            name="delete_vehicle" 
                                                            class="btn btn-sm btn-danger"
                                                            title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($vehicles)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-car fa-3x text-muted mb-3"></i>
                                            <p class="mb-0">Belum ada kendaraan yang ditambahkan</p>
                                        </td>
                                    </tr>
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
