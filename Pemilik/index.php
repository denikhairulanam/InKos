<?php
// Pemilik/index.php
include '../controler/pemilik/index.php';
?>
<!-- Main Content Area -->
<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Pemilik Kos</h2>
            <p class="text-muted mb-0">Selamat datang, <?php echo $_SESSION['nama'] ?? 'Pemilik'; ?>! Berikut ringkasan bisnis kos Anda.</p>
        </div>
        <div>
            <?php if ($notifikasi_count > 0): ?>
                <span class="badge bg-danger me-2"><?php echo $notifikasi_count; ?> Pemesanan Baru</span>
            <?php endif; ?>
            <a href="tambah_kos.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Kos
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo $total_kos; ?></h4>
                            <p class="text-muted mb-0">Total Kos</p>
                        </div>
                        <div class="bg-primary rounded-circle p-3">
                            <i class="fas fa-building fa-2x text-white"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-success">
                            <i class="fas fa-arrow-up me-1"></i>
                            Semua kos Anda
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo $kamar_terisi; ?></h4>
                            <p class="text-muted mb-0">Kamar Terisi</p>
                        </div>
                        <div class="bg-success rounded-circle p-3">
                            <i class="fas fa-bed fa-2x text-white"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-success">
                            <i class="fas fa-check me-1"></i>
                            Sedang ditempati
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo $kamar_kosong; ?></h4>
                            <p class="text-muted mb-0">Kamar Kosong</p>
                        </div>
                        <div class="bg-warning rounded-circle p-3">
                            <i class="fas fa-door-open fa-2x text-white"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-warning">
                            <i class="fas fa-clock me-1"></i>
                            Siap ditempati
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Rp <?php echo number_format($estimasi_pendapatan_bulanan, 0, ',', '.'); ?></h4>
                            <p class="text-muted mb-0">Estimasi Pendapatan/Bulan</p>
                        </div>
                        <div class="bg-info rounded-circle p-3">
                            <i class="fas fa-money-bill-wave fa-2x text-white"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-info">
                            <i class="fas fa-chart-line me-1"></i>
                            Dari pembayaran lunas
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Pemesanan Terbaru -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pemesanan Terbaru</h5>
                    <a href="pemesanan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($pemesanan_terbaru)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pemesan</th>
                                        <th>Kos</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Durasi</th>
                                        <th>Total Harga</th>
                                        <th>Status Pemesanan</th>
                                        <th>Status Pembayaran</th>
                                        <th>Tanggal Pemesanan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pemesanan_terbaru as $pemesanan): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3"
                                                        style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($pemesanan['nama_pemesan']); ?></div>
                                                        <small class="text-muted"><?php echo $pemesanan['telepon_pemesan'] ?? '-'; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($pemesanan['foto_utama']): ?>
                                                        <img src="../uploads/<?php echo htmlspecialchars($pemesanan['foto_utama']); ?>"
                                                            alt="<?php echo htmlspecialchars($pemesanan['nama_kos']); ?>"
                                                            class="rounded me-2" width="40" height="40" style="object-fit: cover;"
                                                            onerror="this.src='https://via.placeholder.com/40x40?text=No+Image'">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-2"
                                                            style="width: 40px; height: 40px;">
                                                            <i class="fas fa-home text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($pemesanan['nama_kos']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars(substr($pemesanan['alamat'], 0, 30) . (strlen($pemesanan['alamat']) > 30 ? '...' : '')); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo date('d M Y', strtotime($pemesanan['tanggal_mulai'])); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $pemesanan['durasi_bulan']; ?> Bulan</span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">Rp <?php echo number_format($pemesanan['total_harga'], 0, ',', '.'); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                                        switch ($pemesanan['status']) {
                                                                            case 'dikonfirmasi':
                                                                                echo 'success';
                                                                                break;
                                                                            case 'menunggu':
                                                                                echo 'warning';
                                                                                break;
                                                                            case 'ditolak':
                                                                                echo 'danger';
                                                                                break;
                                                                            case 'dibatalkan':
                                                                                echo 'secondary';
                                                                                break;
                                                                            case 'selesai':
                                                                                echo 'info';
                                                                                break;
                                                                            default:
                                                                                echo 'secondary';
                                                                        }
                                                                        ?>">
                                                    <?php echo ucfirst($pemesanan['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                                        switch ($pemesanan['status_pembayaran']) {
                                                                            case 'lunas':
                                                                                echo 'success';
                                                                                break;
                                                                            case 'menunggu':
                                                                                echo 'warning';
                                                                                break;
                                                                            case 'gagal':
                                                                                echo 'danger';
                                                                                break;
                                                                            default:
                                                                                echo 'secondary';
                                                                        }
                                                                        ?>">
                                                    <?php echo ucfirst($pemesanan['status_pembayaran'] ?? 'Belum Bayar'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo date('d M Y H:i', strtotime($pemesanan['tanggal_pemesanan'])); ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Belum ada pemesanan</p>
                            <small class="text-muted">Pemesanan akan muncul di sini</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Kos Terbaru -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Kos Terbaru</h5>
                    <a href="kos_saya.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        $query = "SELECT * FROM kos WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->execute();
                        $kos_terbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        $kos_terbaru = [];
                    }
                    ?>

                    <?php if (!empty($kos_terbaru)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Kos</th>
                                        <th>Lokasi</th>
                                        <th>Harga</th>
                                        <th>Status</th>
                                        <th>Tanggal Ditambahkan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kos_terbaru as $kos): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($kos['foto_utama']): ?>
                                                        <img src="../uploads/<?php echo htmlspecialchars($kos['foto_utama']); ?>"
                                                            alt="<?php echo htmlspecialchars($kos['nama_kos']); ?>"
                                                            class="rounded me-3" width="40" height="40" style="object-fit: cover;"
                                                            onerror="this.src='https://via.placeholder.com/40x40?text=No+Image'">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3"
                                                            style="width: 40px; height: 40px;">
                                                            <i class="fas fa-home text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($kos['nama_kos']); ?></div>
                                                        <small class="text-muted"><?php echo ucfirst($kos['tipe_kos']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo htmlspecialchars(substr($kos['alamat'], 0, 30) . (strlen($kos['alamat']) > 30 ? '...' : '')); ?></small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">Rp <?php echo number_format($kos['harga_bulanan'], 0, ',', '.'); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $kos['status'] == 'tersedia' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $kos['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo date('d M Y', strtotime($kos['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <a href="detail_kos.php?id=<?php echo $kos['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit_kos.php?id=<?php echo $kos['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-home fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Belum ada kos</p>
                            <small class="text-muted">Mulai dengan menambahkan kos pertama Anda</small>
                            <div class="mt-3">
                                <a href="tambah_kos.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Tambah Kos Pertama
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer/pemilik_footer.php'; ?>