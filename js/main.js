// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // File input custom text
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
    });

    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Delete confirmation
    document.querySelectorAll('.delete-confirm').forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus item ini?')) {
                e.preventDefault();
            }
        });
    });

    // GPS data refresh (if on vehicle detail page)
    const gpsContainer = document.getElementById('gps-data');
    if (gpsContainer) {
        const vehicleId = gpsContainer.dataset.vehicleId;
        const refreshGPS = async () => {
            try {
                const response = await fetch(`/api/gps.php?vehicle_id=${vehicleId}`);
                const data = await response.json();
                
                if (data.error) {
                    gpsContainer.innerHTML = `<div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> ${data.error}
                    </div>`;
                } else {
                    gpsContainer.innerHTML = `
                        <div class="gps-card">
                            <h5><i class="fas fa-map-marker-alt"></i> Lokasi Kendaraan</h5>
                            <p>Latitude: ${data.latitude}</p>
                            <p>Longitude: ${data.longitude}</p>
                            <p>Terakhir diperbarui: ${data.last_update}</p>
                        </div>`;
                }
            } catch (error) {
                console.error('GPS refresh error:', error);
                gpsContainer.innerHTML = `<div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Gagal memperbarui data GPS
                </div>`;
            }
        };

        // Refresh GPS data every 30 seconds
        setInterval(refreshGPS, 30000);
        refreshGPS(); // Initial load
    }

    // Image preview before upload
    document.querySelectorAll('.image-preview-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const preview = document.getElementById(this.dataset.preview);
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Toast notifications
    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) return;

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                    data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }

    // Handle AJAX form submissions
    document.querySelectorAll('form.ajax-form').forEach(function(form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Memproses...';

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message || 'Berhasil!');
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    }
                } else {
                    showToast(result.message || 'Terjadi kesalahan.', 'danger');
                }
            } catch (error) {
                console.error('Form submission error:', error);
                showToast('Terjadi kesalahan sistem.', 'danger');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    });

    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.admin-sidebar').classList.toggle('show');
        });
    }
});
