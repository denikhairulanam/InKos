<?php
// pencari/dashboard.php
session_start();

// Include config
require_once '../config.php';

// Init database connection
$database = new Database();
$conn = $database->getConnection();

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pencari') {
    header('Location: ../login.php');
    exit;
}

$pencari_id = $_SESSION['user_id'];
$nama_user = $_SESSION['user_nama'] ?? $_SESSION['nama'] ?? 'Pencari';

try {

    // Statistik pemesanan
    $query_stats = "SELECT 
        COUNT(*) as total_pemesanan,
        SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
        SUM(CASE WHEN status = 'dikonfirmasi' THEN 1 ELSE 0 END) as dikonfirmasi,
        SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
        SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak
        FROM pemesanan WHERE pencari_id = ?";

    $stmt_stats = $conn->prepare($query_stats);
    $stmt_stats->execute([$pencari_id]);
    $stats = $stmt_stats->fetch() ?: [
        'total_pemesanan' => 0,
        'menunggu' => 0,
        'dikonfirmasi' => 0,
        'selesai' => 0,
        'ditolak' => 0
    ];

    // Pemesanan terbaru
    $query_pemesanan = "SELECT p.*, k.nama_kos, k.alamat, k.foto_utama, d.nama as nama_daerah,
                               pb.status_pembayaran
                        FROM pemesanan p 
                        JOIN kos k ON p.kos_id = k.id 
                        JOIN daerah d ON k.daerah_id = d.id
                        LEFT JOIN pembayaran pb ON p.id = pb.pemesanan_id
                        WHERE p.pencari_id = ? 
                        ORDER BY p.tanggal_pemesanan DESC 
                        LIMIT 5";

    $stmt_pemesanan = $conn->prepare($query_pemesanan);
    $stmt_pemesanan->execute([$pencari_id]);
    $pemesanan_terbaru = $stmt_pemesanan->fetchAll();

    // Rekomendasi kos
    $query_rekomendasi = "SELECT k.*, d.nama as nama_daerah, 
                                 COUNT(p.id) as jumlah_pemesanan
                          FROM kos k 
                          JOIN daerah d ON k.daerah_id = d.id
                          LEFT JOIN pemesanan p ON k.id = p.kos_id
                          WHERE k.status = 'tersedia'
                          GROUP BY k.id 
                          ORDER BY jumlah_pemesanan DESC, k.created_at DESC 
                          LIMIT 6";

    $stmt_rekomendasi = $conn->prepare($query_rekomendasi);
    $stmt_rekomendasi->execute();
    $rekomendasi_kos = $stmt_rekomendasi->fetchAll();
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());

    // Default jika error
    $stats = [
        'total_pemesanan' => 0,
        'menunggu' => 0,
        'dikonfirmasi' => 0,
        'selesai' => 0,
        'ditolak' => 0
    ];
    $pemesanan_terbaru = [];
    $rekomendasi_kos = [];
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pencari - INKOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }

        .stat-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            color: white;
        }

        .card-kos {
            border: none;
            border-radius: 12px;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        .card-kos:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
        }

        .card-kos img {
            height: 160px;
            object-fit: cover;
            border-radius: 12px 12px 0 0;
        }

        .price-tag {
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status-badge {
            font-size: 0.75rem;
        }

        .quick-action-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .quick-action-card:hover {
            border-color: #0d6efd;
            transform: scale(1.05);
            color: inherit;
            text-decoration: none;
        }

        .quick-action-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
        }
    </style>
</head>

