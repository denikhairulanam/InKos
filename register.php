<?php
// register.php
session_start();
include 'config.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $telepon = trim($_POST['telepon']);
    $role = $_POST['role'];

    // Validasi input
    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Semua field wajib diisi!';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Password dan konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password minimal 6 karakter!';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();

            // Cek apakah email sudah terdaftar
            $checkQuery = "SELECT id FROM users WHERE email = :email";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':email', $email);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                $error_message = 'Email sudah terdaftar!';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user baru
                $query = "INSERT INTO users (nama, email, password, telepon, role, is_verified) 
                          VALUES (:nama, :email, :password, :telepon, :role, TRUE)";

                $stmt = $db->prepare($query);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':telepon', $telepon);
                $stmt->bindParam(':role', $role);

                if ($stmt->execute()) {
                    $success_message = 'Pendaftaran berhasil! Silakan login.';

                    // Reset form
                    $_POST = array();
                } else {
                    $error_message = 'Terjadi kesalahan. Silakan coba lagi.';
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - INKOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            padding: 30px;
            text-align: center;
        }

        .register-body {
            padding: 40px;
        }

        .btn-register {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            color: white;
            width: 100%;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(106, 17, 203, 0.25);
        }

        .role-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .role-option {
            flex: 1;
            text-align: center;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .role-option:hover {
            border-color: var(--primary);
        }

        .role-option.selected {
            border-color: var(--primary);
            background-color: rgba(106, 17, 203, 0.1);
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .password-strength {
            height: 5px;
            border-radius: 5px;
            margin-top: 5px;
            transition: all 0.3s;
        }

        .strength-weak {
            background-color: #dc3545;
            width: 25%;
        }

        .strength-medium {
            background-color: #ffc107;
            width: 50%;
        }

        .strength-strong {
            background-color: #28a745;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="register-card">
                    <div class="register-header">
                        <h2><i class="fas fa-home"></i> INKOS</h2>
                        <p class="mb-0">Buat akun baru</p>
                    </div>
                    <div class="register-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= $success_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                <div class="mt-2">
                                    <a href="login.php" class="btn btn-success btn-sm">Login Sekarang</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= $error_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="nama" name="nama"
                                        value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>"
                                        placeholder="Masukkan nama lengkap" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                        placeholder="Masukkan email Anda" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="telepon" class="form-label">Nomor Telepon</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="telepon" name="telepon"
                                        value="<?= isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : '' ?>"
                                        placeholder="Masukkan nomor telepon">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Saya ingin mendaftar sebagai</label>
                                <div class="role-selector">
                                    <label class="role-option <?= (isset($_POST['role']) && $_POST['role'] === 'pencari') ? 'selected' : '' ?>">
                                        <input type="radio" name="role" value="pencari" <?= (isset($_POST['role']) && $_POST['role'] === 'pencari') ? 'checked' : 'checked' ?> required>
                                        <i class="fas fa-search fa-2x mb-2"></i>
                                        <h6>Pencari Kos</h6>
                                        <small class="text-muted">Cari kos yang sesuai</small>
                                    </label>
                                    <label class="role-option <?= (isset($_POST['role']) && $_POST['role'] === 'pemilik') ? 'selected' : '' ?>">
                                        <input type="radio" name="role" value="pemilik" <?= (isset($_POST['role']) && $_POST['role'] === 'pemilik') ? 'checked' : '' ?> required>
                                        <i class="fas fa-home fa-2x mb-2"></i>
                                        <h6>Pemilik Kos</h6>
                                        <small class="text-muted">Pasang iklan kos</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Masukkan password (min. 6 karakter)" required>
                                    <button type="button" class="input-group-text" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength" id="passwordStrength"></div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                        placeholder="Konfirmasi password Anda" required>
                                    <button type="button" class="input-group-text" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="text-danger small" id="passwordMatch"></div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="agree" name="agree" required>
                                <label class="form-check-label" for="agree">
                                    Saya menyetujui <a href="#" class="text-decoration-none">Syarat dan Ketentuan</a>
                                    serta <a href="#" class="text-decoration-none">Kebijakan Privasi</a>
                                </label>
                            </div>

                            <button type="submit" name="register" class="btn btn-register mb-3">
                                <i class="fas fa-user-plus"></i> Daftar
                            </button>
                        </form>

                        <div class="text-center">
                            <p>Sudah punya akun? <a href="login.php" class="text-decoration-none">Masuk di sini</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function setupPasswordToggle(inputId, buttonId) {
            const button = document.getElementById(buttonId);
            button.addEventListener('click', function() {
                const passwordInput = document.getElementById(inputId);
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }

        setupPasswordToggle('password', 'togglePassword');
        setupPasswordToggle('confirm_password', 'toggleConfirmPassword');

        // Role selection
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.role-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const strength = checkPasswordStrength(password);

            strengthBar.className = 'password-strength';
            if (password.length > 0) {
                strengthBar.classList.add('strength-' + strength);
            }
        });

        // Password match indicator
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchIndicator = document.getElementById('passwordMatch');

            if (confirmPassword.length === 0) {
                matchIndicator.textContent = '';
            } else if (password === confirmPassword) {
                matchIndicator.textContent = '✓ Password cocok';
                matchIndicator.className = 'text-success small';
            } else {
                matchIndicator.textContent = '✗ Password tidak cocok';
                matchIndicator.className = 'text-danger small';
            }
        });

        function checkPasswordStrength(password) {
            let strength = 0;

            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            if (strength < 2) return 'weak';
            if (strength < 4) return 'medium';
            return 'strong';
        }

        // Auto-hide alert after 5 seconds
        setTimeout(function() {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.style.display = 'none';
            }
        }, 5000);
    </script>
</body>

</html>