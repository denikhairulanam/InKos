<?php
include '../controler/admin/pemesanan.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pemesanan - INKOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bukti-bayar {
            max-width: 300px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <?php include '../includes/header/admin_header.php'; ?>

    <!-- Main Content -->
    <div class="container py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-dark">Manajemen Pemesanan</h1>
                <p class="text-muted mb-0">Kelola data pemesanan kos</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="pemesanan.php">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Cari Pemesanan</label>
                            <input type="text" class="form-control" name="search"
                                placeholder="Nama pencari atau nama kos..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status Pemesanan</label>
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="dikonfirmasi" <?php echo $status_filter == 'dikonfirmasi' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                <option value="dibatalkan" <?php echo $status_filter == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                <option value="selesai" <?php echo $status_filter == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status Pembayaran</label>
                            <select class="form-select" name="status_pembayaran">
                                <option value="">Semua Status</option>
                                <option value="menunggu" <?php echo $status_pembayaran_filter == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="lunas" <?php echo $status_pembayaran_filter == 'lunas' ? 'selected' : ''; ?>>Lunas</option>
                                <option value="gagal" <?php echo $status_pembayaran_filter == 'gagal' ? 'selected' : ''; ?>>Gagal</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="d-grid w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($search) || !empty($status_filter) || !empty($status_pembayaran_filter)): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <a href="pemesanan.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-refresh me-2"></i>Reset Filter
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">Daftar Pemesanan</h5>
                <span class="badge bg-primary"><?php echo $total_count; ?> Pemesanan</span>
            </div>
            <div class="card-body">
                <?php if (empty($pemesanan)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data pemesanan</h5>
                        <p class="text-muted">Belum ada pemesanan kos yang tercatat.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>ID Pemesanan</th>
                                    <th>Pencari Kos</th>
                                    <th>Nama Kos</th>
                                    <th>Durasi</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                    <th>Status Pembayaran</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pemesanan as $index => $order): ?>
                                    <tr>
                                        <td class="text-muted"><?php echo $offset + $index + 1; ?></td>
                                        <td>
                                            <strong>#<?php echo $order['id']; ?></strong>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order['nama_pencari']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['email_pencari']); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order['nama_kos']); ?></strong>
                                            <br>
                                            <small class="text-muted">Rp <?php echo number_format($order['harga_bulanan'], 0, ',', '.'); ?>/bln</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $order['durasi_bulan']; ?> bulan</span>
                                        </td>
                                        <td>
                                            <strong>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                <?php echo $order['status'] == 'dikonfirmasi' ? 'bg-success' : ($order['status'] == 'pending' ? 'bg-warning' : ($order['status'] == 'dibatalkan' ? 'bg-danger' : 'bg-secondary')); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                <?php echo $order['status_pembayaran'] == 'lunas' ? 'bg-success' : ($order['status_pembayaran'] == 'menunggu' ? 'bg-warning' : 'bg-danger'); ?>">
                                                <?php echo ucfirst($order['status_pembayaran']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y', strtotime($order['tanggal_mulai'])); ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <!-- Button Detail -->
                                                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal"
                                                    data-bs-target="#detailModal<?php echo $order['id']; ?>" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <!-- Button Edit Status -->
                                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                                    data-bs-target="#statusModal<?php echo $order['id']; ?>" title="Update Status">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <!-- Button Hapus -->
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal<?php echo $order['id']; ?>" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Detail Modal -->
                                            <div class="modal fade" id="detailModal<?php echo $order['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detail Pemesanan #<?php echo $order['id']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <!-- Informasi Pemesanan -->
                                                                <div class="col-md-6">
                                                                    <div class="card h-100">
                                                                        <div class="card-header bg-light">
                                                                            <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Informasi Pemesanan</h6>
                                                                        </div>
                                                                        <div class="card-body">
                                                                            <table class="table table-sm">
                                                                                <tr>
                                                                                    <td><strong>ID Pemesanan:</strong></td>
                                                                                    <td>#<?php echo $order['id']; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Status:</strong></td>
                                                                                    <td>
                                                                                        <span class="badge 
                                                                                            <?php echo $order['status'] == 'dikonfirmasi' ? 'bg-success' : ($order['status'] == 'pending' ? 'bg-warning' : ($order['status'] == 'dibatalkan' ? 'bg-danger' : 'bg-secondary')); ?>">
                                                                                            <?php echo ucfirst($order['status']); ?>
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Durasi:</strong></td>
                                                                                    <td><?php echo $order['durasi_bulan']; ?> bulan</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Total Harga:</strong></td>
                                                                                    <td class="fw-bold">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Tanggal Mulai:</strong></td>
                                                                                    <td><?php echo date('d/m/Y', strtotime($order['tanggal_mulai'])); ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Tanggal Selesai:</strong></td>
                                                                                    <td><?php echo date('d/m/Y', strtotime($order['tanggal_selesai'])); ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Dibuat:</strong></td>
                                                                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Informasi Kos -->
                                                                <div class="col-md-6">
                                                                    <div class="card h-100">
                                                                        <div class="card-header bg-light">
                                                                            <h6 class="mb-0"><i class="fas fa-home me-2"></i>Informasi Kos</h6>
                                                                        </div>
                                                                        <div class="card-body">
                                                                            <table class="table table-sm">
                                                                                <tr>
                                                                                    <td><strong>Nama Kos:</strong></td>
                                                                                    <td><?php echo htmlspecialchars($order['nama_kos']); ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Alamat:</strong></td>
                                                                                    <td><?php echo htmlspecialchars($order['alamat_kos']); ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Tipe Kos:</strong></td>
                                                                                    <td><?php echo ucfirst($order['tipe_kos']); ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Harga Bulanan:</strong></td>
                                                                                    <td>Rp <?php echo number_format($order['harga_bulanan'], 0, ',', '.'); ?></td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row mt-3">
                                                                <!-- Informasi Pencari -->
                                                                <div class="col-md-6">
                                                                    <div class="card">
                                                                        <div class="card-header bg-light">
                                                                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Pencari</h6>
                                                                        </div>
                                                                        <div class="card-body">
                                                                            <table class="table table-sm">
                                                                                <tr>
                                                                                    <td><strong>Nama:</strong></td>
                                                                                    <td><?php echo htmlspecialchars($order['nama_pencari']); ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Email:</strong></td>
                                                                                    <td><?php echo htmlspecialchars($order['email_pencari']); ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Telepon:</strong></td>
                                                                                    <td><?php echo $order['telepon_pencari'] ? htmlspecialchars($order['telepon_pencari']) : '-'; ?></td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Informasi Pembayaran -->
                                                                <div class="col-md-6">
                                                                    <div class="card">
                                                                        <div class="card-header bg-light">
                                                                            <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Informasi Pembayaran</h6>
                                                                        </div>
                                                                        <div class="card-body">
                                                                            <table class="table table-sm">
                                                                                <tr>
                                                                                    <td><strong>Status Pembayaran:</strong></td>
                                                                                    <td>
                                                                                        <span class="badge 
                                                                                            <?php echo $order['status_pembayaran'] == 'lunas' ? 'bg-success' : ($order['status_pembayaran'] == 'menunggu' ? 'bg-warning' : 'bg-danger'); ?>">
                                                                                            <?php echo ucfirst($order['status_pembayaran']); ?>
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Metode Pembayaran:</strong></td>
                                                                                    <td>
                                                                                        <?php if (isset($order['metode_pembayaran'])): ?>
                                                                                            <span class="badge bg-info"><?php echo ucfirst($order['metode_pembayaran']); ?></span>
                                                                                        <?php else: ?>
                                                                                            <span class="text-muted">-</span>
                                                                                        <?php endif; ?>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Jumlah Bayar:</strong></td>
                                                                                    <td>
                                                                                        <?php if (isset($order['jumlah_bayar'])): ?>
                                                                                            <strong class="text-success">Rp <?php echo number_format($order['jumlah_bayar'], 0, ',', '.'); ?></strong>
                                                                                        <?php else: ?>
                                                                                            <span class="text-muted">-</span>
                                                                                        <?php endif; ?>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Bukti Pembayaran -->
                                                            <?php if (isset($order['bukti_bayar']) && $order['bukti_bayar']): ?>
                                                                <div class="row mt-3">
                                                                    <div class="col-12">
                                                                        <div class="card">
                                                                            <div class="card-header bg-light">
                                                                                <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Bukti Pembayaran</h6>
                                                                            </div>
                                                                            <div class="card-body text-center">
                                                                                <img src="../uploads/bukti_bayar/<?php echo htmlspecialchars($order['bukti_bayar']); ?>"
                                                                                    class="img-fluid bukti-bayar rounded border"
                                                                                    alt="Bukti Pembayaran"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#imageModal<?php echo $order['id']; ?>"
                                                                                    onclick="document.getElementById('modalImage<?php echo $order['id']; ?>').src = this.src">
                                                                                <p class="text-muted mt-2">Klik gambar untuk memperbesar</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Image Modal -->
                                                                <div class="modal fade" id="imageModal<?php echo $order['id']; ?>" tabindex="-1">
                                                                    <div class="modal-dialog modal-xl">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title">Bukti Pembayaran</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                            </div>
                                                                            <div class="modal-body text-center">
                                                                                <img id="modalImage<?php echo $order['id']; ?>" src="" class="img-fluid" alt="Bukti Pembayaran">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                                data-bs-target="#statusModal<?php echo $order['id']; ?>">
                                                                <i class="fas fa-edit me-2"></i>Update Status
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Status Modal -->
                                            <div class="modal fade" id="statusModal<?php echo $order['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Update Status Pemesanan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <p>Update status untuk pemesanan <strong>#<?php echo $order['id']; ?></strong>?</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status Pemesanan</label>
                                                                    <select class="form-select" name="status" required>
                                                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                        <option value="dikonfirmasi" <?php echo $order['status'] == 'dikonfirmasi' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                                                        <option value="dibatalkan" <?php echo $order['status'] == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                                                        <option value="selesai" <?php echo $order['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status Pembayaran</label>
                                                                    <select class="form-select" name="status_pembayaran" required>
                                                                        <option value="menunggu" <?php echo $order['status_pembayaran'] == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                                                        <option value="lunas" <?php echo $order['status_pembayaran'] == 'lunas' ? 'selected' : ''; ?>>Lunas</option>
                                                                        <option value="gagal" <?php echo $order['status_pembayaran'] == 'gagal' ? 'selected' : ''; ?>>Gagal</option>
                                                                    </select>
                                                                </div>
                                                                <input type="hidden" name="pemesanan_id" value="<?php echo $order['id']; ?>">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" name="update_status" class="btn btn-success">Update Status</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal<?php echo $order['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Apakah Anda yakin ingin menghapus data pemesanan <strong>#<?php echo $order['id']; ?></strong>?</p>
                                                            <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="delete_id" value="<?php echo $order['id']; ?>">
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
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&status_pembayaran=<?php echo urlencode($status_pembayaran_filter); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&status_pembayaran=<?php echo urlencode($status_pembayaran_filter); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&status_pembayaran=<?php echo urlencode($status_pembayaran_filter); ?>">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>