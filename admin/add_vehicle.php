<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = sanitizeInput($_POST['type']);
    $brand = sanitizeInput($_POST['brand']);
    $model = sanitizeInput($_POST['model']);
    $year = (int)$_POST['year'];
    $facilities = sanitizeInput($_POST['facilities']);
    $gps_device_id = sanitizeInput($_POST['gps_device_id']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($type) || !in_array($type, ['bus', 'car'])) {
        $errors[] = "Tipe kendaraan tidak valid";
    }
    if (empty($brand)) {
        $errors[] = "Merek kendaraan harus diisi";
    }
    if (empty($model)) {
        $errors[] = "Model kendaraan harus diisi";
    }
    if ($year < 1900 || $year > date('Y') + 1) {
        $errors[] = "Tahun kendaraan tidak valid";
    }
    
    // Handle image upload
    $image_filename = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['image']);
        if (isset($upload_result['error'])) {
            $errors[] = $upload_result['error'];
        } else {
            $image_filename = $upload_result['filename'];
        }
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO vehicles (type, brand, model, year, facilities, image, gps_device_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $type,
                $brand,
                $model,
                $year,
                $facilities,
                $image_filename,
                $gps_device_id
            ]);
            
            $_SESSION['success'] = "Kendaraan berhasil ditambahkan";
            header('Location: vehicles.php');
            exit();
            
        } catch (PDOException $e) {
            error_log("Add Vehicle Error: " . $e->getMessage());
            $errors[] = "Gagal menambahkan kendaraan";
        }
    }
}

// Page title
$pageTitle = "Tambah Kendaraan";
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
                    <h2 class="mb-0">Tambah Kendaraan Baru</h2>
                    <a href="vehicles.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Kembali
                    </a>
                </div>

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

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="type" class="form-label">Tipe Kendaraan *</label>
                                        <select class="form-select" id="type" name="type" required>
                                            <option value="">Pilih Tipe</option>
                                            <option value="bus" <?= isset($_POST['type']) && $_POST['type'] === 'bus' ? 'selected' : '' ?>>Bus</option>
                                            <option value="car" <?= isset($_POST['type']) && $_POST['type'] === 'car' ? 'selected' : '' ?>>Mobil</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Silakan pilih tipe kendaraan
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="brand" class="form-label">Merek *</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="brand" 
                                               name="brand" 
                                               value="<?= isset($_POST['brand']) ? htmlspecialchars($_POST['brand']) : '' ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Silakan masukkan merek kendaraan
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="model" class="form-label">Model *</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="model" 
                                               name="model" 
                                               value="<?= isset($_POST['model']) ? htmlspecialchars($_POST['model']) : '' ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Silakan masukkan model kendaraan
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="year" class="form-label">Tahun *</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="year" 
                                               name="year" 
                                               min="1900" 
                                               max="<?= date('Y') + 1 ?>"
                                               value="<?= isset($_POST['year']) ? htmlspecialchars($_POST['year']) : '' ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Silakan masukkan tahun kendaraan yang valid
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="facilities" class="form-label">Fasilitas</label>
                                        <textarea class="form-control" 
                                                  id="facilities" 
                                                  name="facilities" 
                                                  rows="4"
                                                  placeholder="Masukkan fasilitas kendaraan (satu per baris)"><?= isset($_POST['facilities']) ? htmlspecialchars($_POST['facilities']) : '' ?></textarea>
                                        <div class="form-text">
                                            Pisahkan setiap fasilitas dengan baris baru
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="gps_device_id" class="form-label">GPS Device ID</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="gps_device_id" 
                                               name="gps_device_id"
                                               value="<?= isset($_POST['gps_device_id']) ? htmlspecialchars($_POST['gps_device_id']) : '' ?>">
                                        <div class="form-text">
                                            Opsional - Masukkan ID perangkat GPS jika tersedia
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image" class="form-label">Foto Kendaraan</label>
                                        <input type="file" 
                                               class="form-control" 
                                               id="image" 
                                               name="image"
                                               accept="image/*">
                                        <div class="form-text">
                                            Format yang didukung: JPG, JPEG, PNG (Max. 5MB)
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <img id="image-preview" 
                                             src="#" 
                                             alt="Preview" 
                                             class="img-thumbnail mt-2" 
                                             style="max-width: 200px; display: none;">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="reset" class="btn btn-light me-md-2">
                                    <i class="fas fa-undo me-2"></i>
                                    Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Simpan Kendaraan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="../js/main.js"></script>

    <script>
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html>
