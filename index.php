<?php
// index.php
session_start();
include 'config.php';

// Cek session user
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? $_SESSION['user_role'] : '';
$userNama = $isLoggedIn ? $_SESSION['user_nama'] : '';
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

class HomePage
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Method untuk mendapatkan kos featured
    public function getFeaturedKos()
    {
        $query = "SELECT k.*, d.nama as nama_daerah, d.kota 
                  FROM kos k 
                  LEFT JOIN daerah d ON k.daerah_id = d.id 
                  WHERE k.featured = 1 AND k.status = 'tersedia' 
                  ORDER BY k.created_at DESC 
                  LIMIT 6";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Method untuk mendapatkan semua kos (untuk statistik)
    public function getAllKosCount()
    {
        $query = "SELECT COUNT(*) as total FROM kos WHERE status = 'tersedia'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Method untuk mendapatkan daerah populer
    public function getPopularDistricts()
    {
        $query = "SELECT d.*, COUNT(k.id) as jumlah_kos 
                  FROM daerah d 
                  LEFT JOIN kos k ON d.id = k.daerah_id AND k.status = 'tersedia'
                  GROUP BY d.id 
                  ORDER BY jumlah_kos DESC, d.nama ASC
                  LIMIT 8";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Method untuk mendapatkan semua daerah
    public function getAllDistricts()
    {
        $query = "SELECT * FROM daerah ORDER BY kota, nama";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Method untuk mendapatkan jumlah user terdaftar
    public function getUserCount()
    {
        try {
            $query = "SELECT COUNT(*) as total FROM users WHERE status = 'aktif'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException $e) {
            return 1000;
        }
    }

    // Method untuk mendapatkan jumlah daerah
    public function getDistrictCount()
    {
        $query = "SELECT COUNT(*) as total FROM daerah";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}

// Inisialisasi database
$database = new Database();
$db = $database->getConnection();
$homePage = new HomePage($db);

// Ambil data untuk home page
$featuredKos = $homePage->getFeaturedKos()->fetchAll(PDO::FETCH_ASSOC);
$popularDistricts = $homePage->getPopularDistricts()->fetchAll(PDO::FETCH_ASSOC);
$allDistricts = $homePage->getAllDistricts()->fetchAll(PDO::FETCH_ASSOC);

// Ambil data statistik
$totalKos = $homePage->getAllKosCount();
$totalDistricts = $homePage->getDistrictCount();
$totalUsers = $homePage->getUserCount();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INKOS - Temukan Kos Terbaik di Jambi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --accent: #ff6b6b;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #28a745;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1555854871-d6df97330e33?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            border-radius: 0 0 20px 20px;
        }

        .search-box {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-top: -40px;
            position: relative;
            z-index: 10;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border: none;
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
        }

        .section-title {
            position: relative;
            margin-bottom: 25px;
            font-weight: 700;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .card-kos {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        .card-kos:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .card-kos img {
            height: 180px;
            object-fit: cover;
        }

        .price-tag {
            position: absolute;
            top: 12px;
            right: 12px;
            background: var(--accent);
            color: white;
            padding: 4px 8px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .district-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 25px;
        }

        .district-btn {
            background: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 6px 15px;
            transition: all 0.3s;
            font-weight: 500;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .district-btn.active,
        .district-btn:hover {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border-color: transparent;
        }

        footer {
            background: var(--dark);
            color: white;
            padding: 40px 0 15px;
            margin-top: 60px;
        }

        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            margin-right: 8px;
            transition: all 0.3s;
        }

        .social-icons a:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }

        .stat-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            backdrop-filter: blur(5px);
            margin-right: 8px;
            margin-bottom: 8px;
            display: inline-block;
        }

        .features-section {
            background: white;
            border-radius: 15px;
            padding: 40px 20px;
            margin: 40px 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .feature-item {
            text-align: center;
            padding: 20px 15px;
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 1.3rem;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
                text-align: center;
            }

            .search-box {
                padding: 20px;
                margin-top: -30px;
            }

            .district-filter {
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar Sederhana -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-home me-2"></i>INKOS
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cari_kos.php">Cari Kos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tentang.php">Tentang</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?= htmlspecialchars($userNama) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                
                                <?php if ($userRole === 'pemilik'): ?>
                                    <li><a class="dropdown-item" href="pemilik/">Dashboard</a></li>
                                <?php elseif ($userRole === 'pencari'): ?>
                                    <li><a class="dropdown-item" href="pencari/">Dashboard</a></li>
                                <?php elseif ($userRole === 'admin'): ?>
                                    <li><a class="dropdown-item" href="admin/">Dashboard</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary me-2">Masuk</a>
                        <a href="register.php" class="btn btn-primary">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

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

    <!-- Search Section -->
    <section class="container">
        <div class="search-box">
            <h3 class="mb-3">Cari Kos Impian Anda</h3>
            <form action="cari_kos.php" method="GET" class="row g-2">
                <div class="col-md-4">
                    <select class="form-select" name="daerah">
                        <option value="">Semua Daerah</option>
                        <?php foreach ($allDistricts as $district): ?>
                            <option value="<?= $district['id'] ?>"><?= htmlspecialchars($district['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="tipe">
                        <option value="">Semua Tipe</option>
                        <option value="putra">Putra</option>
                        <option value="putri">Putri</option>
                        <option value="campur">Campur</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="harga">
                        <option value="">Semua Harga</option>
                        <option value="500000">≤ Rp 500.000</option>
                        <option value="1000000">≤ Rp 1.000.000</option>
                        <option value="1500000">≤ Rp 1.500.000</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Cari
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Popular Districts -->
    <section class="container my-5">
        <h2 class="section-title">Daerah Populer di Jambi</h2>
        <div class="district-filter">
            <button class="district-btn active" data-daerah="">Semua</button>
            <?php foreach ($popularDistricts as $district): ?>
                <button class="district-btn" data-daerah="<?= htmlspecialchars($district['nama']) ?>">
                    <?= htmlspecialchars($district['nama']) ?> (<?= $district['jumlah_kos'] ?>)
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Kos -->
    <section class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title">Kos Pilihan</h2>
            <a href="cari_kos.php" class="btn btn-outline-primary btn-sm">
                Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row" id="featured-kos-container">
            <?php if (count($featuredKos) > 0): ?>
                <?php foreach ($featuredKos as $kos): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card card-kos">
                            <div class="position-relative">
                                <img src="uploads/<?= htmlspecialchars($kos['foto_utama'] ?? 'default.jpg') ?>"
                                    class="card-img-top"
                                    alt="Kos <?= htmlspecialchars($kos['nama_kos'] ?? '') ?>"
                                    onerror="this.src='https://via.placeholder.com/400x200?text=No+Image'">
                                <div class="price-tag">Rp <?= number_format($kos['harga_bulanan'], 0, ',', '.') ?>/bln</div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($kos['nama_kos']) ?></h5>
                                <p class="card-text text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($kos['alamat']) .
                                        ($kos['daerah_id'] ? ' - ' . htmlspecialchars($kos['nama_daerah'] ?? '') : '')
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
                                            <span class="badge bg-light text-dark me-1"><?= htmlspecialchars($fasilitas_item) ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($fasilitas) > 2): ?>
                                            <span class="badge bg-light text-dark">+<?= count($fasilitas) - 2 ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">-</span>
                                    <?php endif; ?>
                                </div>
                                <a href="/pencari/detail_kos.php?id=<?= $kos['id'] ?>" class="btn btn-primary w-100 btn-sm">Lihat Detail</a>
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

    <!-- Features Section -->
    <section class="container">
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

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold">INKOS</h5>
                    <p class="mt-2 small">Platform pencarian kos terbaik di Jambi.</p>
                    <div class="social-icons mt-3">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Menu</h5>
                    <div class="footer-links small">
                        <a href="index.php">Beranda</a><br>
                        <a href="cari_kos.php">Cari Kos</a><br>
                        <a href="tentang.php">Tentang Kami</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Kontak</h5>
                    <div class="footer-links small">
                        <p><i class="fas fa-map-marker-alt me-2"></i> Jl. Contoh No. 123, Jambi</p>
                        <p><i class="fas fa-phone me-2"></i> (0741) 123-456</p>
                        <p><i class="fas fa-envelope me-2"></i> info@inkosjambi.com</p>
                    </div>
                </div>
            </div>
            <hr class="my-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 small">&copy; 2024 INKOS. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-light me-3 small">Privacy</a>
                    <a href="#" class="text-light small">Terms</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter kos berdasarkan daerah
        document.querySelectorAll('.district-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.district-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');

                const daerah = this.getAttribute('data-daerah');
                const cards = document.querySelectorAll('.card-kos');

                cards.forEach(card => {
                    const cardDaerah = card.querySelector('.card-text').textContent;
                    if (daerah === '' || cardDaerah.includes(daerah)) {
                        card.closest('.col-md-4').style.display = 'block';
                    } else {
                        card.closest('.col-md-4').style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>