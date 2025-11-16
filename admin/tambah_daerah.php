<?php
include '../includes/auth.php';
include '../controler/admin/tambah_daerah.php';
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
    <?php include '../JavaScript/admin/tambah_daerah.js'; ?>
</script>

<?php include '../includes/footer/footer.php'; ?>