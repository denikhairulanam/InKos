<?php
// Mulai output buffering di PALING ATAS
ob_start();

include '../includes/auth.php';
include '../controler/admin/tambah_kos.php';
?>

<!-- Main Content -->
<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark">Tambah Kos Baru</h1>
            <p class="text-muted mb-0">Tambahkan kos baru ke sistem INKOS</p>
        </div>
        <a href="kos.php" class="btn btn-outline-secondary">
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
                <i class="fas fa-building me-2 text-primary"></i>
                Form Tambah Kos
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="tambah_kos.php" id="kosForm" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- Basic Information -->
                    <div class="col-12">
                        <h6 class="text-primary mb-3 border-bottom pb-2">Informasi Dasar</h6>
                    </div>

                    <div class="col-md-6">
                        <label for="nama_kos" class="form-label">Nama Kos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kos" name="nama_kos" required
                            placeholder="Contoh: Kos Mawar Indah"
                            value="<?php echo isset($_POST['nama_kos']) ? htmlspecialchars($_POST['nama_kos']) : ''; ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Pemilik Kos <span class="text-danger">*</span></label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Pilih Pemilik</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>"
                                    <?php echo (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"
                            placeholder="Deskripsi lengkap tentang kos..."><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
                    </div>

                    <!-- Photo Upload -->
                    <div class="col-12">
                        <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">Foto Kos</h6>
                    </div>

                    <div class="col-md-6">
                        <label for="foto_utama" class="form-label">Foto Utama <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="foto_utama" name="foto_utama" accept="image/*" required>
                        <div class="form-text">
                            Format: JPG, PNG, GIF (Maks. 5MB). Foto ini akan menjadi gambar utama kos.
                        </div>
                        <div id="foto_utama_preview" class="mt-2"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="foto_lainnya" class="form-label">Foto Lainnya</label>
                        <input type="file" class="form-control" id="foto_lainnya" name="foto_lainnya[]"
                            multiple accept="image/*">
                        <div class="form-text">
                            Pilih multiple foto (Maks. 5MB per foto). Tekan Ctrl untuk memilih multiple file.
                        </div>
                        <div id="foto_lainnya_preview" class="mt-2"></div>
                    </div>

                    <!-- Location Information -->
                    <div class="col-12">
                        <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">Informasi Lokasi</h6>
                    </div>

                    <div class="col-md-6">
                        <label for="daerah_id" class="form-label">Daerah</label>
                        <select class="form-select" id="daerah_id" name="daerah_id">
                            <option value="">Pilih Daerah</option>
                            <?php foreach ($daerah_list as $daerah): ?>
                                <option value="<?php echo $daerah['id']; ?>"
                                    <?php echo (isset($_POST['daerah_id']) && $_POST['daerah_id'] == $daerah['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($daerah['nama']); ?> - <?php echo htmlspecialchars($daerah['kota']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="harga_bulanan" class="form-label">Harga Bulanan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="harga_bulanan" name="harga_bulanan" required
                                placeholder="Contoh: 1500000" min="0"
                                value="<?php echo isset($_POST['harga_bulanan']) ? htmlspecialchars($_POST['harga_bulanan']) : ''; ?>">
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required
                            placeholder="Alamat lengkap kos..."><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                    </div>

                    <!-- Room Information -->
                    <div class="col-12">
                        <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">Informasi Kamar</h6>
                    </div>

                    <div class="col-md-4">
                        <label for="tipe_kos" class="form-label">Tipe Kos <span class="text-danger">*</span></label>
                        <select class="form-select" id="tipe_kos" name="tipe_kos" required>
                            <option value="">Pilih Tipe</option>
                            <option value="putra" <?php echo (isset($_POST['tipe_kos']) && $_POST['tipe_kos'] == 'putra') ? 'selected' : ''; ?>>Putra</option>
                            <option value="putri" <?php echo (isset($_POST['tipe_kos']) && $_POST['tipe_kos'] == 'putri') ? 'selected' : ''; ?>>Putri</option>
                            <option value="campur" <?php echo (isset($_POST['tipe_kos']) && $_POST['tipe_kos'] == 'campur') ? 'selected' : ''; ?>>Campur</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="ukuran_kamar" class="form-label">Ukuran Kamar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ukuran_kamar" name="ukuran_kamar" required
                            placeholder="Contoh: 3x4 meter"
                            value="<?php echo isset($_POST['ukuran_kamar']) ? htmlspecialchars($_POST['ukuran_kamar']) : ''; ?>">
                    </div>

                    <div class="col-md-4">
                        <label for="kamar_mandi" class="form-label">Kamar Mandi <span class="text-danger">*</span></label>
                        <select class="form-select" id="kamar_mandi" name="kamar_mandi" required>
                            <option value="">Pilih Tipe</option>
                            <option value="dalam" <?php echo (isset($_POST['kamar_mandi']) && $_POST['kamar_mandi'] == 'dalam') ? 'selected' : ''; ?>>Dalam</option>
                            <option value="luar" <?php echo (isset($_POST['kamar_mandi']) && $_POST['kamar_mandi'] == 'luar') ? 'selected' : ''; ?>>Luar</option>
                        </select>
                    </div>

                    <!-- Facilities -->
                    <div class="col-12">
                        <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">Fasilitas</h6>
                        <div class="row g-2">
                            <?php foreach ($common_facilities as $facility): ?>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fasilitas[]"
                                            value="<?php echo htmlspecialchars($facility); ?>"
                                            id="facility_<?php echo preg_replace('/[^a-zA-Z0-9]/', '_', $facility); ?>"
                                            <?php echo (isset($_POST['fasilitas']) && in_array($facility, $_POST['fasilitas'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="facility_<?php echo preg_replace('/[^a-zA-Z0-9]/', '_', $facility); ?>">
                                            <?php echo htmlspecialchars($facility); ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="col-12">
                        <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">Informasi Tambahan</h6>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="tersedia" <?php echo (isset($_POST['status']) && $_POST['status'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="tidak_tersedia" <?php echo (isset($_POST['status']) && $_POST['status'] == 'tidak_tersedia') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check mt-4 pt-2">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1"
                                <?php echo (isset($_POST['featured']) && $_POST['featured'] == '1') ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-medium" for="featured">
                                Jadikan Featured
                            </label>
                            <div class="form-text">Kos featured akan ditampilkan di halaman utama</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-12">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-4 mt-3">
                            <a href="kos.php" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Kos
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    <?php include '../JavaScript/admin/tambah_kos.js'; ?>
</script>
<?php
include '../includes/footer/footer.php';
ob_end_flush();
?>