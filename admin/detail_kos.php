<?php
include '../includes/auth.php';
include '../includes/header/admin_header.php';
include '../controler/admin/detail_kos.php';
?>

<!-- Main Content -->
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3"><i class="fas fa-eye me-2"></i>Detail Kos</h2>
        <div class="btn-group">
            <a href="edit_kos.php?id=<?php echo $id; ?>" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="kos.php" class="btn btn-outline-secondary">
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

    <div class="row g-4">
        <!-- Main Info -->
        <div class="col-lg-8">
            <!-- Photos -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Foto Kos</h5>
                    <?php if ($kos['foto_utama'] || !empty($foto_lainnya)): ?>
                        <div class="row g-3">
                            <?php if ($kos['foto_utama']): ?>
                                <div class="col-12">
                                    <img src="../uploads/<?php echo htmlspecialchars($kos['foto_utama']); ?>"
                                        class="img-fluid rounded" alt="Foto Utama" style="max-height: 400px; width: 100%; object-fit: cover;">
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($foto_lainnya)): ?>
                                <?php foreach ($foto_lainnya as $foto): ?>
                                    <div class="col-md-4">
                                        <img src="../uploads/<?php echo htmlspecialchars($foto); ?>"
                                            class="img-fluid rounded" alt="Foto Kos" style="height: 150px; width: 100%; object-fit: cover;">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-image fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada foto</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Deskripsi</h5>
                    <p class="card-text"><?php echo $kos['deskripsi'] ? nl2br(htmlspecialchars($kos['deskripsi'])) : '<span class="text-muted">Tidak ada deskripsi</span>'; ?></p>
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
            <!-- Basic Info -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">Informasi Kos</h5>
                        <div>
                            <?php if ($kos['featured']): ?>
                                <span class="badge bg-warning"><i class="fas fa-star me-1"></i>Featured</span>
                            <?php endif; ?>
                            <span class="badge <?php echo $kos['status'] == 'tersedia' ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $kos['status'] == 'tersedia' ? 'Tersedia' : 'Tidak Tersedia'; ?>
                            </span>
                        </div>
                    </div>

                    <h4 class="text-primary mb-3">Rp <?php echo number_format($kos['harga_bulanan'], 0, ',', '.'); ?> <small class="text-muted">/bulan</small></h4>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <small class="text-muted">Tipe Kos</small>
                            <p class="mb-0">
                                <span class="badge 
                                            <?php echo $kos['tipe_kos'] == 'putra' ? 'bg-primary' : ($kos['tipe_kos'] == 'putri' ? 'bg-danger' : 'bg-success'); ?>">
                                    <?php echo ucfirst($kos['tipe_kos']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Kamar Mandi</small>
                            <p class="mb-0">
                                <span class="badge bg-info"><?php echo ucfirst($kos['kamar_mandi']); ?></span>
                            </p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Ukuran Kamar</small>
                            <p class="mb-0"><strong><?php echo htmlspecialchars($kos['ukuran_kamar']); ?></strong></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Views</small>
                            <p class="mb-0"><strong><?php echo number_format($kos['views']); ?></strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Facilities -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Fasilitas</h5>
                    <?php if (!empty($fasilitas)): ?>
                        <div class="row g-2">
                            <?php foreach ($fasilitas as $fasilitas_item): ?>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <small><?php echo htmlspecialchars($fasilitas_item); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Tidak ada fasilitas</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Owner Info -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informasi Pemilik</h5>
                    <div class="d-flex align-items-center mb-3">
                        <?php if ($kos['pemilik_foto']): ?>
                            <img src="../uploads/profiles/<?php echo htmlspecialchars($kos['pemilik_foto']); ?>"
                                class="rounded-circle me-3" width="50" height="50" alt="Pemilik">
                        <?php else: ?>
                            <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px;">
                                <i class="fas fa-user text-white"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h6 class="mb-0"><?php echo htmlspecialchars($kos['pemilik_nama']); ?></h6>
                            <small class="text-muted">Pemilik Kos</small>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-12">
                            <small class="text-muted">Email</small>
                            <p class="mb-2"><a href="mailto:<?php echo htmlspecialchars($kos['pemilik_email']); ?>"><?php echo htmlspecialchars($kos['pemilik_email']); ?></a></p>
                        </div>
                        <?php if ($kos['pemilik_telepon']): ?>
                            <div class="col-12">
                                <small class="text-muted">Telepon</small>
                                <p class="mb-0"><?php echo htmlspecialchars($kos['pemilik_telepon']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
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
</div>
</div>

<?php include '../includes/footer/footer.php'; ?>