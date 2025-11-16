<?php
include '../controler/admin/edit_kos.php';
include '../includes/header/admin_header.php';
?>
<!-- Main Content -->
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3"><i class="fas fa-edit me-2"></i>Edit Kos</h2>
        <div class="btn-group">
            <a href="detail_kos.php?id=<?php echo $id; ?>" class="btn btn-outline-info">
                <i class="fas fa-eye me-2"></i>Detail
            </a>
            <a href="kos.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
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
            <h5 class="mb-0">Edit Data Kos</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="edit_kos.php?id=<?php echo $id; ?>" id="kosForm">
                <div class="row g-3">
                    <!-- Basic Information -->
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Informasi Dasar</h6>
                    </div>

                    <div class="col-md-6">
                        <label for="nama_kos" class="form-label">Nama Kos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kos" name="nama_kos" required
                            value="<?php echo htmlspecialchars($kos['nama_kos']); ?>"
                            placeholder="Contoh: Kos Mawar Indah">
                    </div>

                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Pemilik Kos <span class="text-danger">*</span></label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Pilih Pemilik</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>"
                                    <?php echo $user['id'] == $kos['user_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"
                            placeholder="Deskripsi lengkap tentang kos..."><?php echo htmlspecialchars($kos['deskripsi']); ?></textarea>
                    </div>

                    <!-- Location Information -->
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mt-4">Informasi Lokasi</h6>
                    </div>

                    <div class="col-md-6">
                        <label for="daerah_id" class="form-label">Daerah</label>
                        <select class="form-select" id="daerah_id" name="daerah_id">
                            <option value="">Pilih Daerah</option>
                            <?php foreach ($daerah_list as $daerah): ?>
                                <option value="<?php echo $daerah['id']; ?>"
                                    <?php echo $daerah['id'] == $kos['daerah_id'] ? 'selected' : ''; ?>>
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
                                value="<?php echo $kos['harga_bulanan']; ?>"
                                placeholder="Contoh: 1500000" min="0">
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required
                            placeholder="Alamat lengkap kos..."><?php echo htmlspecialchars($kos['alamat']); ?></textarea>
                    </div>

                    <!-- Room Information -->
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mt-4">Informasi Kamar</h6>
                    </div>

                    <div class="col-md-4">
                        <label for="tipe_kos" class="form-label">Tipe Kos <span class="text-danger">*</span></label>
                        <select class="form-select" id="tipe_kos" name="tipe_kos" required>
                            <option value="">Pilih Tipe</option>
                            <option value="putra" <?php echo $kos['tipe_kos'] == 'putra' ? 'selected' : ''; ?>>Putra</option>
                            <option value="putri" <?php echo $kos['tipe_kos'] == 'putri' ? 'selected' : ''; ?>>Putri</option>
                            <option value="campur" <?php echo $kos['tipe_kos'] == 'campur' ? 'selected' : ''; ?>>Campur</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="ukuran_kamar" class="form-label">Ukuran Kamar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ukuran_kamar" name="ukuran_kamar" required
                            value="<?php echo htmlspecialchars($kos['ukuran_kamar']); ?>"
                            placeholder="Contoh: 3x4 meter">
                    </div>

                    <div class="col-md-4">
                        <label for="kamar_mandi" class="form-label">Kamar Mandi <span class="text-danger">*</span></label>
                        <select class="form-select" id="kamar_mandi" name="kamar_mandi" required>
                            <option value="">Pilih Tipe</option>
                            <option value="dalam" <?php echo $kos['kamar_mandi'] == 'dalam' ? 'selected' : ''; ?>>Dalam</option>
                            <option value="luar" <?php echo $kos['kamar_mandi'] == 'luar' ? 'selected' : ''; ?>>Luar</option>
                        </select>
                    </div>

                    <!-- Facilities -->
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mt-4">Fasilitas</h6>
                        <div class="row g-2" id="facilities_container">
                            <?php foreach ($common_facilities as $facility): ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fasilitas[]"
                                            value="<?php echo htmlspecialchars($facility); ?>"
                                            id="facility_<?php echo preg_replace('/[^a-zA-Z0-9]/', '_', $facility); ?>"
                                            <?php echo in_array($facility, $fasilitas) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="facility_<?php echo preg_replace('/[^a-zA-Z0-9]/', '_', $facility); ?>">
                                            <?php echo htmlspecialchars($facility); ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Custom Facility Input -->
                        <div class="row mt-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="custom_facility"
                                    placeholder="Tambah fasilitas custom...">
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-primary w-100" onclick="addCustomFacility()">
                                    <i class="fas fa-plus me-2"></i>Tambah
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mt-4">Informasi Tambahan</h6>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="tersedia" <?php echo $kos['status'] == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="tidak_tersedia" <?php echo $kos['status'] == 'tidak_tersedia' ? 'selected' : ''; ?>>Tidak Tersedia</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1"
                                <?php echo $kos['featured'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="featured">
                                Jadikan Featured
                            </label>
                            <div class="form-text">Kos featured akan ditampilkan di halaman utama</div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Informasi Sistem</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <small class="text-muted">ID Kos</small>
                                        <p class="mb-2"><code><?php echo $kos['id']; ?></code></p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Views</small>
                                        <p class="mb-2"><?php echo number_format($kos['views']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Dibuat</small>
                                        <p class="mb-2"><?php echo date('d/m/Y H:i', strtotime($kos['created_at'])); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Terakhir Update</small>
                                        <p class="mb-0"><?php echo date('d/m/Y H:i', strtotime($kos['updated_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="kos.php" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Perbarui Kos
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
    <?php include '../JavaScript/admin/edit_kos.js'; ?>
</script>

<?php include '../includes/footer/footer.php'; ?>