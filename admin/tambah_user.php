<?php
include '../includes/auth.php';
checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Tambah User - INKOS";
include '../includes/admin_header.php';

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $telepon = $_POST['telepon'];
    $role = $_POST['role'];
    $is_verified = isset($_POST['is_verified']) ? 1 : 0;

    try {
        // Check if email already exists
        $check_query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        $email_exists = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];

        if ($email_exists > 0) {
            $error_message = "Email sudah terdaftar";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (nama, email, password, telepon, role, is_verified) 
                      VALUES (:nama, :email, :password, :telepon, :role, :is_verified)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':telepon', $telepon);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':is_verified', $is_verified);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "User berhasil ditambahkan";
                header('Location: users.php');
                exit();
            } else {
                $error_message = "Gagal menambahkan user";
            }
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!-- Main Content -->
<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark">Tambah User Baru</h1>
            <p class="text-muted mb-0">Tambahkan user baru ke sistem INKOS</p>
        </div>
        <a href="users.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-dark">
                <i class="fas fa-user-plus me-2 text-primary"></i>
                Form Tambah User
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="tambah_user.php" id="userForm">
                <div class="row g-3">
                    <!-- Nama Lengkap -->
                    <div class="col-md-6">
                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required
                            placeholder="Masukkan nama lengkap" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required
                            placeholder="contoh@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <!-- Password -->
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required
                            placeholder="Minimal 6 karakter" minlength="6">
                        <div class="form-text">Password minimal 6 karakter</div>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                            placeholder="Ulangi password">
                        <div class="invalid-feedback" id="password-error">
                            Password tidak cocok
                        </div>
                    </div>

                    <!-- Telepon -->
                    <div class="col-md-6">
                        <label for="telepon" class="form-label">Nomor Telepon</label>
                        <input type="tel" class="form-control" id="telepon" name="telepon"
                            placeholder="Contoh: 081234567890" pattern="[0-9]{10,15}"
                            value="<?php echo isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : ''; ?>">
                        <div class="form-text">Format: 10-15 digit angka</div>
                    </div>

                    <!-- Role -->
                    <div class="col-md-6">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="pencari" <?php echo (isset($_POST['role']) && $_POST['role'] == 'pencari') ? 'selected' : ''; ?>>Pencari Kos</option>
                            <option value="pemilik" <?php echo (isset($_POST['role']) && $_POST['role'] == 'pemilik') ? 'selected' : ''; ?>>Pemilik Kos</option>
                            <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <!-- Verifikasi -->
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_verified" name="is_verified" value="1"
                                <?php echo (isset($_POST['is_verified']) && $_POST['is_verified'] == '1') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_verified">
                                Verifikasi user langsung
                            </label>
                            <div class="form-text">Centang untuk langsung memverifikasi user tanpa perlu verifikasi manual</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-12">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-4">
                            <a href="users.php" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan User
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Password confirmation validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('userForm');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordError = document.getElementById('password-error');

        // Real-time password confirmation check
        confirmPasswordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmPassword = this.value;

            if (confirmPassword && password !== confirmPassword) {
                this.classList.add('is-invalid');
                passwordError.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                passwordError.style.display = 'none';
            }
        });

        // Form submission validation
        form.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (password !== confirmPassword) {
                e.preventDefault();
                confirmPasswordInput.classList.add('is-invalid');
                passwordError.style.display = 'block';

                // Scroll to error
                confirmPasswordInput.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });

        // Clear error when password changes
        passwordInput.addEventListener('input', function() {
            const confirmPassword = confirmPasswordInput.value;
            if (confirmPassword) {
                if (this.value !== confirmPassword) {
                    confirmPasswordInput.classList.add('is-invalid');
                    passwordError.style.display = 'block';
                } else {
                    confirmPasswordInput.classList.remove('is-invalid');
                    passwordError.style.display = 'none';
                }
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>