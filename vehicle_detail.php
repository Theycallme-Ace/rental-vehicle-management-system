<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get vehicle ID from URL
$vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$vehicle_id) {
    header('Location: index.php');
    exit();
}

// Get vehicle details
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->execute([$vehicle_id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    header('Location: index.php');
    exit();
}

// Get GPS data if available
$gpsData = null;
if (!empty($vehicle['gps_device_id'])) {
    $gpsData = fetchGPSData($vehicle['gps_device_id']);
}

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Back Button -->
    <a href="index.php" class="btn btn-outline-primary mb-4">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    <div class="row">
        <!-- Vehicle Image -->
        <div class="col-md-6 mb-4">
            <div class="position-relative">
                <img src="<?= !empty($vehicle['image']) ? 'uploads/vehicles/' . $vehicle['image'] : 
                    'https://images.pexels.com/photos/385998/pexels-photo-385998.jpeg' ?>" 
                     class="vehicle-detail-img" 
                     alt="<?= $vehicle['brand'] . ' ' . $vehicle['model'] ?>">
                <span class="vehicle-type position-absolute top-0 end-0 m-3">
                    <?= ucfirst($vehicle['type']) ?>
                </span>
            </div>
        </div>

        <!-- Vehicle Details -->
        <div class="col-md-6">
            <h1 class="mb-3">
                <?= $vehicle['brand'] . ' ' . $vehicle['model'] ?>
                <small class="text-muted">(<?= $vehicle['year'] ?>)</small>
            </h1>

            <!-- Vehicle Specifications -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Spesifikasi Kendaraan</h5>
                    <ul class="facilities-list">
                        <?php 
                        $facilities = explode("\n", $vehicle['facilities']);
                        foreach ($facilities as $facility): 
                            if (trim($facility)): 
                        ?>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <?= trim($facility) ?>
                            </li>
                        <?php 
                            endif; 
                        endforeach; 
                        ?>
                    </ul>
                </div>
            </div>

            <!-- GPS Location -->
            <?php if ($gpsData && !isset($gpsData['error'])): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-map-marker-alt text-danger"></i> 
                        Lokasi Kendaraan
                    </h5>
                    <div id="gps-data" data-vehicle-id="<?= $vehicle['id'] ?>">
                        <div class="gps-info">
                            <p class="mb-2">
                                <strong>Latitude:</strong> <?= $gpsData['latitude'] ?? 'N/A' ?>
                            </p>
                            <p class="mb-2">
                                <strong>Longitude:</strong> <?= $gpsData['longitude'] ?? 'N/A' ?>
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

            <!-- Contact/Booking Section -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Tertarik menyewa kendaraan ini?</h5>
                    <p class="mb-4">
                        Hubungi kami untuk informasi lebih lanjut dan pemesanan:
                    </p>
                    <div class="d-grid gap-2">
                        <a href="https://wa.me/6281234567890" class="btn btn-success btn-lg">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                        <a href="tel:+6281234567890" class="btn btn-primary btn-lg">
                            <i class="fas fa-phone"></i> Telepon
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Similar Vehicles -->
    <?php
    $stmt = $db->prepare("SELECT * FROM vehicles WHERE type = ? AND id != ? LIMIT 3");
    $stmt->execute([$vehicle['type'], $vehicle['id']]);
    $similar_vehicles = $stmt->fetchAll();
    
    if ($similar_vehicles):
    ?>
    <section class="mt-5">
        <h3 class="mb-4">Kendaraan Serupa</h3>
        <div class="row g-4">
            <?php foreach ($similar_vehicles as $similar): ?>
                <div class="col-md-4">
                    <div class="vehicle-card card h-100">
                        <img src="<?= !empty($similar['image']) ? 'uploads/vehicles/' . $similar['image'] : 
                            'https://images.pexels.com/photos/385998/pexels-photo-385998.jpeg' ?>" 
                             class="card-img-top" 
                             alt="<?= $similar['brand'] . ' ' . $similar['model'] ?>">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= $similar['brand'] . ' ' . $similar['model'] ?>
                                <small class="text-muted">(<?= $similar['year'] ?>)</small>
                            </h5>
                            <p class="card-text">
                                <?= substr($similar['facilities'], 0, 100) . '...' ?>
                            </p>
                            <a href="vehicle_detail.php?id=<?= $similar['id'] ?>" 
                               class="btn btn-primary">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