<body>
    <?php
    // Include header
    include '../includes/header/pencari_header.php';
    ?>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="welcome-section p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2">Halo, <?= htmlspecialchars($nama_user) ?>! 👋</h2>
                    <p class="mb-0">Selamat datang di dashboard pencari kos. Temukan kos impian Anda dengan mudah.</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-white text-primary rounded-pill px-4 py-2 d-inline-block">
                        <i class="fas fa-calendar-day me-2"></i>
                        <?= date('l, d F Y') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3><?= $stats['total_pemesanan'] ?></h3>
                                <p class="mb-0">Total Pemesanan</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-shopping-cart fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3><?= $stats['menunggu'] ?></h3>
                                <p class="mb-0">Menunggu</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3><?= $stats['dikonfirmasi'] ?></h3>
                                <p class="mb-0">Dikonfirmasi</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3><?= $stats['selesai'] ?></h3>
                                <p class="mb-0">Selesai</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-flag-checkered fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Pemesanan Terbaru -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2 text-warning"></i>Pemesanan Terbaru
                        </h5>
                        <a href="pemesanan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pemesanan_terbaru)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-3"></i>
                                <p class="mb-0">Belum ada pemesanan</p>
                                <small>Mulai cari kos untuk melakukan pemesanan</small>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($pemesanan_terbaru as $pesan): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= htmlspecialchars($pesan['nama_kos']) ?></h6>
                                                <p class="mb-1 small text-muted">
                                                    <?= date('d M Y', strtotime($pesan['tanggal_mulai'])) ?> -
                                                    <?= date('d M Y', strtotime($pesan['tanggal_selesai'])) ?>
                                                </p>
                                                <div class="d-flex align-items-center">
                                                    <?php
                                                    $badge_class = [
                                                        'menunggu' => 'bg-warning',
                                                        'dikonfirmasi' => 'bg-success',
                                                        'ditolak' => 'bg-danger',
                                                        'selesai' => 'bg-info'
                                                    ];
                                                    $status = $pesan['status'] ?? 'menunggu';
                                                    ?>
                                                    <span class="badge <?= $badge_class[$status] ?> status-badge me-2">
                                                        <?= ucfirst($status) ?>
                                                    </span>
                                                    <?php if (isset($pesan['status_pembayaran'])): ?>
                                                        <span class="badge bg-secondary status-badge">
                                                            <?= ucfirst($pesan['status_pembayaran']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <strong class="text-success d-block">
                                                    Rp <?= number_format($pesan['total_harga'] ?? 0, 0, ',', '.') ?>
                                                </strong>
                                                <small class="text-muted">
                                                    <?= date('d M', strtotime($pesan['tanggal_pemesanan'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Rekomendasi Kos -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-star me-2 text-warning"></i>Rekomendasi Kos
                        </h5>
                        <a href="../index.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (empty($rekomendasi_kos)): ?>
                                <div class="col-12 text-center text-muted py-4">
                                    <i class="fas fa-home fa-2x mb-3"></i>
                                    <p class="mb-0">Belum ada rekomendasi kos</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($rekomendasi_kos as $kos): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card card-kos h-100">
                                            <img src="../uploads/<?= htmlspecialchars($kos['foto_utama'] ?? 'default.jpg') ?>"
                                                class="card-img-top"
                                                alt="<?= htmlspecialchars($kos['nama_kos'] ?? 'Kos') ?>"
                                                onerror="this.src='https://via.placeholder.com/400x200?text=No+Image'">
                                            <div class="card-body">
                                                <h6 class="card-title"><?= htmlspecialchars($kos['nama_kos'] ?? 'Kos') ?></h6>
                                                <p class="card-text small text-muted mb-2">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?= htmlspecialchars($kos['nama_daerah'] ?? '') ?>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="price-tag">
                                                        Rp <?= number_format($kos['harga_bulanan'] ?? 0, 0, ',', '.') ?>
                                                    </span>
                                                    <a href="detail_kos.php?id=<?= $kos['id'] ?>"
                                                        class="btn btn-sm btn-outline-primary">
                                                        Lihat
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Tips -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb me-2 text-warning"></i>Tips Mencari Kos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-map-marked-alt text-primary fa-2x me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6>Pilih Lokasi Strategis</h6>
                                        <p class="small text-muted mb-0">Pilih kos dekat dengan kampus atau tempat kerja untuk menghemat waktu perjalanan.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-money-bill-wave text-success fa-2x me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6>Perhatikan Budget</h6>
                                        <p class="small text-muted mb-0">Sesuaikan dengan kemampuan finansial dan pertimbangkan biaya tambahan.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-shield-alt text-warning fa-2x me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6>Periksa Keamanan</h6>
                                        <p class="small text-muted mb-0">Pastikan lingkungan kos aman dan memiliki sistem keamanan yang memadai.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include '../includes/footer/footer.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>