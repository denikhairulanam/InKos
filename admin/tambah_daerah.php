<?php
include '../includes/auth.php';
checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Tambah Daerah - INKOS";
include '../includes/admin_header.php';

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $kota = $_POST['kota'];

    // Handle empty latitude/longitude - convert to NULL
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    try {
        $query = "INSERT INTO daerah (nama, kota, latitude, longitude) 
                  VALUES (:nama, :kota, :latitude, :longitude)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':kota', $kota);
        $stmt->bindParam(':latitude', $latitude);
        $stmt->bindParam(':longitude', $longitude);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Daerah berhasil ditambahkan";
            header('Location: daerah.php');
            exit();
        } else {
            $error_message = "Gagal menambahkan daerah";
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!-- Main Content -->
<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark">Tambah Daerah Baru</h1>
            <p class="text-muted mb-0">Tambahkan daerah baru ke sistem INKOS</p>
        </div>
        <a href="daerah.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-dark">
                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                Form Tambah Daerah
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="tambah_daerah.php" id="daerahForm">
                <div class="row g-3">
                    <!-- Nama Daerah -->
                    <div class="col-md-6">
                        <label for="nama" class="form-label">Nama Daerah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required
                            placeholder="Contoh: Paal Merah, Telanaipura"
                            value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                    </div>

                    <!-- Kota -->
                    <div class="col-md-6">
                        <label for="kota" class="form-label">Kota <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kota" name="kota" required
                            placeholder="Contoh: Jambi"
                            value="<?php echo isset($_POST['kota']) ? htmlspecialchars($_POST['kota']) : 'Jambi'; ?>">
                    </div>

                    <!-- Latitude -->
                    <div class="col-md-6">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" class="form-control" id="latitude" name="latitude"
                            placeholder="Contoh: -1.610000" pattern="-?\d+(\.\d+)?"
                            value="<?php echo isset($_POST['latitude']) ? htmlspecialchars($_POST['latitude']) : ''; ?>">
                        <div class="form-text">Koordinat latitude (opsional). Format: -1.610000</div>
                    </div>

                    <!-- Longitude -->
                    <div class="col-md-6">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" class="form-control" id="longitude" name="longitude"
                            placeholder="Contoh: 103.610000" pattern="-?\d+(\.\d+)?"
                            value="<?php echo isset($_POST['longitude']) ? htmlspecialchars($_POST['longitude']) : ''; ?>">
                        <div class="form-text">Koordinat longitude (opsional). Format: 103.610000</div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-12">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-4">
                            <a href="daerah.php" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Daerah
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Client-side validation untuk koordinat
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('daerahForm');
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');

        function isValidCoordinate(coord, isLatitude) {
            const pattern = /^-?\d+(\.\d+)?$/;
            if (!pattern.test(coord)) return false;

            const num = parseFloat(coord);
            if (isLatitude) {
                return num >= -90 && num <= 90;
            } else {
                return num >= -180 && num <= 180;
            }
        }

        // Real-time validation untuk latitude
        latitudeInput.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && !isValidCoordinate(value, true)) {
                this.classList.add('is-invalid');
                this.setCustomValidity('Format latitude tidak valid. Contoh: -1.610000');
            } else {
                this.classList.remove('is-invalid');
                this.setCustomValidity('');
            }
        });

        // Real-time validation untuk longitude
        longitudeInput.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && !isValidCoordinate(value, false)) {
                this.classList.add('is-invalid');
                this.setCustomValidity('Format longitude tidak valid. Contoh: 103.610000');
            } else {
                this.classList.remove('is-invalid');
                this.setCustomValidity('');
            }
        });

        // Form submission validation
        form.addEventListener('submit', function(e) {
            const latitude = latitudeInput.value.trim();
            const longitude = longitudeInput.value.trim();

            let isValid = true;

            // Validasi format latitude
            if (latitude && !isValidCoordinate(latitude, true)) {
                e.preventDefault();
                latitudeInput.classList.add('is-invalid');
                latitudeInput.setCustomValidity('Format latitude tidak valid. Contoh: -1.610000');
                latitudeInput.focus();
                isValid = false;
            }

            // Validasi format longitude
            if (longitude && !isValidCoordinate(longitude, false)) {
                e.preventDefault();
                longitudeInput.classList.add('is-invalid');
                longitudeInput.setCustomValidity('Format longitude tidak valid. Contoh: 103.610000');
                if (isValid) {
                    longitudeInput.focus();
                }
                isValid = false;
            }

            if (!isValid) {
                // Scroll to first error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }
        });

        // Clear validation on input
        latitudeInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            this.setCustomValidity('');
        });

        longitudeInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            this.setCustomValidity('');
        });
    });
</script>

<?php include '../includes/footer.php'; ?>