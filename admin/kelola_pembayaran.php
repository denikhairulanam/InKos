<?php
include '../includes/auth.php';
include '../controler/admin/pembayaran.php';
include '../includes/header/admin_header.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pembayaran - INKOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .payment-card {
            border-left: 4px solid #007bff;
        }

        .payment-lunas {
            border-left-color: #28a745;
        }

        .payment-menunggu {
            border-left-color: #ffc107;
        }

        .payment-gagal {
            border-left-color: #dc3545;
        }

        .bukti-bayar {
            max-width: 300px;
            cursor: pointer;
        }

        .status-badge {
            font-size: 0.8rem;
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
                <h1 class="h3 mb-1 text-dark">Manajemen Pembayaran</h1>
                <p class="text-muted mb-0">Kelola data pembayaran dan verifikasi transaksi</p>
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
                <form method="GET" action="pembayaran.php">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Cari Pembayaran</label>
                            <input type="text" class="form-control" name="search"
                                placeholder="Nama pencari, email, atau nama kos..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status Pembayaran</label>
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="menunggu" <?php echo $status_filter == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="lunas" <?php echo $status_filter == 'lunas' ? 'selected' : ''; ?>>Lunas</option>
                                <option value="gagal" <?php echo $status_filter == 'gagal' ? 'selected' : ''; ?>>Gagal</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="d-grid w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <a href="pembayaran.php" class="btn btn-sm btn-outline-secondary">
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
                <h5 class="mb-0">Daftar Pembayaran</h5>
                <span class="badge bg-primary"><?php echo $total_count; ?> Pembayaran</span>
            </div>
            <div class="card-body">
                <?php if (empty($pembayaran)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data pembayaran</h5>
                        <p class="text-muted">Belum ada transaksi pembayaran yang tercatat.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Pemesanan</th>
                                    <th>Pencari Kos</th>
                                    <th>Kos</th>
                                    <th>Jumlah Bayar</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pembayaran as $index => $pay): ?>
                                    <tr class="<?php echo $pay['status_pembayaran'] == 'lunas' ? 'table-success' : ($pay['status_pembayaran'] == 'menunggu' ? 'table-warning' : 'table-danger'); ?>">
                                        <td class="text-muted"><?php echo $offset + $index + 1; ?></td>
                                        <td>
                                            <div>
                                                <strong>#<?php echo $pay['pemesanan_id']; ?></strong>
                                                <br>
                                                <small class="text-muted">Status:
                                                    <span class="badge bg-secondary"><?php echo ucfirst($pay['status_pemesanan']); ?></span>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($pay['nama_pencari']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($pay['email_pencari']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($pay['nama_kos']); ?></strong>
                                                <br>
                                                <small class="text-muted">Rp <?php echo number_format($pay['harga_bulanan'], 0, ',', '.'); ?>/bln</small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>Rp <?php echo number_format($pay['jumlah_bayar'], 0, ',', '.'); ?></strong>
                                            <br>
                                            <small class="text-muted">Total: Rp <?php echo number_format($pay['total_harga'], 0, ',', '.'); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo ucfirst($pay['metode_pembayaran']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                <?php echo $pay['status_pembayaran'] == 'lunas' ? 'bg-success' : ($pay['status_pembayaran'] == 'menunggu' ? 'bg-warning' : 'bg-danger'); ?>">
                                                <?php echo ucfirst($pay['status_pembayaran']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y', strtotime($pay['created_at'])); ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($pay['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="pembayaran.php?id=<?php echo $pay['id']; ?>" class="btn btn-outline-primary" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($pay['status_pembayaran'] == 'menunggu'): ?>
                                                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                                                        data-bs-target="#verifyModal<?php echo $pay['id']; ?>" title="Verifikasi">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal<?php echo $pay['id']; ?>" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Verify Modal -->
                                            <?php if ($pay['status_pembayaran'] == 'menunggu'): ?>
                                                <div class="modal fade" id="verifyModal<?php echo $pay['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Verifikasi Pembayaran</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST">
                                                                <div class="modal-body">
                                                                    <p>Verifikasi pembayaran untuk pemesanan <strong>#<?php echo $pay['pemesanan_id']; ?></strong>?</p>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Status Pembayaran</label>
                                                                        <select class="form-select" name="status_pembayaran" required>
                                                                            <option value="lunas">Lunas</option>
                                                                            <option value="gagal">Gagal</option>
                                                                        </select>
                                                                    </div>
                                                                    <input type="hidden" name="payment_id" value="<?php echo $pay['id']; ?>">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" name="update_status" class="btn btn-success">Update Status</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal<?php echo $pay['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Apakah Anda yakin ingin menghapus data pembayaran untuk pemesanan <strong>#<?php echo $pay['pemesanan_id']; ?></strong>?</p>
                                                            <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="delete_id" value="<?php echo $pay['id']; ?>">
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
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Detail Modal -->
        <?php if ($detail_pembayaran): ?>
            <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="false" data-bs-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Pembayaran #<?php echo $detail_pembayaran['id']; ?></h5>
                            <a href="pembayaran.php" class="btn-close"></a>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Informasi Pembayaran -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Informasi Pembayaran</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>ID Pembayaran:</strong></td>
                                                    <td>#<?php echo $detail_pembayaran['id']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>ID Pemesanan:</strong></td>
                                                    <td>#<?php echo $detail_pembayaran['pemesanan_id']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Jumlah Bayar:</strong></td>
                                                    <td class="fw-bold text-success">Rp <?php echo number_format($detail_pembayaran['jumlah_bayar'], 0, ',', '.'); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Harga:</strong></td>
                                                    <td>Rp <?php echo number_format($detail_pembayaran['total_harga'], 0, ',', '.'); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Metode:</strong></td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo ucfirst($detail_pembayaran['metode_pembayaran']); ?></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td>
                                                        <span class="badge 
                                                            <?php echo $detail_pembayaran['status_pembayaran'] == 'lunas' ? 'bg-success' : ($detail_pembayaran['status_pembayaran'] == 'menunggu' ? 'bg-warning' : 'bg-danger'); ?>">
                                                            <?php echo ucfirst($detail_pembayaran['status_pembayaran']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Bayar:</strong></td>
                                                    <td>
                                                        <?php echo $detail_pembayaran['tanggal_bayar'] ? date('d/m/Y H:i', strtotime($detail_pembayaran['tanggal_bayar'])) : '-'; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Dibuat:</strong></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($detail_pembayaran['created_at'])); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Pemesanan -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Informasi Pemesanan</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Kos:</strong></td>
                                                    <td><?php echo htmlspecialchars($detail_pembayaran['nama_kos']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Durasi:</strong></td>
                                                    <td><?php echo $detail_pembayaran['durasi_bulan']; ?> bulan</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Mulai:</strong></td>
                                                    <td><?php echo date('d/m/Y', strtotime($detail_pembayaran['tanggal_mulai'])); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Selesai:</strong></td>
                                                    <td><?php echo date('d/m/Y', strtotime($detail_pembayaran['tanggal_selesai'])); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status Pemesanan:</strong></td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo ucfirst($detail_pembayaran['status_pemesanan']); ?></span>
                                                    </td>
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
                                                    <td><?php echo htmlspecialchars($detail_pembayaran['nama_pencari']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td><?php echo htmlspecialchars($detail_pembayaran['email_pencari']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Telepon:</strong></td>
                                                    <td><?php echo $detail_pembayaran['telepon_pencari'] ? htmlspecialchars($detail_pembayaran['telepon_pencari']) : '-'; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Universitas:</strong></td>
                                                    <td><?php echo $detail_pembayaran['universitas_pencari'] ? htmlspecialchars($detail_pembayaran['universitas_pencari']) : '-'; ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Pemilik -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-home me-2"></i>Informasi Pemilik</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Nama:</strong></td>
                                                    <td><?php echo htmlspecialchars($detail_pembayaran['nama_pemilik']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td><?php echo htmlspecialchars($detail_pembayaran['email_pemilik']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Telepon:</strong></td>
                                                    <td><?php echo $detail_pembayaran['telepon_pemilik'] ? htmlspecialchars($detail_pembayaran['telepon_pemilik']) : '-'; ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bukti Pembayaran -->
                            <?php if ($detail_pembayaran['bukti_bayar']): ?>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Bukti Pembayaran</h6>
                                            </div>
                                            <div class="card-body text-center">
                                                <img src="../uploads/payments/<?php echo htmlspecialchars($detail_pembayaran['bukti_bayar']); ?>"
                                                    class="img-fluid bukti-bayar rounded border"
                                                    alt="Bukti Pembayaran"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#imageModal"
                                                    onclick="document.getElementById('modalImage').src = this.src">
                                                <p class="text-muted mt-2">Klik gambar untuk memperbesar</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Update Status Form -->
                            <?php if ($detail_pembayaran['status_pembayaran'] == 'menunggu'): ?>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Update Status</h6>
                                            </div>
                                            <div class="card-body">
                                                <form method="POST" class="row g-3 align-items-end">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Status Pembayaran</label>
                                                        <select class="form-select" name="status_pembayaran" required>
                                                            <option value="lunas">Lunas</option>
                                                            <option value="gagal">Gagal</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="hidden" name="payment_id" value="<?php echo $detail_pembayaran['id']; ?>">
                                                        <button type="submit" name="update_status" class="btn btn-success w-100">
                                                            <i class="fas fa-check me-2"></i>Update Status
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <a href="pembayaran.php" class="btn btn-secondary">Tutup</a>
                            <?php if ($detail_pembayaran['status_pembayaran'] == 'menunggu'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="payment_id" value="<?php echo $detail_pembayaran['id']; ?>">
                                    <input type="hidden" name="status_pembayaran" value="lunas">
                                    <button type="submit" name="update_status" class="btn btn-success">
                                        <i class="fas fa-check me-2"></i>Verifikasi sebagai Lunas
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Modal -->
            <div class="modal fade" id="imageModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Bukti Pembayaran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img id="modalImage" src="" class="img-fluid" alt="Bukti Pembayaran">
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if ($detail_pembayaran): ?>
            // Show detail modal when page loads with ID parameter
            document.addEventListener('DOMContentLoaded', function() {
                var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
                detailModal.show();
            });
        <?php endif; ?>
    </script>
</body>

</html>