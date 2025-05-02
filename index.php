<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get available vehicles
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM vehicles ORDER BY created_at DESC");
$stmt->execute();
$vehicles = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section bg-dark text-light py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold mb-4">Rental Bis & Mobil Terpercaya</h1>
                <p class="lead mb-4">
                    Solusi transportasi terbaik untuk perjalanan Anda. 
                    Tersedia berbagai pilihan kendaraan dengan kondisi prima dan pelayanan profesional.
                </p>
                <a href="#vehicles" class="btn btn-primary btn-lg">
                    Lihat Kendaraan
                </a>
            </div>
            <div class="col-md-6">
                <img src="https://images.pexels.com/photos/385998/pexels-photo-385998.jpeg" 
                     alt="Rental Kendaraan" 
                     class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Section -->
<section id="vehicles" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Kendaraan Tersedia</h2>
        
        <!-- Filter Buttons -->
        <div class="text-center mb-4">
            <button class="btn btn-outline-primary me-2 mb-2 filter-btn active" data-filter="all">
                Semua
            </button>
            <button class="btn btn-outline-primary me-2 mb-2 filter-btn" data-filter="bus">
                Bus
            </button>
            <button class="btn btn-outline-primary mb-2 filter-btn" data-filter="car">
                Mobil
            </button>
        </div>

        <div class="row g-4">
            <?php foreach ($vehicles as $vehicle): ?>
                <div class="col-md-6 col-lg-4 vehicle-item" data-type="<?= $vehicle['type'] ?>">
                    <div class="vehicle-card card h-100">
                        <span class="vehicle-type">
                            <?= ucfirst($vehicle['type']) ?>
                        </span>
                        <img src="<?= !empty($vehicle['image']) ? 'uploads/vehicles/' . $vehicle['image'] : 
                            'https://images.pexels.com/photos/385998/pexels-photo-385998.jpeg' ?>" 
                             class="card-img-top" 
                             alt="<?= $vehicle['brand'] . ' ' . $vehicle['model'] ?>">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= $vehicle['brand'] . ' ' . $vehicle['model'] ?> 
                                <small class="text-muted">(<?= $vehicle['year'] ?>)</small>
                            </h5>
                            <p class="card-text">
                                <?= substr($vehicle['facilities'], 0, 100) . '...' ?>
                            </p>
                            <a href="admin/login.php" 
                               class="btn btn-primary">
                                Login Admin
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($vehicles)): ?>
            <div class="text-center py-5">
                <i class="fas fa-car fa-3x text-muted mb-3"></i>
                <h3>Tidak ada kendaraan tersedia saat ini</h3>
                <p class="text-muted">Silakan cek kembali nanti</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-5">Mengapa Memilih Kami?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <i class="fas fa-car-alt fa-3x text-primary mb-3"></i>
                    <h4>Kendaraan Berkualitas</h4>
                    <p class="text-muted">
                        Armada kendaraan terawat dengan baik dan selalu dalam kondisi prima
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <i class="fas fa-map-marked-alt fa-3x text-primary mb-3"></i>
                    <h4>GPS Tracking</h4>
                    <p class="text-muted">
                        Pantau lokasi kendaraan secara real-time melalui sistem GPS
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                    <h4>Layanan 24/7</h4>
                    <p class="text-muted">
                        Tim support kami siap membantu Anda kapan saja
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h2 class="mb-4">Siap untuk memesan?</h2>
                <p class="lead mb-4">
                    Hubungi kami sekarang untuk mendapatkan penawaran terbaik
                </p>
                <a href="contact.php" class="btn btn-primary btn-lg">
                    Hubungi Kami
                </a>
            </div>
        </div>
    </div>
</section>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const vehicleItems = document.querySelectorAll('.vehicle-item');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            btn.classList.add('active');
            
            const filterValue = btn.getAttribute('data-filter');
            
            vehicleItems.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-type') === filterValue) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
