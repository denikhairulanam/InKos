<?php
// Mulai output buffering
ob_start();

// Perbaiki path include
include __DIR__ . '/../config.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Cek apakah parameter id ada
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID kos tidak valid";
    header("Location: ../index.php");
    exit();
}

$kos_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Initialize variables dengan default values
$kos = null;
$fasilitas = [];
$foto_lainnya = [];

// Initialize database connection
try {
    $database = new Database();
    $db = $database->getConnection();

    // Ambil data kos berdasarkan ID
    $query = "SELECT k.*, u.nama as pemilik_nama, u.email as pemilik_email, 
                     u.telepon as pemilik_telepon, u.foto_profil as pemilik_foto,
                     d.nama as daerah_nama, d.kota, d.latitude, d.longitude
              FROM kos k 
              LEFT JOIN users u ON k.user_id = u.id 
              LEFT JOIN daerah d ON k.daerah_id = d.id 
              WHERE k.id = :id AND k.status = 'tersedia'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $kos_id);
    $stmt->execute();
    $kos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kos) {
        $_SESSION['error'] = "Kos tidak ditemukan atau sudah tidak tersedia";
        header("Location: ../index.php");
        exit();
    }

    // Ambil fasilitas dari JSON
    if (!empty($kos['fasilitas'])) {
        $fasilitas_data = json_decode($kos['fasilitas'], true);
        if (is_array($fasilitas_data)) {
            $fasilitas = $fasilitas_data;
        }
    }

    // Ambil foto lainnya dari JSON
    if (!empty($kos['foto_lainnya'])) {
        $foto_data = json_decode($kos['foto_lainnya'], true);
        if (is_array($foto_data)) {
            $foto_lainnya = $foto_data;
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error mengambil data: " . $e->getMessage();
    header("Location: ../index.php");
    exit();
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: ../index.php");
    exit();
}

// Jika $kos masih null, redirect ke index
if (!$kos) {
    $_SESSION['error'] = "Data kos tidak ditemukan";
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($kos['nama_kos'] ?? 'Detail Kos'); ?> - Detail Kos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-image {
            height: 400px;
            object-fit: cover;
            width: 100%;
            border-radius: 10px;
        }

        .gallery-thumb {
            height: 80px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 5px;
        }

        .gallery-thumb:hover,
        .gallery-thumb.active {
            border-color: #007bff;
        }

        .price-tag {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            text-align: center;
        }

        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .profile-image-fallback {
            width: 35px;
            height: 35px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container px-4 py-4">

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Cek jika data kos ada -->
        <?php if ($kos): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3"><i class="fas fa-eye me-2"></i>Detail Kos</h2>
                <a href="../index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <div class="row g-4">
                <!-- Main Info -->
                <div class="col-lg-8">
                    <!-- Photos -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Foto Kos</h5>
                            <?php if ($kos['foto_utama'] || !empty($foto_lainnya)): ?>
                                <div class="row g-3">
                                    <?php if ($kos['foto_utama']): ?>
                                        <div class="col-12">
                                            <img src="../uploads/<?php echo htmlspecialchars($kos['foto_utama']); ?>"
                                                class="img-fluid rounded" alt="Foto Utama"
                                                style="max-height: 400px; width: 100%; object-fit: cover;"
                                                id="mainImage">
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($foto_lainnya)): ?>
                                        <div class="col-12">
                                            <h6 class="mt-3 mb-2">Gambar Lainnya</h6>
                                            <div class="row g-2">
                                                <?php foreach ($foto_lainnya as $foto): ?>
                                                    <div class="col-md-3 col-4">
                                                        <img src="../uploads/<?php echo htmlspecialchars($foto); ?>"
                                                            class="img-fluid rounded gallery-thumb"
                                                            alt="Foto Kos"
                                                            style="height: 100px; width: 100%; object-fit: cover; cursor: pointer;"
                                                            onclick="changeMainImage(this.src)">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada foto</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Deskripsi</h5>
                            <p class="card-text"><?php echo $kos['deskripsi'] ? nl2br(htmlspecialchars($kos['deskripsi'])) : '<span class="text-muted">Tidak ada deskripsi</span>'; ?></p>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Alamat Lengkap</h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($kos['alamat'])); ?></p>
                            <?php if ($kos['daerah_nama']): ?>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <?php echo htmlspecialchars($kos['daerah_nama']); ?>, <?php echo htmlspecialchars($kos['kota']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="col-lg-4">
                    <!-- Basic Info -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">Informasi Kos</h5>
                                <div>
                                    <?php if ($kos['featured']): ?>
                                        <span class="badge bg-warning"><i class="fas fa-star me-1"></i>Featured</span>
                                    <?php endif; ?>
                                    <span class="badge <?php echo $kos['status'] == 'tersedia' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $kos['status'] == 'tersedia' ? 'Tersedia' : 'Tidak Tersedia'; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="price-tag mb-3">
                                <h3 class="fw-bold mb-1">Rp <?php echo number_format($kos['harga_bulanan'], 0, ',', '.'); ?></h3>
                                <p class="mb-0">per bulan</p>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Tipe Kos</small>
                                    <p class="mb-0">
                                        <span class="badge 
                                            <?php echo $kos['tipe_kos'] == 'putra' ? 'bg-primary' : ($kos['tipe_kos'] == 'putri' ? 'bg-danger' : 'bg-success'); ?>">
                                            <?php echo ucfirst($kos['tipe_kos']); ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Kamar Mandi</small>
                                    <p class="mb-0">
                                        <span class="badge bg-info"><?php echo ucfirst($kos['kamar_mandi']); ?></span>
                                    </p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Ukuran Kamar</small>
                                    <p class="mb-0"><strong><?php echo htmlspecialchars($kos['ukuran_kamar']); ?></strong></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Views</small>
                                    <p class="mb-0"><strong><?php echo number_format($kos['views']); ?></strong></p>
                                </div>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="d-grid gap-2">
                                <?php if (!empty($kos['pemilik_telepon'])): ?>
                                    <a href="https://wa.me/62<?php echo substr($kos['pemilik_telepon'], 1); ?>?text=Halo, saya tertarik dengan kos <?php echo urlencode($kos['nama_kos']); ?> di <?php echo urlencode($kos['alamat']); ?>"
                                        class="btn btn-success btn-lg" target="_blank">
                                        <i class="fab fa-whatsapp me-2"></i>Hubungi via WhatsApp
                                    </a>
                                <?php endif; ?>
                                <a href="booking_form.php?kos_id=<?= $kos['id'] ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-calendar-plus me-2"></i>Pesan Sekarang
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Facilities -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Fasilitas</h5>
                            <?php if (!empty($fasilitas)): ?>
                                <div class="row g-2">
                                    <?php foreach ($fasilitas as $fasilitas_item): ?>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-check text-success me-2"></i>
                                                <small><?php echo htmlspecialchars($fasilitas_item); ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">Tidak ada fasilitas</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Owner Info -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Informasi Pemilik</h5>
                            <div class="d-flex align-items-center mb-3">
                                <?php if ($kos['pemilik_foto'] && $kos['pemilik_foto'] !== 'default.jpg'): ?>
                                    <img src="../uploads/profiles/<?php echo htmlspecialchars($kos['pemilik_foto']); ?>"
                                        class="rounded-circle me-3" width="50" height="50" alt="Pemilik">
                                <?php else: ?>
                                    <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center"
                                        style="width: 50px; height: 50px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($kos['pemilik_nama']); ?></h6>
                                    <small class="text-muted">Pemilik Kos</small>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-12">
                                    <small class="text-muted">Email</small>
                                    <p class="mb-2"><a href="mailto:<?php echo htmlspecialchars($kos['pemilik_email']); ?>"><?php echo htmlspecialchars($kos['pemilik_email']); ?></a></p>
                                </div>
                                <?php if ($kos['pemilik_telepon']): ?>
                                    <div class="col-12">
                                        <small class="text-muted">Telepon</small>
                                        <p class="mb-0"><?php echo htmlspecialchars($kos['pemilik_telepon']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Info -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Informasi Sistem</h5>
                            <div class="row g-2">
                                <div class="col-12">
                                    <small class="text-muted">ID Kos</small>
                                    <p class="mb-2"><code><?php echo $kos['id']; ?></code></p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Dibuat</small>
                                    <p class="mb-2"><?php echo date('d/m/Y H:i', strtotime($kos['created_at'])); ?></p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Terakhir Update</small>
                                    <p class="mb-0"><?php echo date('d/m/Y H:i', strtotime($kos['updated_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Data kos tidak ditemukan.
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;

            // Update active thumbnail
            document.querySelectorAll('.gallery-thumb').forEach(thumb => {
                thumb.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Auto dismiss alerts
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>

</html>
<?php ob_end_flush(); ?>