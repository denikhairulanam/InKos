<?php
// Mulai sesi untuk menangani data login dan pesan
session_start();
// Mengambil file controller untuk menangani proses login
require_once 'controler/login.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - INKOS</title>

    <!-- Memuat CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Memuat ikon Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- CSS tambahan untuk halaman login -->
    <link rel="stylesheet" href="css/login.css">
</head>

<body class="d-flex justify-content-center align-items-center min-vh-100 p-3">

    <div class="card shadow-sm p-4" style="width:100%;max-width:400px;">
        <!-- Header Judul Login -->
        <div class="text-center mb-4">
            <div class="d-flex justify-content-center align-items-center mb-3">
                <!-- Ikon Rumah -->
                <i class="bi bi-house-door-fill text-primary fs-2 me-2"></i>
                <h3 class="fw-bold text-primary mb-0">INKOS</h3>
            </div>
            <p class="text-muted mb-0">Sistem Informasi Kos</p>
        </div>

        <!-- Pesan Error Jika Login Gagal -->
        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger p-3 mb-3 d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div class="flex-grow-1"><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <!-- Pesan Sukses Jika Login Atau Register Berhasil -->
        <?php if (isset($_SESSION['success'])) : ?>
            <div class="alert alert-success p-3 mb-3 d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div class="flex-grow-1"><?= htmlspecialchars($_SESSION['success']) ?></div>
            </div>
            <!-- Hapus pesan setelah ditampilkan -->
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Formulir Login -->
        <form method="POST" autocomplete="off">

            <!-- Input Email -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email"
                    name="email"
                    required
                    class="form-control"
                    placeholder="Masukkan email Anda"
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>

            <!-- Input Password -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Kata Sandi</label>
                <div class="input-group">
                    <input type="password"
                        id="password"
                        name="password"
                        required
                        class="form-control"
                        placeholder="Masukkan kata sandi">
                </div>
            </div>

            <!-- Opsi Remember Me -->
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Ingat saya</label>
            </div>

            <!-- Tombol Login -->
            <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>

        </form>

        <!-- Link Ke Halaman Register -->
        <div class="text-center mt-4 pt-3 border-top">
            <p class="text-muted mb-2">Belum punya akun?</p>
            <a href="register.php" class="btn btn-outline-primary w-100">
                <i class="bi bi-person-plus me-2"></i>Daftar Akun Baru
            </a>
        </div>
    </div>

    <script>
        // Script untuk menampilkan/menyembunyikan password
        const togglePassword = document.getElementById("togglePassword");
        const password = document.getElementById("password");
        const eyeIcon = document.getElementById("eyeIcon");

        // Event klik tombol mata (jika ada)
        if (togglePassword) {
            togglePassword.addEventListener("click", () => {
                const type = password.getAttribute("type") === "password" ? "text" : "password";
                password.setAttribute("type", type);

                eyeIcon.classList.toggle("bi-eye");
                eyeIcon.classList.toggle("bi-eye-slash");
            });
        }
    </script>
    <!-- JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
