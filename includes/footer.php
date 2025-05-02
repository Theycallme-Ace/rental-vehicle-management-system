</div> <!-- End of Main Content Container -->

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5>Rental Bis & Mobil</h5>
                    <p class="text-muted">
                        Solusi terpercaya untuk kebutuhan transportasi Anda. 
                        Menyediakan layanan rental bus dan mobil dengan kualitas terbaik.
                    </p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Kontak Kami</h5>
                    <ul class="list-unstyled text-muted">
                        <li><i class="fas fa-phone me-2"></i> +62 123 4567 890</li>
                        <li><i class="fas fa-envelope me-2"></i> info@rentalbis.com</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> Jl. Contoh No. 123, Jakarta</li>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Ikuti Kami</h5>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <hr class="mt-4">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-muted mb-0">
                        &copy; <?= date('Y') ?> Rental Bis & Mobil. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (for AJAX functionality) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?= BASE_URL ?>/js/main.js"></script>

    <?php if (isset($page_specific_js)): ?>
        <!-- Page Specific JavaScript -->
        <script src="<?= BASE_URL ?>/js/<?= $page_specific_js ?>"></script>
    <?php endif; ?>

</body>
</html>
