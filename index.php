<?php
// index.php
session_start();
include 'config.php';
include 'includes/header/guest_header.php';
include 'controler/home.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INKOS - Temukan Kos Terbaik di Jambi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/home.css">
</head>

<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-5 fw-bold mb-3">Temukan Kos Terbaik di Jambi</h1>
                    <p class="lead mb-4">Cari kos sesuai kebutuhan dengan harga terjangkau</p>
                </div>
            </div>
        </div>
    </section>

    <section class="container">
        <div class="search-box">
            <form method="GET" action="" class="search-form">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" name="search"
                        placeholder="Cari kos berdasarkan nama, lokasi, atau fasilitas..."
                        value="<?= htmlspecialchars($search_keyword) ?>">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-search me-1"></i>Cari
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Main Content -->
    <section class="container my-5">
        <?php if ($is_searching): ?>
            <!-- Tampilkan Hasil Pencarian -->
            <div class="search-results-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="section-title mb-1">Hasil Pencarian</h2>
                        <p class="text-muted mb-0">
                            Ditemukan <?php echo count($searchResults); ?> hasil
                            <?php if ($search_daerah): ?>
                                di <?php
                                    $daerah_name = '';
                                    foreach ($allDistricts as $d) {
                                        if ($d['id'] == $search_daerah) {
                                            $daerah_name = $d['nama'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($daerah_name);
                                    ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Reset Pencarian
                    </a>
                </div>
            </div>

            <div class="row" id="search-results-container">
                <?php if (count($searchResults) > 0): ?>
                    <?php foreach ($searchResults as $kos): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card card-kos">
                                <div class="position-relative">
                                    <?php if ($kos['foto_utama']): ?>
                                        <img src="uploads/<?= htmlspecialchars($kos['foto_utama']) ?>"
                                            class="card-img-top kos-image"
                                            alt="Kos <?= htmlspecialchars($kos['nama_kos']) ?>"
                                            onerror="this.src='https://via.placeholder.com/400x200?text=No+Image'">
                                    <?php else: ?>
                                        <div class="kos-image bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-home fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="price-tag">Rp <?= number_format($kos['harga_bulanan'], 0, ',', '.') ?>/bln</div>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($kos['nama_kos']) ?></h5>
                                    <p class="card-text text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        <?= htmlspecialchars($kos['alamat']) .
                                            ($kos['nama_daerah'] ? ' - ' . htmlspecialchars($kos['nama_daerah']) : '')
                                        ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-<?= $kos['tipe_kos'] == 'putra' ? 'primary' : ($kos['tipe_kos'] == 'putri' ? 'danger' : 'warning') ?>">
                                            <i class="fas fa-<?= $kos['tipe_kos'] == 'putra' ? 'male' : ($kos['tipe_kos'] == 'putri' ? 'female' : 'users') ?> me-1"></i>
                                            <?= ucfirst($kos['tipe_kos']) ?>
                                        </span>
                                        <small class="text-muted">
                                            <i class="fas fa-expand-arrows-alt me-1"></i> <?= htmlspecialchars($kos['ukuran_kamar']) ?>
                                        </small>
                                    </div>
                                    <div class="mb-3">
                                        <?php
                                        $fasilitas = json_decode($kos['fasilitas'] ?? '[]', true);
                                        if (is_array($fasilitas) && !empty($fasilitas)):
                                            $limited_fasilitas = array_slice($fasilitas, 0, 2);
                                        ?>
                                            <?php foreach ($limited_fasilitas as $fasilitas_item): ?>
                                                <span class="badge bg-light text-dark me-1 mb-1"><?= htmlspecialchars($fasilitas_item) ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($fasilitas) > 2): ?>
                                                <span class="badge bg-light text-dark">+<?= count($fasilitas) - 2 ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">Fasilitas dasar</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-auto">
                                        <a href="/pencari/detail_kos.php?id=<?= $kos['id'] ?>" class="btn btn-primary w-100 btn-sm">Lihat Detail</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="no-results text-center">
                            <div>
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Tidak ada kos yang ditemukan</h4>
                                <p class="text-muted mb-4">Coba ubah filter pencarian Anda atau gunakan kata kunci lain.</p>
                                <a href="index.php" class="btn btn-primary">Reset Pencarian</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Tampilkan Featured Kos (Default View) -->

            <!-- Popular Districts -->
            <section class="my-5">
                <h2 class="section-title">Daerah Populer di Jambi</h2>
                <div class="district-filter">
                    <a href="index.php" class="district-btn active">Semua</a>
                    <?php foreach ($popularDistricts as $district): ?>
                        <a href="index.php?daerah=<?= $district['id'] ?>" class="district-btn">
                            <?= htmlspecialchars($district['nama']) ?> (<?= $district['jumlah_kos'] ?>)
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Featured Kos -->
            <section class="my-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title">Kos Pilihan</h2>
                </div>
                <div class="row" id="featured-kos-container">
                    <?php if (count($featuredKos) > 0): ?>
                        <?php foreach ($featuredKos as $kos): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card card-kos">
                                    <div class="position-relative">
                                        <?php if ($kos['foto_utama']): ?>
                                            <img src="uploads/<?= htmlspecialchars($kos['foto_utama']) ?>"
                                                class="card-img-top kos-image"
                                                alt="Kos <?= htmlspecialchars($kos['nama_kos']) ?>"
                                                onerror="this.src='https://via.placeholder.com/400x200?text=No+Image'">
                                        <?php else: ?>
                                            <div class="kos-image bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-home fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="price-tag">Rp <?= number_format($kos['harga_bulanan'], 0, ',', '.') ?>/bln</div>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?= htmlspecialchars($kos['nama_kos']) ?></h5>
                                        <p class="card-text text-muted small mb-2">
                                            <i class="fas fa-map-marker-alt text-danger"></i>
                                            <?= htmlspecialchars($kos['alamat']) .
                                                ($kos['nama_daerah'] ? ' - ' . htmlspecialchars($kos['nama_daerah']) : '')
                                            ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-<?= $kos['tipe_kos'] == 'putra' ? 'primary' : ($kos['tipe_kos'] == 'putri' ? 'danger' : 'warning') ?>">
                                                <i class="fas fa-<?= $kos['tipe_kos'] == 'putra' ? 'male' : ($kos['tipe_kos'] == 'putri' ? 'female' : 'users') ?> me-1"></i>
                                                <?= ucfirst($kos['tipe_kos']) ?>
                                            </span>
                                            <small class="text-muted">
                                                <i class="fas fa-expand-arrows-alt me-1"></i> <?= htmlspecialchars($kos['ukuran_kamar']) ?>
                                            </small>
                                        </div>
                                        <div class="mb-3">
                                            <?php
                                            $fasilitas = json_decode($kos['fasilitas'] ?? '[]', true);
                                            if (is_array($fasilitas) && !empty($fasilitas)):
                                                $limited_fasilitas = array_slice($fasilitas, 0, 2);
                                            ?>
                                                <?php foreach ($limited_fasilitas as $fasilitas_item): ?>
                                                    <span class="badge bg-light text-dark me-1 mb-1"><?= htmlspecialchars($fasilitas_item) ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($fasilitas) > 2): ?>
                                                    <span class="badge bg-light text-dark">+<?= count($fasilitas) - 2 ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark">Fasilitas dasar</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-auto">
                                            <a href="/pencari/detail_kos.php?id=<?= $kos['id'] ?>" class="btn btn-primary w-100 btn-sm">Lihat Detail</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-home fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Belum ada kos yang tersedia</h4>
                            <p>Jadilah yang pertama untuk memasang iklan kos di daerah Anda.</p>
                            <?php if (!$isLoggedIn): ?>
                                <a href="register.php" class="btn btn-primary">Daftar Sekarang</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </section>

    <!-- Features Section -->
    <?php if (!$is_searching): ?>
        <section class="container my-5">
            <div class="features-section">
                <h2 class="section-title text-center mb-4">Mengapa Memilih INKOS?</h2>
                <div class="row">
                    <div class="col-md-4">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h5>Cari Mudah</h5>
                            <p class="text-muted small">Temukan kos sesuai kriteria dengan filter lengkap</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h5>Aman Terpercaya</h5>
                            <p class="text-muted small">Semua kos telah diverifikasi untuk keamanan Anda</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h5>Bantuan 24/7</h5>
                            <p class="text-muted small">Tim support siap membantu kapan saja</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php include 'includes/footer/footer_guest.php' ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php include 'JavaScript/home.js' ?>
    </script>
</body>

</html>