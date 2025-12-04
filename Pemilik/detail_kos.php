<?php
include '../controler/pemilik/detail_kos.php';
?>
<!-- Main Content -->
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1"><i class="fas fa-home me-2"></i><?php echo htmlspecialchars($kos['nama_kos']); ?></h2>
            <p class="text-muted mb-0">
                <i class="fas fa-map-marker-alt me-1"></i>
                <?php echo htmlspecialchars($kos['daerah_nama']); ?>, <?php echo htmlspecialchars($kos['kota']); ?>
            </p>
        </div>
        <div class="btn-group">
            <a href="edit_kos.php?id=<?php echo $id; ?>" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Kos
            </a>
            <a href="kos_saya.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

  
    <div class="row g-4">
        <!-- Main Info -->
        <div class="col-lg-8">
            <!-- Status & Quick Actions -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">Status Kos</h5>
                            <div class="d-flex align-items-center">
                                <span class="badge <?php echo $kos['status'] == 'tersedia' ? 'bg-success' : 'bg-secondary'; ?> fs-6 me-3">
                                    <?php echo $kos['status'] == 'tersedia' ? 'Tersedia' : 'Tidak Tersedia'; ?>
                                </span>
                                <?php if ($kos['featured']): ?>
                                    <span class="badge bg-warning fs-6"><i class="fas fa-star me-1"></i>Featured</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <?php if ($kos['status'] == 'tersedia'): ?>
                                    
                                <?php endif; ?>
                                <a href="pemesanan.php?kos_id=<?php echo $id; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-list me-1"></i>Lihat Pemesanan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photos -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Foto Kos</h5>
                      
                    </div>
                    <?php if ($kos['foto_utama'] || !empty($foto_lainnya)): ?>
                        <div class="row g-3">
                            <?php if ($kos['foto_utama']): ?>
                                <div class="col-12">
                                    <div class="position-relative">
                                        <img src="../uploads/<?php echo htmlspecialchars($kos['foto_utama']); ?>"
                                            class="img-fluid rounded" alt="Foto Utama"
                                            style="max-height: 400px; width: 100%; object-fit: cover;">
                                        <span class="position-absolute top-0 start-0 bg-primary text-white px-2 py-1 rounded-end">
                                            Foto Utama
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($foto_lainnya)): ?>
                                <?php foreach ($foto_lainnya as $index => $foto): ?>
                                    <div class="col-md-4">
                                        <img src="../uploads/<?php echo htmlspecialchars($foto); ?>"
                                            class="img-fluid rounded" alt="Foto Kos <?php echo $index + 1; ?>"
                                            style="height: 150px; width: 100%; object-fit: cover;">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 border rounded">
                            <i class="fas fa-image fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">Belum ada foto kos</p>
                            <a href="edit_kos.php?id=<?php echo $id; ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Tambah Foto
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Deskripsi Kos</h5>
                    <p class="card-text">
                        <?php echo $kos['deskripsi'] ? nl2br(htmlspecialchars($kos['deskripsi'])) :
                            '<span class="text-muted fst-italic">Belum ada deskripsi. 
                            <a href="edit_kos.php?id=' . $id . '">Tambahkan deskripsi</a></span>'; ?>
                    </p>
                </div>
            </div>

            <!-- Address -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Alamat Lengkap</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($kos['alamat'])); ?></p>
                    <?php if ($kos['daerah_nama']): ?>
                        <p class="text-muted mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo htmlspecialchars($kos['daerah_nama']); ?>, <?php echo htmlspecialchars($kos['kota']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Pricing & Basic Info -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informasi Harga & Spesifikasi</h5>

                    <div class="text-center mb-4">
                        <h3 class="text-primary mb-1">Rp <?php echo number_format($kos['harga_bulanan'], 0, ',', '.'); ?></h3>
                        <small class="text-muted">per bulan</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-2 border rounded">
                                <i class="fas fa-<?php echo $kos['tipe_kos'] == 'putra' ? 'male' : ($kos['tipe_kos'] == 'putri' ? 'female' : 'users'); ?> fa-2x text-primary mb-2"></i>
                                <p class="mb-1 fw-bold"><?php echo ucfirst($kos['tipe_kos']); ?></p>
                                <small class="text-muted">Tipe Kos</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 border rounded">
                                <i class="fas fa-bath fa-2x text-info mb-2"></i>
                                <p class="mb-1 fw-bold"><?php echo ucfirst($kos['kamar_mandi']); ?></p>
                                <small class="text-muted">Kamar Mandi</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 border rounded">
                                <i class="fas fa-ruler-combined fa-2x text-success mb-2"></i>
                                <p class="mb-1 fw-bold"><?php echo htmlspecialchars($kos['ukuran_kamar']); ?></p>
                                <small class="text-muted">Ukuran Kamar</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 border rounded">
                                <i class="fas fa-eye fa-2x text-warning mb-2"></i>
                                <p class="mb-1 fw-bold"><?php echo number_format($kos['views']); ?></p>
                                <small class="text-muted">Total Views</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Facilities -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Fasilitas</h5>
                        
                    </div>
                    <?php if (!empty($fasilitas)): ?>
                        <div class="row g-2">
                            <?php foreach ($fasilitas as $fasilitas_item): ?>
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-2 border rounded">
                                        <i class="fas fa-check text-success me-3"></i>
                                        <span><?php echo htmlspecialchars($fasilitas_item); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3 border rounded">
                            <i class="fas fa-list fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-2">Belum ada fasilitas</p>
                            <a href="edit_kos.php?id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Tambah Fasilitas
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Meta Info -->
            <div class="card border-0 shadow">
                <div class="card-body">
                    <h5 class="card-title">Informasi Sistem</h5>
                    <div class="row g-2">
                        <div class="col-12">
                            <small class="text-muted">ID Kos</small>
                            <p class="mb-2"><code><?php echo $kos['id']; ?></code></p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Dibuat</small>
                            <p class="mb-2"><?php echo date('d/m/Y H:i', strtotime($kos['created_at'])); ?></p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Terakhir Update</small>
                            <p class="mb-0"><?php echo date('d/m/Y H:i', strtotime($kos['updated_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bagikan Kos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Salin link berikut untuk membagikan kos Anda:</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="shareLink"
                        value="<?php echo "http://" . $_SERVER['HTTP_HOST'] . '/inkos/detail_kos.php?id=' . $id; ?>" readonly>
                    <button class="btn btn-primary" type="button" onclick="copyShareLink()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <div class="alert alert-success d-none" id="copySuccess">
                    Link berhasil disalin!
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyShareLink() {
        const shareLink = document.getElementById('shareLink');
        shareLink.select();
        shareLink.setSelectionRange(0, 99999);
        document.execCommand('copy');

        const copySuccess = document.getElementById('copySuccess');
        copySuccess.classList.remove('d-none');
        setTimeout(() => {
            copySuccess.classList.add('d-none');
        }, 3000);
    }
</script>

<?php include '../includes/footer/footer.php'; ?>