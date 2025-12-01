<?php
// admin_header.php
include 'header.php';

$current_page = basename($_SERVER['PHP_SELF']);
$nama_user = $_SESSION['nama'] ?? 'Admin';
$role_user = $_SESSION['role'] ?? 'admin';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - INKOS</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            padding-top: 76px;
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .nav-link.active {
            color: #0d6efd !important;
            font-weight: 600;
            background-color: rgba(13, 110, 253, 0.1);
            border-radius: 5px;
        }

        .user-info {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                margin-top: 1rem;
            }

            .user-info {
                margin-top: 10px;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand text-primary" href="../index.php">
                <i class="fas fa-home me-2"></i>
                INKOS
            </a>



            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Content -->
            <div class="collapse navbar-collapse" id="adminNavbar">
                <!-- Main Menu -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item mx-2">
                        <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link <?= ($current_page == 'users.php') ? 'active' : '' ?>" href="users.php">
                            <i class="fas fa-users me-2"></i>
                            User
                        </a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link <?= ($current_page == 'kos.php') ? 'active' : '' ?>" href="kos.php">
                            <i class="fas fa-building me-2"></i>
                            Kos
                        </a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link <?= ($current_page == 'daerah.php') ? 'active' : '' ?>" href="daerah.php">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Daerah
                        </a>
                    </li>
                   
                    <li class="nav-item mx-2">
                        <a class="nav-link <?= ($current_page == 'pemesanan.php') ? 'active' : '' ?>" href="pemesanan.php">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Pemesanan
                        </a>
                    </li>
                </ul>
                <!-- User Info and Actions - Visible on mobile -->
                <div class="d-lg-none mt-3">
                    <div class="user-info mb-3">
                        <i class="fas fa-user me-2 text-primary"></i>
                        <div class="d-inline">
                            <span class="fw-bold"><?= htmlspecialchars($nama_user) ?></span>
                            <small class="text-muted ms-2">(<?= ucfirst($role_user) ?>)</small>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="profile.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-edit me-1"></i>
                            Edit Profil
                        </a>
                        <a href="../index.php" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-home me-1"></i>
                            Kembali ke Beranda
                        </a>
                        <a href="../logout.php" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Logout
                        </a>
                    </div>
                </div>

                <!-- Desktop Actions -->
                <div class="d-none d-lg-flex align-items-center ms-3">
                    <a href="profile.php" class="btn btn-outline-primary btn-sm me-2">
                        <i class="fas fa-user-edit"></i>
                    </a>
                    <a href="../index.php" class="btn btn-outline-success btn-sm me-2">
                        <i class="fas fa-home"></i>
                    </a>
                    <a href="../logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Auto close mobile menu on link click
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('#adminNavbar .nav-link');
            const navbarCollapse = document.getElementById('adminNavbar');

            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                        bsCollapse.hide();
                    }
                });
            });
        });
    </script>

</body>

</html>