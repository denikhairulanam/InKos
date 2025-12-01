<?php
include '../controler/pemilik/pemesanan.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pemesanan - INKOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/pemilik/pemesanan.css">
</head>

<body>
    <?php include '../includes/header/pemilik_header.php'; ?>

    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h2 class="mb-1">
                            <i class="fas fa-shopping-cart me-2 text-primary"></i>Manajemen Pemesanan
                        </h2>
                        <p class="text-muted mb-0">Kelola semua pemesanan kos Anda di satu tempat</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-2 col-6 mb-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body text-center py-3">
                        <h4 class="mb-1 fw-bold"><?= $stats['total'] ?? 0 ?></h4>
                        <small>Total Pesanan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card stat-card bg-warning text-dark">
                    <div class="card-body text-center py-3">
                        <h4 class="mb-1 fw-bold"><?= $stats['menunggu'] ?? 0 ?></h4>
                        <small>Menunggu</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body text-center py-3">
                        <h4 class="mb-1 fw-bold"><?= $stats['dikonfirmasi'] ?? 0 ?></h4>
                        <small>Dikonfirmasi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body text-center py-3">
                        <h4 class="mb-1 fw-bold"><?= $stats['menunggu_bayar'] ?? 0 ?></h4>
                        <small>Menunggu Bayar</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card stat-card bg-secondary text-white">
                    <div class="card-body text-center py-3">
                        <h4 class="mb-1 fw-bold"><?= $stats['lunas'] ?? 0 ?></h4>
                        <small>Lunas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card stat-card bg-danger text-white">
                    <div class="card-body text-center py-3">
                        <h4 class="mb-1 fw-bold"><?= $stats['ditolak'] ?? 0 ?></h4>
                        <small>Ditolak</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Status -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="card-title">Filter Status</h6>
                <div class="btn-group btn-group-sm flex-wrap">
                    <a href="?status=all" class="btn btn-outline-primary <?= $status_filter == 'all' ? 'active' : '' ?>">Semua</a>
                    <a href="?status=menunggu" class="btn btn-outline-warning <?= $status_filter == 'menunggu' ? 'active' : '' ?>">Menunggu</a>
                    <a href="?status=dikonfirmasi" class="btn btn-outline-success <?= $status_filter == 'dikonfirmasi' ? 'active' : '' ?>">Dikonfirmasi</a>
                    <a href="?status=selesai" class="btn btn-outline-info <?= $status_filter == 'selesai' ? 'active' : '' ?>">Selesai</a>
                    <a href="?status=ditolak" class="btn btn-outline-danger <?= $status_filter == 'ditolak' ? 'active' : '' ?>">Ditolak</a>
                </div>
            </div>
        </div>

        <?php if (empty($pemesanan)): ?>
            <div class="card text-center py-5">
                <div class="card-body">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum Ada Pemesanan</h5>
                    <p class="text-muted mb-3">Anda belum memiliki pemesanan untuk dikelola</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($pemesanan as $pesan): ?>
                <?php
                // Definisikan variabel status dan badge class
                $badge_class = [
                    'menunggu' => 'bg-warning',
                    'dikonfirmasi' => 'bg-success',
                    'ditolak' => 'bg-danger',
                    'selesai' => 'bg-info',
                    'dibatalkan' => 'bg-secondary'
                ];
                $status = $pesan['status'];

                $payment_badge_class = [
                    'menunggu' => 'bg-warning',
                    'lunas' => 'bg-success',
                    'gagal' => 'bg-danger'
                ];
                $payment_status = $pesan['status_pembayaran'] ?? '';
                ?>

                <div class="card card-pemesanan">
                    <div class="card-body">
                        <div class="row">
                            <!-- Info Kos -->
                            <div class="col-md-8">
                                <div class="d-flex align-items-start">
                                    <img src="../uploads/<?= htmlspecialchars($pesan['foto_utama'] ?? 'default.jpg') ?>"
                                        class="kos-image me-3"
                                        onerror="this.src='https://via.placeholder.com/100x100?text=Kos'">
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($pesan['nama_kos']) ?></h6>
                                        <p class="text-muted mb-1 small">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?= htmlspecialchars($pesan['alamat']) ?> - <?= htmlspecialchars($pesan['nama_daerah']) ?>
                                        </p>
                                        <p class="text-muted mb-1 small">
                                            <i class="fas fa-user me-1"></i>
                                            Pencari: <?= htmlspecialchars($pesan['nama_pencari']) ?>
                                        </p>
                                        <p class="text-muted mb-1 small">
                                            <i class="fas fa-phone me-1"></i>
                                            <?= $pesan['telepon'] ?>
                                        </p>
                                        <p class="text-muted mb-1 small">
                                            <i class="fas fa-envelope me-1"></i>
                                            <?= $pesan['email'] ?>
                                        </p>

                                        <!-- Status -->
                                        <div class="mt-2">
                                            <span class="badge <?= $badge_class[$status] ?> me-2">
                                                Status: <?= ucfirst($status) ?>
                                            </span>

                                            <span class="badge bg-<?=
                                                                    $pesan['status_kos'] == 'tersedia' ? 'success' : ($pesan['status_kos'] == 'dipesan' ? 'warning' : 'danger')
                                                                    ?>">
                                                Kos: <?= ucfirst(str_replace('_', ' ', $pesan['status_kos'])) ?>
                                            </span>

                                            <?php if ($pesan['status_pembayaran']): ?>
                                                <span class="badge <?= $payment_badge_class[$payment_status] ?> ms-2">
                                                    Pembayaran: <?= ucfirst($payment_status) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Harga & Aksi -->
                            <div class="col-md-4">
                                <div class="text-end">
                                    <h5 class="text-success mb-3">Rp <?= number_format($pesan['total_harga'], 0, ',', '.') ?></h5>

                                    <div class="d-grid gap-2">
                                        <?php if ($pesan['status'] === 'menunggu'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="pemesanan_id" value="<?= $pesan['id'] ?>">
                                                <input type="hidden" name="action" value="konfirmasi">
                                                <button type="submit" class="btn btn-success btn-sm w-100"
                                                    onclick="return confirm('Konfirmasi pemesanan ini?')">
                                                    <i class="fas fa-check me-1"></i>Konfirmasi
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm w-100"
                                                data-bs-toggle="modal"
                                                data-bs-target="#tolakModal<?= $pesan['id'] ?>">
                                                <i class="fas fa-times me-1"></i>Tolak
                                            </button>

                                        <?php elseif ($pesan['status'] === 'dikonfirmasi'): ?>
                                            <?php if ($pesan['status_pembayaran'] === 'menunggu' && $pesan['bukti_bayar']): ?>
                                                <div class="d-flex gap-2">
                                                    <form method="POST" class="flex-fill">
                                                        <input type="hidden" name="pemesanan_id" value="<?= $pesan['id'] ?>">
                                                        <input type="hidden" name="pembayaran_id" value="<?= $pesan['pembayaran_id'] ?>">
                                                        <input type="hidden" name="action" value="verifikasi_pembayaran">
                                                        <input type="hidden" name="verifikasi_action" value="terima">
                                                        <button type="submit" class="btn btn-success btn-sm w-100"
                                                            onclick="return confirm('Terima pembayaran ini?')">
                                                            <i class="fas fa-check me-1"></i>Terima Bayar
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-danger btn-sm flex-fill"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#tolakBayarModal<?= $pesan['id'] ?>">
                                                        <i class="fas fa-times me-1"></i>Tolak Bayar
                                                    </button>
                                                </div>
                                            <?php elseif ($pesan['status_pembayaran'] === 'lunas'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="pemesanan_id" value="<?= $pesan['id'] ?>">
                                                    <input type="hidden" name="action" value="selesai">
                                                    <button type="submit" class="btn btn-info btn-sm w-100"
                                                        onclick="return confirm('Tandai penyewaan sebagai selesai? Kos akan kembali tersedia.')">
                                                        <i class="fas fa-flag-checkered me-1"></i>Penyewaan Selesai
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark w-100 text-center py-2">Menunggu Pembayaran</span>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <button type="button" class="btn btn-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailModal<?= $pesan['id'] ?>">
                                            <i class="fas fa-eye me-1"></i>Lihat Detail
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Singkat -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <small class="text-muted">Periode Sewa:</small>
                                <p class="mb-1 fw-semibold">
                                    <?= date('d M Y', strtotime($pesan['tanggal_mulai'])) ?> -
                                    <?= date('d M Y', strtotime($pesan['tanggal_selesai'])) ?>
                                    (<?= $pesan['durasi_bulan'] ?> bulan)
                                </p>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Dipesan: <?= date('d M Y H:i', strtotime($pesan['tanggal_pemesanan'])) ?>
                                </small>
                            </div>
                        </div>

                        <!-- Catatan Pembatalan -->
                        <?php if (!empty($pesan['catatan_pembatalan'])): ?>
                            <div class="alert alert-warning mt-3 mb-0">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Alasan Pembatalan:</strong> <?= htmlspecialchars($pesan['catatan_pembatalan']) ?>
                                </small>
                            </div>
                        <?php endif; ?>

                        <!-- Alasan Penolakan Pembayaran -->
                        <?php if (!empty($pesan['alasan_penolakan'])): ?>
                            <div class="alert alert-danger mt-3 mb-0">
                                <small>
                                    <i class="fas fa-times-circle me-1"></i>
                                    <strong>Alasan Penolakan Pembayaran:</strong> <?= htmlspecialchars($pesan['alasan_penolakan']) ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Modal Detail -->
                <div class="modal fade" id="detailModal<?= $pesan['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Detail Pemesanan - <?= htmlspecialchars($pesan['nama_kos']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Foto Kos -->
                                <div class="text-center mb-4">
                                    <img src="../uploads/<?= htmlspecialchars($pesan['foto_utama'] ?? 'default.jpg') ?>"
                                        class="modal-kos-image"
                                        onerror="this.src='https://via.placeholder.com/800x400?text=Kos+Image'"
                                        alt="<?= htmlspecialchars($pesan['nama_kos']) ?>">
                                </div>

                                <div class="row">
                                    <!-- Info Kos -->
                                    <div class="col-md-6">
                                        <div class="detail-section">
                                            <h6 class="fw-bold mb-3"><i class="fas fa-home me-2"></i>Informasi Kos</h6>
                                            <p><strong>Nama Kos:</strong> <?= htmlspecialchars($pesan['nama_kos']) ?></p>
                                            <p><strong>Alamat:</strong> <?= htmlspecialchars($pesan['alamat']) ?></p>
                                            <p><strong>Daerah:</strong> <?= htmlspecialchars($pesan['nama_daerah']) ?></p>
                                            <p><strong>Status Kos:</strong>
                                                <span class="badge bg-<?=
                                                                        $pesan['status_kos'] == 'tersedia' ? 'success' : ($pesan['status_kos'] == 'dipesan' ? 'warning' : 'danger')
                                                                        ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $pesan['status_kos'])) ?>
                                                </span>
                                            </p>

                                            <?php if ($pesan['deskripsi']): ?>
                                                <p><strong>Deskripsi:</strong><br><?= nl2br(htmlspecialchars($pesan['deskripsi'])) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Info Pemesanan -->
                                    <div class="col-md-6">
                                        <div class="detail-section">
                                            <h6 class="fw-bold mb-3"><i class="fas fa-calendar me-2"></i>Informasi Pemesanan</h6>
                                            <p><strong>Tanggal Mulai:</strong> <?= date('d M Y', strtotime($pesan['tanggal_mulai'])) ?></p>
                                            <p><strong>Tanggal Selesai:</strong> <?= date('d M Y', strtotime($pesan['tanggal_selesai'])) ?></p>
                                            <p><strong>Durasi:</strong> <?= $pesan['durasi_bulan'] ?> bulan</p>
                                            <p><strong>Total Harga:</strong> Rp <?= number_format($pesan['total_harga'], 0, ',', '.') ?></p>
                                            <p><strong>Status:</strong>
                                                <span class="badge <?= $badge_class[$status] ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </p>
                                            <?php if ($pesan['status_pembayaran']): ?>
                                                <p><strong>Status Pembayaran:</strong>
                                                    <span class="badge <?= $payment_badge_class[$payment_status] ?>">
                                                        <?= ucfirst($payment_status) ?>
                                                    </span>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info Pencari -->
                                <div class="detail-section">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-user me-2"></i>Informasi Pencari</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Nama:</strong> <?= htmlspecialchars($pesan['nama_pencari']) ?></p>
                                            <p><strong>Telepon:</strong> <?= $pesan['telepon'] ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Email:</strong> <?= $pesan['email'] ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fasilitas -->
                                <?php if ($pesan['fasilitas']): ?>
                                    <div class="detail-section">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-list me-2"></i>Fasilitas</h6>
                                        <ul class="fasilitas-list">
                                            <?php
                                            $fasilitas = explode(',', $pesan['fasilitas']);
                                            foreach ($fasilitas as $fasilitas_item):
                                                if (trim($fasilitas_item)):
                                            ?>
                                                    <li><i class="fas fa-check text-success me-2"></i><?= htmlspecialchars(trim($fasilitas_item)) ?></li>
                                            <?php
                                                endif;
                                            endforeach;
                                            ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <!-- Info Pembayaran -->
                                <?php if ($pesan['pembayaran_id']): ?>
                                    <div class="detail-section">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-credit-card me-2"></i>Informasi Pembayaran</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Status Pembayaran:</strong>
                                                    <span class="badge <?= $payment_badge_class[$payment_status] ?>">
                                                        <?= ucfirst($payment_status) ?>
                                                    </span>
                                                </p>
                                                <p><strong>Metode:</strong> <?= ucfirst($pesan['metode_pembayaran'] ?? 'Transfer') ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <?php if ($pesan['tanggal_bayar']): ?>
                                                    <p><strong>Tanggal Bayar:</strong> <?= date('d M Y H:i', strtotime($pesan['tanggal_bayar'])) ?></p>
                                                <?php endif; ?>
                                                <?php if ($pesan['bukti_bayar']): ?>
                                                    <p><strong>Bukti Bayar:</strong>
                                                        <a href="../uploads/bukti_bayar/<?= $pesan['bukti_bayar'] ?>"
                                                            target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye me-1"></i>Lihat
                                                        </a>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- ALASAN PENOLAKAN PEMBAYARAN - SELALU TAMPIL JIKA ADA -->
                                        <?php if (!empty($pesan['alasan_penolakan'])): ?>
                                            <div class="alert alert-danger mt-3">
                                                <h6 class="fw-bold mb-2"><i class="fas fa-times-circle me-2"></i>Alasan Penolakan Pembayaran</h6>
                                                <p class="mb-0"><?= nl2br(htmlspecialchars($pesan['alasan_penolakan'])) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Catatan Pembatalan Pemesanan -->
                                <?php if (!empty($pesan['catatan_pembatalan'])): ?>
                                    <div class="detail-section">
                                        <div class="alert alert-warning">
                                            <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-2"></i>Alasan Pembatalan Pemesanan</h6>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($pesan['catatan_pembatalan'])) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <a href="/pencari/cetak_pemesanan.php?id=<?= $pesan['id'] ?>"
                                    class="btn btn-primary" target="_blank">
                                    <i class="fas fa-file-pdf me-1"></i>Download PDF
                                </a>
                                <?php if ($pesan['status'] === 'menunggu'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="pemesanan_id" value="<?= $pesan['id'] ?>">
                                        <input type="hidden" name="action" value="konfirmasi">
                                        <button type="submit" class="btn btn-success"
                                            onclick="return confirm('Konfirmasi pemesanan ini?')">
                                            <i class="fas fa-check me-1"></i>Konfirmasi
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#tolakModal<?= $pesan['id'] ?>">
                                        <i class="fas fa-times me-1"></i>Tolak
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Tolak Pemesanan -->
                <?php if ($pesan['status'] === 'menunggu'): ?>
                    <div class="modal fade" id="tolakModal<?= $pesan['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Tolak Pemesanan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="pemesanan_id" value="<?= $pesan['id'] ?>">
                                        <input type="hidden" name="action" value="tolak">
                                        <div class="mb-3">
                                            <label class="form-label">Alasan Penolakan</label>
                                            <textarea class="form-control" name="alasan_penolakan"
                                                rows="3" placeholder="Masukkan alasan penolakan..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-danger">Tolak Pemesanan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Modal Tolak Pembayaran -->
                <?php if ($pesan['status'] === 'dikonfirmasi' && $pesan['status_pembayaran'] === 'menunggu' && $pesan['bukti_bayar']): ?>
                    <div class="modal fade" id="tolakBayarModal<?= $pesan['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Tolak Pembayaran</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="pemesanan_id" value="<?= $pesan['id'] ?>">
                                        <input type="hidden" name="pembayaran_id" value="<?= $pesan['pembayaran_id'] ?>">
                                        <input type="hidden" name="action" value="verifikasi_pembayaran">
                                        <input type="hidden" name="verifikasi_action" value="tolak">
                                        <div class="mb-3">
                                            <label class="form-label">Alasan Penolakan Pembayaran</label>
                                            <textarea class="form-control" name="alasan_penolakan"
                                                rows="3" placeholder="Masukkan alasan penolakan pembayaran..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-danger">Tolak Pembayaran</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   
</body>

</html>