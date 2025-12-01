<?php
ob_start();
include '../controler/pemilik/kos_saya.php';
?>

<!-- HTML Content -->
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3"><i class="fas fa-home me-2"></i>Daftar Kos Saya</h2>
        <a href="tambah_kos.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Kos Baru
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($kos_list)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-info-circle fa-3x mb-3 text-info"></i>
                    <h4>Belum ada data kos</h4>
                    <p class="text-muted">Mulai dengan menambahkan kos pertama Anda</p>
                    <a href="tambah_kos.php" class="btn btn-primary mt-3">
                        <i class="fas fa-plus"></i>Tambah Kos Pertama
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($kos_list as $kos): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100">
                        <?php if ($kos['foto_utama']): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($kos['foto_utama']); ?>"
                                class="card-img-top"
                                alt="<?php echo htmlspecialchars($kos['nama_kos']); ?>"
                                style="height: 200px; object-fit: cover;"
                                onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                style="height: 200px;">
                                <i class="fas fa-home fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($kos['nama_kos']); ?></h5>
                            <p class="card-text flex-grow-1">
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-tag"></i> Rp <?php echo number_format($kos['harga_bulanan'], 0, ',', '.'); ?>/bulan
                                </small>
                                <span class="badge bg-<?php echo $kos['tipe_kos'] == 'putra' ? 'primary' : ($kos['tipe_kos'] == 'putri' ? 'danger' : 'success'); ?> mb-1">
                                    <i class="fas fa-<?php echo $kos['tipe_kos'] == 'putra' ? 'male' : ($kos['tipe_kos'] == 'putri' ? 'female' : 'users'); ?>"></i>
                                    <?php echo ucfirst($kos['tipe_kos']); ?>
                                </span>
                                <span class="badge bg-secondary mb-1">
                                    <i class="fas fa-expand"></i> <?php echo htmlspecialchars($kos['ukuran_kamar']); ?>
                                </span>
                                <span class="badge bg-info mb-1">
                                    <i class="fas fa-bath"></i> <?php echo ucfirst($kos['kamar_mandi']); ?>
                                </span>
                            </p>
                            <p class="card-text mt-auto">
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i>
                                    Ditambahkan: <?php echo date('d M Y', strtotime($kos['created_at'])); ?>
                                </small>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100">
                                <a href="detail_kos.php?id=<?php echo $kos['id']; ?>" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye"></i>Detail
                                </a>
                                <a href="edit_kos.php?id=<?php echo $kos['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i>Edit
                                </a>
                                <a href="hapus_kos.php?id=<?php echo $kos['id']; ?>" class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus kos <?php echo htmlspecialchars($kos['nama_kos']); ?>?')">
                                    <i class="fas fa-trash"></i>Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
include '../includes/footer/pemilik_footer.php';
ob_end_flush();
?>