<?php
include '../includes/auth.php';
include '../controler/admin/pemesanan.php';
include '../includes/header/admin_header.php';
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
        .booking-card {
            border-left: 4px solid #007bff;
        }

        .status-menunggu {
            border-left-color: #ffc107;
        }

        .status-dikonfirmasi {
            border-left-color: #28a745;
        }

        .status-ditolak {
            border-left-color: #dc3545;
        }

        .status-dibatalkan {
            border-left-color: #6c757d;
        }

        .status-selesai {
            border-left-color: #17a2b8;
        }

        .payment-menunggu {
            color: #ffc107;
        }

        .payment-lunas {
            color: #28a745;
        }

        .payment-gagal {
            color: #dc3545;
        }

        .bukti-bayar {
            max-width: 200px;
            cursor: pointer;
        }

        .stats-card {
            transition: transform 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-2px);
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
                <p class="text-muted mb-0">Kelola data pemesanan dan transaksi kos</p>
            </div>
            <div class="d-flex gap-2">
                <a href="aktivitas.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
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

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php
            $statusColors = [
                'menunggu' => 'warning',
                'dikonfirmasi' => 'success',
                'ditolak' => 'danger',
                'dibatalkan' => 'secondary',
                'selesai' => 'info'
            ];

            foreach ($stats as $stat):
                $color = $statusColors[$stat['status']] ?? 'primary';
            ?>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card stats-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-<?php echo $color; ?> mb-2">
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                            <h4 class="mb-1"><?php echo $stat['total']; ?></h4>
                            <small class="text-muted text-capitalize"><?php echo $stat['status']; ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Search and Filter -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="pemesanan.php">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Cari Pemesanan</label>
                            <input type="text" class="form-control" name="search"
                                placeholder="Nama pencari, email, atau nama kos..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status Pemesanan</label>
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="menunggu" <?php echo $status_filter == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="dikonfirmasi" <?php echo $status_filter == 'dikonfirmasi' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                <option value="ditolak" <?php echo $status_filter == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                <option value="dibatalkan" <?php echo $status_filter == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                <option value="selesai" <?php echo $status_filter == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status Pembayaran</label>
                            <select class="form-select" name="status_pembayaran">
                                <option value="">Semua Status</option>
                                <option value="menunggu" <?php echo $status_pembayaran_filter == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="pending" <?php echo $status_pembayaran_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
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
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data pemesanan</h5>
                        <p class="text-muted">Belum ada pemesanan kos yang tercatat.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Pemesanan</th>
                                    <th>Pencari</th>
                                    <th>Kos</th>
                                    <th>Periode</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Pembayaran</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pemesanan as $index => $order): ?>
                                    <tr class="status-<?php echo $order['status']; ?>">
                                        <td class="text-muted"><?php echo $offset + $index + 1; ?></td>
                                        <td>
                                            <div>
                                                <strong>#<?php echo $order['id']; ?></strong>
                                                <br>
                                                <small class="text-muted">Durasi: <?php echo $order['durasi_bulan']; ?> bulan</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($order['nama_pencari']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['email_pencari']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($order['nama_kos']); ?></strong>
                                                <br>
                                                <small class="text-muted">Rp <?php echo number_format($order['harga_bulanan'], 0, ',', '.'); ?>/bln</small>
                                            </div>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y', strtotime($order['tanggal_mulai'])); ?></small>
                                            <br>
                                            <small class="text-muted">s/d <?php echo date('d/m/Y', strtotime($order['tanggal_selesai'])); ?></small>
                                        </td>
                                        <td>
                                            <strong>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                <?php echo $order['status'] == 'dikonfirmasi' ? 'bg-success' : ($order['status'] == 'menunggu' ? 'bg-warning' : ($order['status'] == 'ditolak' ? 'bg-danger' : ($order['status'] == 'selesai' ? 'bg-info' : 'bg-secondary'))); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                            <?php if ($order['status_penyewaan']): ?>
                                                <br>
                                                <small class="badge bg-dark mt-1"><?php echo ucfirst($order['status_penyewaan']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                <?php echo $order['status_pembayaran'] == 'lunas' ? 'bg-success' : ($order['status_pembayaran'] == 'menunggu' ? 'bg-warning' : ($order['status_pembayaran'] == 'gagal' ? 'bg-danger' : 'bg-secondary')); ?>">
                                                <?php echo ucfirst($order['status_pembayaran']); ?>
                                            </span>
                                            <?php if ($order['jumlah_bayar']): ?>
                                                <br>
                                                <small class="text-muted">Rp <?php echo number_format($order['jumlah_bayar'], 0, ',', '.'); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($order['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="pemesanan.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-primary" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($order['status'] == 'menunggu'): ?>
                                                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                                                        data-bs-target="#confirmModal<?php echo $order['id']; ?>" title="Konfirmasi">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                        data-bs-target="#rejectModal<?php echo $order['id']; ?>" title="Tolak">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal<?php echo $order['id']; ?>" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Confirm Modal -->
                                            <?php if ($order['status'] == 'menunggu'): ?>
                                                <div class="modal fade" id="confirmModal<?php echo $order['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Konfirmasi Pemesanan</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST">
                                                                <div class="modal-body">
                                                                    <p>Konfirmasi pemesanan <strong>#<?php echo $order['id']; ?></strong>?</p>
                                                                    <p>Pemesanan akan dikonfirmasi dan status akan berubah menjadi "dikonfirmasi".</p>
                                                                    <input type="hidden" name="pemesanan_id" value="<?php echo $order['id']; ?>">
                                                                    <input type="hidden" name="status" value="dikonfirmasi">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" name="update_status" class="btn btn-success">Konfirmasi</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Reject Modal -->
                                                <div class="modal fade" id="rejectModal<?php echo $order['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Tolak Pemesanan</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST">
                                                                <div class="modal-body">
                                                                    <p>Tolak pemesanan <strong>#<?php echo $order['id']; ?></strong>?</p>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Alasan Penolakan (Opsional)</label>
                                                                        <textarea class="form-control" name="catatan_pembatalan" rows="3"
                                                                            placeholder="Berikan alasan penolakan..."></textarea>
                                                                    </div>
                                                                    <input type="hidden" name="pemesanan_id" value="<?php echo $order['id']; ?>">
                                                                    <input type="hidden" name="status" value="ditolak">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" name="update_status" class="btn btn-danger">Tolak Pemesanan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

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
                                                            <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait.</small></p>
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

        <!-- Detail Modal -->
        <?php if ($detail_pemesanan): ?>
            <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="false" data-bs-backdrop="static">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Pemesanan #<?php echo $detail_pemesanan['id']; ?></h5>
                            <a href="pemesanan.php" class="btn-close"></a>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Informasi Pemesanan -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Pemesanan</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td width="40%"><strong>ID Pemesanan:</strong></td>
                                                    <td>#<?php echo $detail_pemesanan['id']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Pemesanan:</strong></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($detail_pemesanan['tanggal_pemesanan'])); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td>
                                                        <span class="badge 
                                                            <?php echo $detail_pemesanan['status'] == 'dikonfirmasi' ? 'bg-success' : ($detail_pemesanan['status'] == 'menunggu' ? 'bg-warning' : ($detail_pemesanan['status'] == 'ditolak' ? 'bg-danger' : ($detail_pemesanan['status'] == 'selesai' ? 'bg-info' : 'bg-secondary'))); ?>">
                                                            <?php echo ucfirst($detail_pemesanan['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status Pembayaran:</strong></td>
                                                    <td>
                                                        <span class="badge 
                                                            <?php echo $detail_pemesanan['status_pembayaran'] == 'lunas' ? 'bg-success' : ($detail_pemesanan['status_pembayaran'] == 'menunggu' ? 'bg-warning' : ($detail_pemesanan['status_pembayaran'] == 'gagal' ? 'bg-danger' : 'bg-secondary')); ?>">
                                                            <?php echo ucfirst($detail_pemesanan['status_pembayaran']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status Penyewaan:</strong></td>
                                                    <td>
                                                        <?php if ($detail_pemesanan['status_penyewaan']): ?>
                                                            <span class="badge bg-dark"><?php echo ucfirst($detail_pemesanan['status_penyewaan']); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php if ($detail_pemesanan['catatan_pembatalan']): ?>
                                                    <tr>
                                                        <td><strong>Catatan Pembatalan:</strong></td>
                                                        <td class="text-danger"><?php echo htmlspecialchars($detail_pemesanan['catatan_pembatalan']); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Periode & Biaya -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Periode & Biaya</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td width="40%"><strong>Tanggal Mulai:</strong></td>
                                                    <td><?php echo date('d/m/Y', strtotime($detail_pemesanan['tanggal_mulai'])); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Selesai:</strong></td>
                                                    <td><?php echo date('d/m/Y', strtotime($detail_pemesanan['tanggal_selesai'])); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Durasi:</strong></td>
                                                    <td><?php echo $detail_pemesanan['durasi_bulan']; ?> bulan</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Harga per Bulan:</strong></td>
                                                    <td>Rp <?php echo number_format($detail_pemesanan['harga_bulanan'], 0, ',', '.'); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Harga:</strong></td>
                                                    <td class="fw-bold text-success">Rp <?php echo number_format($detail_pemesanan['total_harga'], 0, ',', '.'); ?></td>
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
                                                    <td width="40%"><strong>Nama:</strong></td>
                                                    <td><?php echo htmlspecialchars($detail_pemesanan['nama_pencari']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td><?php echo htmlspecialchars($detail_pemesanan['email_pencari']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Telepon:</strong></td>
                                                    <td><?php echo $detail_pemesanan['telepon_pencari'] ? htmlspecialchars($detail_pemesanan['telepon_pencari']) : '-'; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Universitas:</strong></td>
                                                    <td><?php echo $detail_pemesanan['universitas_pencari'] ? htmlspecialchars($detail_pemesanan['universitas_pencari']) : '-'; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Jenis Kelamin:</strong></td>
                                                    <td><?php echo $detail_pemesanan['jenis_kelamin_pencari'] == 'L' ? 'Laki-laki' : ($detail_pemesanan['jenis_kelamin_pencari'] == 'P' ? 'Perempuan' : '-'); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Kos -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-home me-2"></i>Informasi Kos</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td width="40%"><strong>Nama Kos:</strong></td>
                                                    <td><?php echo htmlspecialchars($detail_pemesanan['nama_kos']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Alamat:</strong></td>
                                                    <td><?php echo htmlspecialchars($detail_pemesanan['alamat_kos']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tipe Kos:</strong></td>
                                                    <td class="text-capitalize"><?php echo $detail_pemesanan['tipe_kos']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Ukuran Kamar:</strong></td>
                                                    <td><?php echo htmlspecialchars($detail_pemesanan['ukuran_kamar']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Kamar Mandi:</strong></td>
                                                    <td class="text-capitalize"><?php echo $detail_pemesanan['kamar_mandi']; ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Pembayaran -->
                            <?php if ($detail_pemesanan['pembayaran_id']): ?>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Informasi Pembayaran</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <table class="table table-sm">
                                                            <tr>
                                                                <td width="40%"><strong>ID Pembayaran:</strong></td>
                                                                <td>#<?php echo $detail_pemesanan['pembayaran_id']; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Jumlah Bayar:</strong></td>
                                                                <td class="fw-bold text-success">Rp <?php echo number_format($detail_pemesanan['jumlah_bayar'], 0, ',', '.'); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Metode:</strong></td>
                                                                <td class="text-capitalize"><?php echo $detail_pemesanan['metode_pembayaran']; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Status:</strong></td>
                                                                <td>
                                                                    <span class="badge 
                                                                    <?php echo $detail_pemesanan['status_pembayaran_detail'] == 'lunas' ? 'bg-success' : ($detail_pemesanan['status_pembayaran_detail'] == 'menunggu' ? 'bg-warning' : 'bg-danger'); ?>">
                                                                        <?php echo ucfirst($detail_pemesanan['status_pembayaran_detail']); ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Tanggal Bayar:</strong></td>
                                                                <td><?php echo $detail_pemesanan['tanggal_bayar'] ? date('d/m/Y H:i', strtotime($detail_pemesanan['tanggal_bayar'])) : '-'; ?></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6 text-center">
                                                        <?php if ($detail_pemesanan['bukti_bayar']): ?>
                                                            <p class="fw-bold">Bukti Pembayaran:</p>
                                                            <img src="../uploads/payments/<?php echo htmlspecialchars($detail_pemesanan['bukti_bayar']); ?>"
                                                                class="img-fluid bukti-bayar rounded border"
                                                                alt="Bukti Pembayaran"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#imageModal"
                                                                onclick="document.getElementById('modalImage').src = this.src">
                                                            <p class="text-muted mt-2">Klik gambar untuk memperbesar</p>
                                                        <?php else: ?>
                                                            <p class="text-muted">Tidak ada bukti pembayaran</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Kelola Pemesanan</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <!-- Update Status Pemesanan -->
                                                <div class="col-md-4">
                                                    <label class="form-label">Status Pemesanan</label>
                                                    <form method="POST" class="d-flex gap-2">
                                                        <select class="form-select" name="status" required>
                                                            <option value="menunggu" <?php echo $detail_pemesanan['status'] == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                                            <option value="dikonfirmasi" <?php echo $detail_pemesanan['status'] == 'dikonfirmasi' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                                            <option value="ditolak" <?php echo $detail_pemesanan['status'] == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                                            <option value="dibatalkan" <?php echo $detail_pemesanan['status'] == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                                            <option value="selesai" <?php echo $detail_pemesanan['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                        </select>
                                                        <input type="hidden" name="pemesanan_id" value="<?php echo $detail_pemesanan['id']; ?>">
                                                        <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                                                    </form>
                                                </div>

                                                <!-- Update Status Pembayaran -->
                                                <div class="col-md-4">
                                                    <label class="form-label">Status Pembayaran</label>
                                                    <form method="POST" class="d-flex gap-2">
                                                        <select class="form-select" name="status_pembayaran" required>
                                                            <option value="menunggu" <?php echo $detail_pemesanan['status_pembayaran'] == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                                            <option value="pending" <?php echo $detail_pemesanan['status_pembayaran'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="lunas" <?php echo $detail_pemesanan['status_pembayaran'] == 'lunas' ? 'selected' : ''; ?>>Lunas</option>
                                                            <option value="gagal" <?php echo $detail_pemesanan['status_pembayaran'] == 'gagal' ? 'selected' : ''; ?>>Gagal</option>
                                                        </select>
                                                        <input type="hidden" name="pemesanan_id" value="<?php echo $detail_pemesanan['id']; ?>">
                                                        <button type="submit" name="update_payment_status" class="btn btn-warning">Update</button>
                                                    </form>
                                                </div>

                                                <!-- Update Status Penyewaan -->
                                                <div class="col-md-4">
                                                    <label class="form-label">Status Penyewaan</label>
                                                    <form method="POST" class="d-flex gap-2">
                                                        <select class="form-select" name="status_penyewaan" required>
                                                            <option value="">Pilih Status</option>
                                                            <option value="aktif" <?php echo $detail_pemesanan['status_penyewaan'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                                            <option value="selesai" <?php echo $detail_pemesanan['status_penyewaan'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                        </select>
                                                        <input type="hidden" name="pemesanan_id" value="<?php echo $detail_pemesanan['id']; ?>">
                                                        <button type="submit" name="update_rental_status" class="btn btn-info">Update</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="pemesanan.php" class="btn btn-secondary">Tutup</a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $detail_pemesanan['id']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Hapus pemesanan ini?')">
                                    <i class="fas fa-trash me-2"></i>Hapus Pemesanan
                                </button>
                            </form>
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
        <?php if ($detail_pemesanan): ?>
            // Show detail modal when page loads with ID parameter
            document.addEventListener('DOMContentLoaded', function() {
                var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
                detailModal.show();
            });
        <?php endif; ?>
    </script>
</body>

</html>