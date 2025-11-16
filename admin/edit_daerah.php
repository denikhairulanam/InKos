<?php
include '../includes/auth.php';
include '../controler/admin/edit_daerah.php';
?>
<!-- Main Content -->
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3"><i class="fas fa-edit me-2"></i>Edit Daerah</h2>
        <a href="daerah.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow">
        <div class="card-header bg-white">
            <h5 class="mb-0">Edit Data Daerah</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="edit_daerah.php?id=<?php echo $id; ?>" id="daerahForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nama" class="form-label">Nama Daerah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required
                            value="<?php echo htmlspecialchars($daerah['nama']); ?>"
                            placeholder="Contoh: Paal Merah, Telanaipura">
                    </div>

                    <div class="col-md-6">
                        <label for="kota" class="form-label">Kota <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kota" name="kota" required
                            value="<?php echo htmlspecialchars($daerah['kota']); ?>"
                            placeholder="Contoh: Jambi">
                    </div>

                    <div class="col-md-6">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" class="form-control" id="latitude" name="latitude"
                            value="<?php echo $daerah['latitude'] !== null ? htmlspecialchars($daerah['latitude']) : ''; ?>"
                            placeholder="Contoh: -1.610000" pattern="-?\d+(\.\d+)?">
                        <div class="form-text">Koordinat latitude (opsional). Format: -1.610000</div>
                    </div>

                    <div class="col-md-6">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" class="form-control" id="longitude" name="longitude"
                            value="<?php echo $daerah['longitude'] !== null ? htmlspecialchars($daerah['longitude']) : ''; ?>"
                            placeholder="Contoh: 103.610000" pattern="-?\d+(\.\d+)?">
                        <div class="form-text">Koordinat longitude (opsional). Format: 103.610000</div>
                    </div>

                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Informasi</h6>
                                <p class="mb-1"><strong>Dibuat:</strong> <?php echo date('d/m/Y H:i', strtotime($daerah['created_at'])); ?></p>
                                <p class="mb-0"><strong>ID:</strong> <?php echo $daerah['id']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="daerah.php" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Perbarui Daerah
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
</div>

<script>
    <?php include '../JavaScript/admin/edit_daerah.js'; ?>
</script>

<?php include '../includes/footer/footer.php'; ?>