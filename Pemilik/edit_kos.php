<?php
ob_start();
include '../includes/header/pemilik_header.php';
include '../controler/pemilik/edit_kos.php';
?>
<div class="container px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3"><i class="fas fa-edit me-2"></i>Edit Kos</h2>
        <div class="btn-group">
            <a href="detail_kos.php?id=<?php echo $id; ?>" class="btn btn-outline-info">
                <i class="fas fa-eye me-2"></i>Detail
            </a>
            <a href="kos_saya.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error_message'];
            unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Main Form -->
    <form method="POST" action="edit_kos.php?id=<?php echo $id; ?>" enctype="multipart/form-data" id="editKosForm">
        <div class="card border-0 shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Data Kos</h5>
            </div>
            <div class="card-body">
                <!-- Informasi Dasar -->
                <div class="mb-5">
                    <h6 class="border-bottom pb-2 mb-3">Informasi Dasar</h6>

                    <div class="row g-3">
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
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"
                                placeholder="Deskripsi lengkap tentang kos..."><?php echo htmlspecialchars($kos['deskripsi']); ?></textarea>
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
                            <textarea class="form-control" id="alamat" name="alamat" rows="2" required
                                placeholder="Alamat lengkap kos..."><?php echo htmlspecialchars($kos['alamat']); ?></textarea>
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
                            </div>
                        </div>

                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mt-3 mb-3">Fasilitas</h6>
                            <div class="row g-2">
                                <?php foreach ($common_facilities as $facility): ?>
                                    <div class="col-md-3 col-6">
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
                        </div>
                    </div>
                </div>

                <!-- Semua Foto dalam Satu Card -->
                <div class="mb-5">
                    <h6 class="border-bottom pb-2 mb-3">Foto Kos</h6>

                    <div class="card">
                        <div class="card-body">
                            <!-- Foto Saat Ini -->
                            <div class="mb-5">
                                <h6 class="mb-3">Foto Saat Ini</h6>

                                <?php if (!empty($semua_foto)): ?>
                                    <div class="row g-3">
                                        <?php foreach ($semua_foto as $index => $foto): ?>
                                            <div class="col-md-3 col-6">
                                                <div class="card h-100">
                                                    <div class="card-img-top position-relative" style="height: 150px; overflow: hidden;">
                                                        <img src="../uploads/<?php echo htmlspecialchars($foto['filename']); ?>"
                                                            class="w-100 h-100"
                                                            alt="Foto <?php echo $index + 1; ?>"
                                                            style="object-fit: cover;">
                                                        <?php if ($foto['is_main']): ?>
                                                            <span class="badge bg-primary position-absolute top-0 start-0 m-2">
                                                                <i class="fas fa-star me-1"></i>Utama
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="card-body p-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input keep-photo-checkbox"
                                                                type="checkbox"
                                                                name="keep_photos[]"
                                                                value="<?php echo htmlspecialchars($foto['filename']); ?>"
                                                                id="keep_photo_<?php echo $index; ?>"
                                                                checked
                                                                data-is-main="<?php echo $foto['is_main'] ? 'true' : 'false'; ?>"
                                                                data-photo-name="<?php echo htmlspecialchars($foto['filename']); ?>">
                                                            <label class="form-check-label" for="keep_photo_<?php echo $index; ?>">
                                                                Simpan foto
                                                            </label>
                                                        </div>
                                                        <small class="text-muted d-block"></small>
                                                       
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="mt-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectAllPhotos()">
                                            <i class="fas fa-check-square me-1"></i>Simpan Semua
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="deselectAllPhotos()">
                                            <i class="fas fa-square me-1"></i>Hapus Semua
                                        </button>
                                        <span class="ms-3 text-muted">Foto yang tidak dicentang akan dihapus</span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4 bg-light rounded">
                                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Belum ada foto untuk kos ini</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <hr class="my-4">

                            <!-- Tambah Foto Baru -->
                            <div>
                                <h6 class="mb-3">Tambah Foto Baru</h6>

                                <div class="mb-3">
                                    <label for="new_fotos" class="form-label">Pilih Foto Baru</label>
                                    <input type="file" class="form-control" id="new_fotos" name="new_fotos[]" multiple accept="image/*">
                                    <div class="form-text">
                                        Bisa pilih beberapa foto sekaligus. Maksimal 2MB per foto.
                                    </div>
                                </div>

                                <!-- Preview foto baru -->
                                <div id="preview-container" class="d-none">
                                    <h6 class="mb-3">Preview Foto Baru:</h6>
                                    <div class="row g-3" id="preview-photos"></div>
                                </div>

                                <!-- Pesan info -->
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Info:</strong> Foto yang tidak dicentang akan dihapus. Foto baru akan ditambahkan.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Sistem -->
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Informasi Sistem</h6>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <small class="text-muted">ID Kos</small>
                                        <p class="mb-2"><code><?php echo $kos['id']; ?></code></p>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Views</small>
                                        <p class="mb-2"><?php echo number_format($kos['views']); ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Terakhir Update</small>
                                        <p class="mb-0"><?php echo date('d/m/Y H:i', strtotime($kos['updated_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="card-footer bg-white">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="kos_saya.php" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-2"></i>Batal
                    </a>
                    <button type="submit" class="btn btn-primary" id="saveButton">
                        <i class="fas fa-save me-2"></i>Perbarui Kos
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<script src="../JavaScript/pemilik/edit_kos.js"></script>
<script>
    // Data untuk JavaScript
    window.kosData = {
        totalPhotos: <?php echo count($semua_foto); ?>,
        hasMainPhoto: <?php echo !empty($kos['foto_utama']) ? 'true' : 'false'; ?>,
        mainPhotoFilename: "<?php echo $kos['foto_utama']; ?>"
    };
</script>
<?php
include '../includes/footer/pemilik_footer.php';
?>