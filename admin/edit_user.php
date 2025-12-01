<?php
include '../includes/auth.php';
checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Edit User - INKOS";
include '../includes/header/header.php';

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Get user data
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: users.php');
    exit();
}

try {
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error_message'] = "User tidak ditemukan";
        header('Location: users.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: users.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $telepon = $_POST['telepon'];
    $role = $_POST['role'];
    $is_verified = isset($_POST['is_verified']) ? 1 : 0;
    $update_password = isset($_POST['update_password']) && !empty($_POST['password']);

    try {
        // Check if email already exists (excluding current user)
        $check_query = "SELECT COUNT(*) as count FROM users WHERE email = :email AND id != :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->bindParam(':id', $id);
        $check_stmt->execute();
        $email_exists = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];

        if ($email_exists > 0) {
            $error_message = "Email sudah terdaftar oleh user lain";
        } else {
            if ($update_password) {
                // Update with new password
                $password = $_POST['password'];
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $query = "UPDATE users SET nama = :nama, email = :email, password = :password, 
                         telepon = :telepon, role = :role, is_verified = :is_verified 
                         WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':password', $hashed_password);
            } else {
                // Update without password
                $query = "UPDATE users SET nama = :nama, email = :email, telepon = :telepon, 
                         role = :role, is_verified = :is_verified 
                         WHERE id = :id";
                $stmt = $db->prepare($query);
            }

            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telepon', $telepon);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':is_verified', $is_verified);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "User berhasil diperbarui";
                header('Location: users.php');
                exit();
            } else {
                $error_message = "Gagal memperbarui user";
            }
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

        <!-- Main Content -->
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3"><i class="fas fa-edit me-2"></i>Edit User</h2>
                <a href="users.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Edit Data User</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="edit_user.php?id=<?php echo $id; ?>" id="userForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" required
                                    value="<?php echo htmlspecialchars($user['nama']); ?>"
                                    placeholder="Masukkan nama lengkap">
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    value="<?php echo htmlspecialchars($user['email']); ?>"
                                    placeholder="contoh@email.com">
                            </div>

                            <div class="col-md-6">
                                <label for="telepon" class="form-label">Nomor Telepon</label>
                                <input type="tel" class="form-control" id="telepon" name="telepon"
                                    value="<?php echo htmlspecialchars($user['telepon']); ?>"
                                    placeholder="Contoh: 081234567890" pattern="[0-9]{10,15}">
                                <div class="form-text">Format: 10-15 digit angka</div>
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="pencari" <?php echo $user['role'] == 'pencari' ? 'selected' : ''; ?>>Pencari Kos</option>
                                    <option value="pemilik" <?php echo $user['role'] == 'pemilik' ? 'selected' : ''; ?>>Pemilik Kos</option>
                                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="update_password" name="update_password">
                                    <label class="form-check-label" for="update_password">
                                        Ubah Password
                                    </label>
                                    <div class="form-text">Centang jika ingin mengubah password user</div>
                                </div>
                            </div>

                            <div class="col-md-6 password-field" style="display: none;">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Kosongkan jika tidak ingin mengubah" minlength="6">
                                <div class="form-text">Password minimal 6 karakter</div>
                            </div>

                            <div class="col-md-6 password-field" style="display: none;">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                    placeholder="Ulangi password baru">
                                <div class="invalid-feedback" id="password-error">
                                    Password tidak cocok
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_verified" name="is_verified" value="1"
                                        <?php echo $user['is_verified'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_verified">
                                        User Terverifikasi
                                    </label>
                                    <div class="form-text">Centang untuk memverifikasi user</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Informasi User</h6>
                                        <p class="mb-1"><strong>ID User:</strong> <?php echo $user['id']; ?></p>
                                        <p class="mb-1"><strong>Tanggal Daftar:</strong> <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></p>
                                        <p class="mb-0"><strong>Terakhir Update:</strong> <?php echo date('d/m/Y H:i', strtotime($user['updated_at'])); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="users.php" class="btn btn-secondary me-md-2">Batal</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Perbarui User
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle password fields
    document.getElementById('update_password').addEventListener('change', function() {
        const passwordFields = document.querySelectorAll('.password-field');
        passwordFields.forEach(field => {
            field.style.display = this.checked ? 'block' : 'none';
        });

        if (!this.checked) {
            document.getElementById('password').value = '';
            document.getElementById('confirm_password').value = '';
            document.getElementById('confirm_password').classList.remove('is-invalid');
            document.getElementById('password-error').style.display = 'none';
        }
    });

    // Password confirmation validation
    document.getElementById('userForm').addEventListener('submit', function(e) {
        const updatePassword = document.getElementById('update_password').checked;

        if (updatePassword) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const passwordError = document.getElementById('password-error');

            if (password && password !== confirmPassword) {
                e.preventDefault();
                document.getElementById('confirm_password').classList.add('is-invalid');
                passwordError.style.display = 'block';
                return;
            }
        }
    });

    // Real-time password confirmation check
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        const passwordError = document.getElementById('password-error');

        if (confirmPassword && password !== confirmPassword) {
            this.classList.add('is-invalid');
            passwordError.style.display = 'block';
        } else {
            this.classList.remove('is-invalid');
            passwordError.style.display = 'none';
        }
    });
</script>

<?php include '../includes/footer/footer.php'; ?>