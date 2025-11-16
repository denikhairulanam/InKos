<?php
// includes/pencari_header.php
$current_page = basename($_SERVER['PHP_SELF']);
$nama_user = $_SESSION['nama'] ?? 'Pencari';
$role_user = $_SESSION['role'] ?? 'pencari';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand text-primary" href="../index.php">
            <i class="fas fa-home me-2"></i>
            INKOS
           
        </a>

  
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#pencariNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Content -->
        <div class="collapse navbar-collapse" id="pencariNavbar">
            <!-- Main Menu -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>"
                        href="index.php">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        Dashboard
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'pemesanan.php') ? 'active' : '' ?>"
                        href="pemesanan.php">
                        <i class="fas fa-history me-1"></i>
                        Pemesanan
                    </a>
                </li>
                
            </ul>

            <!-- User Info and Actions - Visible on mobile -->
            <div class="d-lg-none mt-3">
                <div class="user-info mb-3">
                    <i class="fas fa-user me-2 text-info"></i>
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

<style>
    .user-info {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 5px;
        border: 1px solid #dee2e6;
    }

    .nav-link.active {
        color: #0d6efd !important;
        font-weight: 600;
        background-color: rgba(13, 110, 253, 0.1);
        border-radius: 5px;
    }
</style>