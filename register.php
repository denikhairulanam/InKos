<?php
// register.php
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h3 class="mb-0"><i class="fas fa-home me-2"></i>INKOS</h3>
                        <p class="mb-0 mt-2">Buat akun baru</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                <div class="mt-2">
                                    <a href="login.php" class="btn btn-success btn-sm">Login Sekarang</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control" id="nama" name="nama"
                                            value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>"
                                            placeholder="Nama lengkap" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                            placeholder="Email Anda" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="telepon" class="form-label">Nomor Telepon</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                    <input type="tel" class="form-control" id="telepon" name="telepon"
                                        value="<?php echo isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : ''; ?>"
                                        placeholder="Nomor telepon">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Daftar sebagai</label>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check card border h-100">
                                            <input class="form-check-input position-absolute top-0 start-0 m-2"
                                                type="radio" name="role" value="pencari"
                                                <?php echo (isset($_POST['role']) && $_POST['role'] === 'pencari') || !isset($_POST['role']) ? 'checked' : ''; ?>
                                                id="rolePencari" required>
                                            <label class="form-check-label card-body text-center" for="rolePencari">
                                                <i class="fas fa-search fa-2x text-primary mb-2"></i>
                                                <h6>Pencari Kos</h6>
                                                <small class="text-muted">Cari kos yang sesuai</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check card border h-100">
                                            <input class="form-check-input position-absolute top-0 start-0 m-2"
                                                type="radio" name="role" value="pemilik"
                                                <?php echo (isset($_POST['role']) && $_POST['role'] === 'pemilik') ? 'checked' : ''; ?>
                                                id="rolePemilik" required>
                                            <label class="form-check-label card-body text-center" for="rolePemilik">
                                                <i class="fas fa-home fa-2x text-success mb-2"></i>
                                                <h6>Pemilik Kos</h6>
                                                <small class="text-muted">Pasang iklan kos</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Min. 6 karakter" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                            placeholder="Ulangi password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="agree" name="agree" required>
                                <label class="form-check-label" for="agree">
                                    Saya menyetujui syarat dan ketentuan
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-user-plus me-2"></i>Daftar
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <p class="mb-0">Sudah punya akun?
                                <a href="login.php" class="text-decoration-none">Login di sini</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="JavaScript/registrasi.js"></script>
</body>

</html>