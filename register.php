<?php
session_start();
include 'config.php';
include 'controler/register.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - INKOS</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/registrasi.css">
</head>

<body class="d-flex justify-content-center align-items-center min-vh-100 p-3">

    <div class="card shadow-sm p-4" style="width:100%;max-width:450px;">
        <!-- Header -->
        <div class="text-center mb-4">
            <div class="d-flex justify-content-center align-items-center mb-3">
                <i class="bi bi-house-door-fill text-primary fs-2 me-2"></i>
                <h3 class="fw-bold text-primary mb-0">INKOS</h3>
            </div>
            <p class="text-muted mb-0">Buat akun baru</p>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($success)) : ?>
            <div class="alert alert-success p-3 mb-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div class="flex-grow-1"><?= htmlspecialchars($success) ?></div>
                </div>
                <a href="login.php" class="btn btn-success btn-sm w-100 mt-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login Sekarang
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger p-3 mb-3 d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div class="flex-grow-1"><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <!-- Register Form -->
        <form method="POST" autocomplete="off">
            <!-- Nama Lengkap -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-person text-muted"></i>
                    </span>
                    <input type="text"
                        name="nama"
                        class="form-control"
                        required
                        placeholder="Masukkan nama lengkap"
                        value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
                </div>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-envelope text-muted"></i>
                    </span>
                    <input type="email"
                        name="email"
                        class="form-control"
                        required
                        placeholder="Masukkan email"
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
            </div>

            <!-- Telepon -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Nomor Telepon</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-telephone text-muted"></i>
                    </span>
                    <input type="tel"
                        name="telepon"
                        class="form-control"
                        placeholder="Masukkan nomor telepon"
                        value="<?= isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : '' ?>">
                </div>
            </div>

            <!-- Role -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Daftar sebagai</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-person-badge text-muted"></i>
                    </span>
                    <select name="role" class="form-select" required>
                        <option value="pencari" <?= (isset($_POST['role']) && $_POST['role'] == 'pemilik') ? '' : 'selected' ?>>Pencari Kos</option>
                        <option value="pemilik" <?= (isset($_POST['role']) && $_POST['role'] == 'pemilik') ? 'selected' : '' ?>>Pemilik Kos</option>
                    </select>
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3 password-wrapper">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <input type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        required
                        placeholder="Masukkan password">
                    <button type="button"
                        class="password-toggle"
                        onclick="togglePassword('password', 'eyeIcon')">
                        <i id="eyeIcon" class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-3 password-wrapper">
                <label class="form-label fw-semibold">Konfirmasi Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-lock-fill text-muted"></i>
                    </span>
                    <input type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                        required
                        placeholder="Konfirmasi password">
                    <button type="button"
                        class="password-toggle"
                        onclick="togglePassword('confirm_password', 'eyeIconConfirm')">
                        <i id="eyeIconConfirm" class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <!-- Terms Agreement -->
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="agree" name="agree" required>
                <label for="agree" class="form-check-label">
                    Saya menyetujui <a href="#" class="text-decoration-none">syarat dan ketentuan</a>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                <i class="bi bi-person-plus me-2"></i>Daftar
            </button>
        </form>

        <!-- Login Link -->
        <div class="text-center mt-4 pt-3 border-top">
            <span class="text-muted">Sudah punya akun?</span>
            <a href="login.php" class="btn btn-outline-primary w-100 mt-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </a>
        </div>
    </div>

    <script src="javascript/registrasi.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>