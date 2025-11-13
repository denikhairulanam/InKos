<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pemilik') {
    header('Location: ../login.php');
    exit;
}

$pemilik_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'all';

// Query dengan join yang lebih baik untuk mendapatkan data pembayaran
$query = "SELECT p.*, k.nama_kos, k.alamat, k.foto_utama, k.deskripsi, k.fasilitas, k.id as kos_id, 
                 d.nama as nama_daerah,
                 u.nama as nama_pencari, u.telepon, u.email, 
                 pb.id as pembayaran_id, pb.status_pembayaran, pb.bukti_bayar,
                 pb.tanggal_bayar, pb.metode_pembayaran, k.status as status_kos
          FROM pemesanan p 
          JOIN kos k ON p.kos_id = k.id 
          JOIN daerah d ON k.daerah_id = d.id
          JOIN users u ON p.pencari_id = u.id 
          LEFT JOIN pembayaran pb ON p.id = pb.pemesanan_id
          WHERE p.pemilik_id = ?";

if ($status_filter !== 'all') {
    $query .= " AND p.status = ?";
}

$query .= " ORDER BY p.tanggal_pemesanan DESC";

$stmt = $conn->prepare($query);
if ($status_filter !== 'all') {
    $stmt->bind_param("is", $pemilik_id, $status_filter);
} else {
    $stmt->bind_param("i", $pemilik_id);
}

$stmt->execute();
$result = $stmt->get_result();
$pemesanan = $result->fetch_all(MYSQLI_ASSOC);

// Hitung statistik dengan status pembayaran
$query_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN p.status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
    SUM(CASE WHEN p.status = 'dikonfirmasi' THEN 1 ELSE 0 END) as dikonfirmasi,
    SUM(CASE WHEN p.status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
    SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) as selesai,
    SUM(CASE WHEN pb.status_pembayaran = 'menunggu' AND p.status = 'dikonfirmasi' THEN 1 ELSE 0 END) as menunggu_bayar,
    SUM(CASE WHEN pb.status_pembayaran = 'lunas' AND p.status = 'dikonfirmasi' THEN 1 ELSE 0 END) as lunas
    FROM pemesanan p 
    LEFT JOIN pembayaran pb ON p.id = pb.pemesanan_id
    WHERE p.pemilik_id = ?";
$stmt_stats = $conn->prepare($query_stats);
$stmt_stats->bind_param("i", $pemilik_id);
$stmt_stats->execute();
$result_stats = $stmt_stats->get_result();
$stats = $result_stats->fetch_assoc();

