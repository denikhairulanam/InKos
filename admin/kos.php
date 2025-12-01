<?php
// Mulai output buffering di paling atas
ob_start();
include '../includes/auth.php';
include '../controler/admin/kos.php';
?>

<!-- Main Content -->
<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark">Manajemen Kos</h1>
            <p class="text-muted mb-0">Kelola data kos dalam sistem INKOS</p>
        </div>
        <a href="tambah_kos.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Kos
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-dark mb-1"><?php echo $total_count; ?></h4>
                            <p class="card-text text-muted mb-0">Total Kos</p>
                        </div>
                        <div class="bg-primary rounded-circle p-3">
                            <i class="fas fa-building fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-dark mb-1"><?php echo $stats['tersedia']; ?></h4>
                            <p class="card-text text-muted mb-0">Tersedia</p>
                        </div>
                        <div class="bg-success rounded-circle p-3">
                            <i class="fas fa-check-circle fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-dark mb-1"><?php echo $stats['featured']; ?></h4>
                            <p class="card-text text-muted mb-0">Featured</p>
                        </div>
                        <div class="bg-warning rounded-circle p-3">
                            <i class="fas fa-star fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-dark mb-1"><?php echo number_format($stats['total_views']); ?></h4>
                            <p class="card-text text-muted mb-0">Total Views</p>
                        </div>
                        <div class="bg-info rounded-circle p-3">
                            <i class="fas fa-eye fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="kos.php">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Cari Kos</label>
                        <input type="text" class="form-control" name="search"
                            placeholder="Nama kos, alamat, atau pemilik..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipe</label>
                        <select class="form-select" name="tipe">
                            <option value="">Semua Tipe</option>
                            <option value="putra" <?php echo $tipe_filter == 'putra' ? 'selected' : ''; ?>>Putra</option>
                            <option value="putri" <?php echo $tipe_filter == 'putri' ? 'selected' : ''; ?>>Putri</option>
                            <option value="campur" <?php echo $tipe_filter == 'campur' ? 'selected' : ''; ?>>Campur</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="tersedia" <?php echo $status_filter == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="tidak_tersedia" <?php echo $status_filter == 'tidak_tersedia' ? 'selected' : ''; ?>>Tidak Tersedia</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Featured</label>
                        <select class="form-select" name="featured">
                            <option value="">Semua</option>
                            <option value="featured" <?php echo $featured_filter == 'featured' ? 'selected' : ''; ?>>Featured</option>
                            <option value="not_featured" <?php echo $featured_filter == 'not_featured' ? 'selected' : ''; ?>>Biasa</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="d-grid w-100 gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <?php if (!empty($search) || !empty($tipe_filter) || !empty($status_filter) || !empty($featured_filter)): ?>
                                <a href="kos.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh me-2"></i>Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-dark">Daftar Kos</h5>
            <span class="badge bg-primary"><?php echo $total_count; ?> Kos</span>
        </div>
        <div class="card-body">
            <?php if (empty($kos_list)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-building fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada data kos</h5>
                    <p class="text-muted">Silakan tambahkan kos baru untuk memulai.</p>
                    <a href="tambah_kos.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Kos Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Kos</th>
                                <th>Pemilik</th>
                                <th>Lokasi</th>
                                <th>Harga</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kos_list as $index => $kos): ?>
                                <tr>
                                    <td class="text-muted"><?php echo $offset + $index + 1; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($kos['foto_utama'])): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($kos['foto_utama']); ?>"
                                                    class="rounded me-3"
                                                    alt="Kos <?php echo htmlspecialchars($kos['nama_kos']); ?>"
                                                    style="width: 50px; height: 50px; object-fit: cover;"
                                                    onerror="this.src='https://via.placeholder.com/50x50?text=No+Image'">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center"
                                                    style="width: 50px; height: 50px;">
                                                    <i class="fas fa-building text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($kos['nama_kos']); ?></strong>
                                                <?php if ($kos['featured']): ?>
                                                    <span class="badge bg-warning ms-1"><i class="fas fa-star"></i></span>
                                                <?php endif; ?>
                                                <br>
                                                <small class="text-muted">ID: <?php echo $kos['id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($kos['pemilik_nama'] ?? '-'); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo $kos['pemilik_telepon'] ?: '-'; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo !empty($kos['daerah_nama']) ? htmlspecialchars($kos['daerah_nama']) : '<span class="text-muted">-</span>'; ?>
                                            <?php if (!empty($kos['kota'])): ?>
                                                <br><span class="text-muted"><?php echo htmlspecialchars($kos['kota']); ?></span>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong>Rp <?php echo number_format($kos['harga_bulanan'], 0, ',', '.'); ?></strong>
                                        <br>
                                        <small class="text-muted">/bulan</small>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?php echo $kos['tipe_kos'] == 'putra' ? 'bg-primary' : ($kos['tipe_kos'] == 'putri' ? 'bg-danger' : 'bg-success'); ?>">
                                            <?php echo ucfirst($kos['tipe_kos']); ?>
                                        </span>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($kos['ukuran_kamar'] ?? '-'); ?> • <?php echo ucfirst($kos['kamar_mandi'] ?? '-'); ?></small>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="kos_id" value="<?php echo $kos['id']; ?>">
                                            <input type="hidden" name="kos_status" value="<?php echo $kos['status'] == 'tersedia' ? 'tidak_tersedia' : 'tersedia'; ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $kos['status'] == 'tersedia' ? 'btn-success' : 'btn-secondary'; ?>">
                                                <?php echo $kos['status'] == 'tersedia' ? 'Tersedia' : 'Tidak Tersedia'; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo number_format($kos['views']); ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="detail_kos.php?id=<?php echo $kos['id']; ?>" class="btn btn-outline-info" title="Detail" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_kos.php?id=<?php echo $kos['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="kos_id" value="<?php echo $kos['id']; ?>">
                                                <input type="hidden" name="featured_status" value="<?php echo $kos['featured'] ? '0' : '1'; ?>">
                                                <button type="submit" name="toggle_featured" class="btn btn-sm btn-outline-<?php echo $kos['featured'] ? 'warning' : 'secondary'; ?>" title="<?php echo $kos['featured'] ? 'Hapus Featured' : 'Jadikan Featured'; ?>">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal<?php echo $kos['id']; ?>" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $kos['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Apakah Anda yakin ingin menghapus kos <strong>"<?php echo htmlspecialchars($kos['nama_kos']); ?>"</strong>?</p>
                                                        <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="delete_id" value="<?php echo $kos['id']; ?>">
                                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&tipe=<?php echo urlencode($tipe_filter); ?>&status=<?php echo urlencode($status_filter); ?>&featured=<?php echo urlencode($featured_filter); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&tipe=<?php echo urlencode($tipe_filter); ?>&status=<?php echo urlencode($status_filter); ?>&featured=<?php echo urlencode($featured_filter); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&tipe=<?php echo urlencode($tipe_filter); ?>&status=<?php echo urlencode($status_filter); ?>&featured=<?php echo urlencode($featured_filter); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include '../includes/footer/footer.php';
ob_end_flush(); // Akhiri output buffering
?>