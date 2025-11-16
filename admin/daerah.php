<?php
include '../includes/auth.php';
include '../controler/admin/daerah.php';
?>

<!-- Main Content -->
<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark">Manajemen Daerah</h1>
            <p class="text-muted mb-0">Kelola data daerah dalam sistem INKOS</p>
        </div>
        <a href="tambah_daerah.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Daerah
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

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="daerah.php">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Cari Daerah</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="search"
                                placeholder="Cari nama daerah atau kota..."
                                value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <?php if (!empty($search)): ?>
                            <a href="daerah.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-refresh me-2"></i>Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-dark">Daftar Daerah</h5>
            <span class="badge bg-primary"><?php echo $total_count; ?> Daerah</span>
        </div>
        <div class="card-body">
            <?php if (empty($daerah)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada data daerah</h5>
                    <p class="text-muted">Silakan tambahkan daerah baru untuk memulai.</p>
                    <a href="tambah_daerah.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Daerah Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nama Daerah</th>
                                <th>Kota</th>
                                <th>Koordinat</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($daerah as $index => $item): ?>
                                <tr>
                                    <td class="text-muted"><?php echo $offset + $index + 1; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['nama']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($item['kota']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($item['latitude'] && $item['longitude']): ?>
                                            <small class="text-muted">
                                                <?php echo number_format($item['latitude'], 6) . ', ' . number_format($item['longitude'], 6); ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo date('d/m/Y', strtotime($item['created_at'])); ?></small>
                                        <br>
                                        <small class="text-muted"><?php echo date('H:i', strtotime($item['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit_daerah.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal<?php echo $item['id']; ?>" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Apakah Anda yakin ingin menghapus daerah <strong>"<?php echo htmlspecialchars($item['nama']); ?>"</strong>?</p>
                                                        <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
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
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
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

<?php include '../includes/footer/footer.php'; ?>