// Proses aksi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pemesanan_id = $_POST['pemesanan_id'];
    $action = $_POST['action'];

    try {
        if ($action === 'konfirmasi') {
            $query_kos = "SELECT kos_id FROM pemesanan WHERE id = ?";
            $stmt_kos = $conn->prepare($query_kos);
            $stmt_kos->bind_param("i", $pemesanan_id);
            $stmt_kos->execute();
            $result_kos = $stmt_kos->get_result();
            $kos_data = $result_kos->fetch_assoc();

            if ($kos_data) {
                $conn->begin_transaction();

                // Update status pemesanan
                $query = "UPDATE pemesanan SET status = 'dikonfirmasi' 
                          WHERE id = ? AND pemilik_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $pemesanan_id, $pemilik_id);
                $stmt->execute();

                // Update status kos menjadi 'dipesan'
                $query_update_kos = "UPDATE kos SET status = 'dipesan' WHERE id = ?";
                $stmt_update_kos = $conn->prepare($query_update_kos);
                $stmt_update_kos->bind_param("i", $kos_data['kos_id']);
                $stmt_update_kos->execute();

                // Buat record pembayaran jika belum ada
                $query_check_pembayaran = "SELECT id FROM pembayaran WHERE pemesanan_id = ?";
                $stmt_check = $conn->prepare($query_check_pembayaran);
                $stmt_check->bind_param("i", $pemesanan_id);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();

                if ($result_check->num_rows === 0) {
                    $query_pemesanan = "SELECT total_harga FROM pemesanan WHERE id = ?";
                    $stmt_pemesanan = $conn->prepare($query_pemesanan);
                    $stmt_pemesanan->bind_param("i", $pemesanan_id);
                    $stmt_pemesanan->execute();
                    $result_pemesanan = $stmt_pemesanan->get_result();
                    $pemesanan_data = $result_pemesanan->fetch_assoc();

                    if ($pemesanan_data) {
                        $query_pembayaran = "INSERT INTO pembayaran 
                                            (pemesanan_id, jumlah_bayar, metode_pembayaran, status_pembayaran) 
                                            VALUES (?, ?, 'transfer', 'menunggu')";
                        $stmt_pembayaran = $conn->prepare($query_pembayaran);
                        $stmt_pembayaran->bind_param("id", $pemesanan_id, $pemesanan_data['total_harga']);
                        $stmt_pembayaran->execute();
                    }
                }

                $conn->commit();
                $_SESSION['success'] = "Pemesanan berhasil dikonfirmasi. Menunggu pembayaran dari penyewa.";
            }
        } elseif ($action === 'tolak') {
            $alasan = $_POST['alasan_penolakan'] ?? '';

            $query_kos = "SELECT kos_id FROM pemesanan WHERE id = ?";
            $stmt_kos = $conn->prepare($query_kos);
            $stmt_kos->bind_param("i", $pemesanan_id);
            $stmt_kos->execute();
            $result_kos = $stmt_kos->get_result();
            $kos_data = $result_kos->fetch_assoc();

            if ($kos_data) {
                $conn->begin_transaction();

                $query = "UPDATE pemesanan SET status = 'ditolak', catatan_pembatalan = ? 
                          WHERE id = ? AND pemilik_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sii", $alasan, $pemesanan_id, $pemilik_id);
                $stmt->execute();

                // Kembalikan status kos menjadi 'tersedia'
                $query_update_kos = "UPDATE kos SET status = 'tersedia' WHERE id = ?";
                $stmt_update_kos = $conn->prepare($query_update_kos);
                $stmt_update_kos->bind_param("i", $kos_data['kos_id']);
                $stmt_update_kos->execute();

                $conn->commit();
                $_SESSION['success'] = "Pemesanan berhasil ditolak dan kos kembali tersedia.";
            }
        } elseif ($action === 'selesai') {
            $query_kos = "SELECT kos_id FROM pemesanan WHERE id = ?";
            $stmt_kos = $conn->prepare($query_kos);
            $stmt_kos->bind_param("i", $pemesanan_id);
            $stmt_kos->execute();
            $result_kos = $stmt_kos->get_result();
            $kos_data = $result_kos->fetch_assoc();

            if ($kos_data) {
                $conn->begin_transaction();

                // Update status pemesanan dan penyewaan
                $query = "UPDATE pemesanan SET status = 'selesai', status_penyewaan = 'selesai' 
                          WHERE id = ? AND pemilik_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $pemesanan_id, $pemilik_id);
                $stmt->execute();

                // Kembalikan status kos menjadi 'tersedia'
                $query_update_kos = "UPDATE kos SET status = 'tersedia' WHERE id = ?";
                $stmt_update_kos = $conn->prepare($query_update_kos);
                $stmt_update_kos->bind_param("i", $kos_data['kos_id']);
                $stmt_update_kos->execute();

                $conn->commit();
                $_SESSION['success'] = "Penyewaan telah diselesaikan dan kos kembali tersedia.";
            }
        } elseif ($action === 'verifikasi_pembayaran') {
            $pembayaran_id = $_POST['pembayaran_id'];
            $verifikasi_action = $_POST['verifikasi_action'];

            $conn->begin_transaction();

            if ($verifikasi_action === 'terima') {
                // Update status pembayaran menjadi lunas
                $query_pembayaran = "UPDATE pembayaran SET status_pembayaran = 'lunas' WHERE id = ?";
                $stmt_pembayaran = $conn->prepare($query_pembayaran);
                $stmt_pembayaran->bind_param("i", $pembayaran_id);
                $stmt_pembayaran->execute();

                // Update status_pembayaran di pemesanan
                $query_pemesanan = "UPDATE pemesanan SET status_pembayaran = 'lunas' WHERE id = ?";
                $stmt_pemesanan = $conn->prepare($query_pemesanan);
                $stmt_pemesanan->bind_param("i", $pemesanan_id);
                $stmt_pemesanan->execute();

                $_SESSION['success'] = "Pembayaran berhasil diverifikasi dan diterima.";
            } elseif ($verifikasi_action === 'tolak') {
                $alasan_penolakan = $_POST['alasan_penolakan'] ?? '';

                // Update status pembayaran menjadi gagal
                $query_pembayaran = "UPDATE pembayaran SET status_pembayaran = 'gagal' WHERE id = ?";
                $stmt_pembayaran = $conn->prepare($query_pembayaran);
                $stmt_pembayaran->bind_param("i", $pembayaran_id);
                $stmt_pembayaran->execute();

                // Update status_pembayaran di pemesanan
                $query_pemesanan = "UPDATE pemesanan SET status_pembayaran = 'gagal' WHERE id = ?";
                $stmt_pemesanan = $conn->prepare($query_pemesanan);
                $stmt_pemesanan->bind_param("i", $pemesanan_id);
                $stmt_pemesanan->execute();

                $_SESSION['success'] = "Pembayaran ditolak. Penyewa dapat mengupload bukti baru.";
            }

            $conn->commit();
        }

        header("Location: pemesanan.php");
        exit;
    } catch (Exception $e) {
        if (isset($conn) && $conn->begin_transaction) {
            $conn->rollback();
        }
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pemesanan - INKOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            padding-top: 80px;
            background-color: #f8f9fa;
        }

        .card-pemesanan {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            background: white;
        }

        .kos-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }

        .modal-kos-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
        }

        .fasilitas-list {
            list-style: none;
            padding: 0;
        }

        .fasilitas-list li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        .fasilitas-list li:last-child {
            border-bottom: none;
        }

        .detail-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .stat-card {
            transition: transform 0.2s;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body>
    <?php include '../includes/pemilik_header.php'; ?>

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
                                            <?php
                                            $badge_class = [
                                                'menunggu' => 'bg-warning',
                                                'dikonfirmasi' => 'bg-success',
                                                'ditolak' => 'bg-danger',
                                                'selesai' => 'bg-info',
                                                'dibatalkan' => 'bg-secondary'
                                            ];
                                            $status = $pesan['status'];
                                            ?>
                                            <span class="badge <?= $badge_class[$status] ?> me-2">
                                                Status: <?= ucfirst($status) ?>
                                            </span>

                                            <span class="badge bg-<?=
                                                                    $pesan['status_kos'] == 'tersedia' ? 'success' : ($pesan['status_kos'] == 'dipesan' ? 'warning' : 'danger')
                                                                    ?>">
                                                Kos: <?= ucfirst(str_replace('_', ' ', $pesan['status_kos'])) ?>
                                            </span>

                                            <?php if ($pesan['status_pembayaran']): ?>
                                                <?php
                                                $payment_badge_class = [
                                                    'menunggu' => 'bg-warning',
                                                    'lunas' => 'bg-success',
                                                    'gagal' => 'bg-danger'
                                                ];
                                                $payment_status = $pesan['status_pembayaran'];
                                                ?>
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
                        <?php if ($pesan['catatan_pembatalan']): ?>
                            <div class="alert alert-warning mt-3 mb-0">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Alasan:</strong> <?= htmlspecialchars($pesan['catatan_pembatalan']) ?>
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
                                                    <span class="badge <?= $payment_badge_class[$payment_status] ?? 'bg-secondary' ?>">
                                                        <?= ucfirst($pesan['status_pembayaran']) ?>
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
    <script>
        // Auto close alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>

</html>