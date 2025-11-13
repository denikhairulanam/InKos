<?php
include '../includes/auth.php';
checkAuth();

$page_title = "Profil Saya - INKOS";
include '../includes/header.php';

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Get current user data
$user_id = $_SESSION['user_id'];
try {
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error_message'] = "User tidak ditemukan";
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $telepon = $_POST['telepon'];
    $alamat = $_POST['alamat'];
    $bio = $_POST['bio'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];

    // Handle password change
    $update_password = !empty($_POST['password_baru']);

    try {
        // Check if email already exists (excluding current user)
        if ($email !== $user['email']) {
            $check_query = "SELECT COUNT(*) as count FROM users WHERE email = :email AND id != :id";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(':email', $email);
            $check_stmt->bindParam(':id', $user_id);
            $check_stmt->execute();
            $email_exists = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($email_exists > 0) {
                $error_message = "Email sudah terdaftar oleh user lain";
            }
        }

        if (!isset($error_message)) {
            // Handle file upload
            $foto_profil = $user['foto_profil']; // keep existing photo by default

            if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '/uploads/profil/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $file_extension = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array(strtolower($file_extension), $allowed_extensions)) {
                    // Delete old photo if exists
                    if ($user['foto_profil'] && file_exists($upload_dir . $user['foto_profil'])) {
                        unlink($upload_dir . $user['foto_profil']);
                    }

                    // Generate unique filename
                    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;

                    if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $upload_path)) {
                        $foto_profil = $new_filename;
                    } else {
                        $error_message = "Gagal mengupload foto profil";
                    }
                } else {
                    $error_message = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
                }
            }

            if (!isset($error_message)) {
                if ($update_password) {
                    // Verify current password
                    if (!password_verify($_POST['password_lama'], $user['password'])) {
                        $error_message = "Password lama tidak sesuai";
                    } else {
                        $password_baru = $_POST['password_baru'];
                        $konfirmasi_password = $_POST['konfirmasi_password'];

                        if ($password_baru !== $konfirmasi_password) {
                            $error_message = "Password baru dan konfirmasi password tidak cocok";
                        } else {
                            $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);

                            $query = "UPDATE users SET nama = :nama, email = :email, telepon = :telepon, 
                                     alamat = :alamat, bio = :bio, jenis_kelamin = :jenis_kelamin, 
                                     tanggal_lahir = :tanggal_lahir, foto_profil = :foto_profil, 
                                     password = :password, updated_at = NOW() 
                                     WHERE id = :id";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(':password', $hashed_password);
                        }
                    }
                } else {
                    $query = "UPDATE users SET nama = :nama, email = :email, telepon = :telepon, 
                             alamat = :alamat, bio = :bio, jenis_kelamin = :jenis_kelamin, 
                             tanggal_lahir = :tanggal_lahir, foto_profil = :foto_profil, updated_at = NOW() 
                             WHERE id = :id";
                    $stmt = $db->prepare($query);
                }

                if (!isset($error_message)) {
                    $stmt->bindParam(':nama', $nama);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':telepon', $telepon);
                    $stmt->bindParam(':alamat', $alamat);
                    $stmt->bindParam(':bio', $bio);
                    $stmt->bindParam(':jenis_kelamin', $jenis_kelamin);
                    $stmt->bindParam(':tanggal_lahir', $tanggal_lahir);
                    $stmt->bindParam(':foto_profil', $foto_profil);
                    $stmt->bindParam(':id', $user_id);

                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "Profil berhasil diperbarui";
                        // Update session data
                        $_SESSION['user_nama'] = $nama;
                        $_SESSION['user_email'] = $email;
                        if ($foto_profil) {
                            $_SESSION['user_foto'] = $foto_profil;
                        }
                        header('Location: profil.php');
                        exit();
                    } else {
                        $error_message = "Gagal memperbarui profil";
                    }
                }
            }
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3"><i class="fas fa-user me-2"></i>Profil Saya</h2>
                <div class="btn-group">
                    <a href="<?php echo getUserRole() === 'admin' ? 'index.php' : '../index.php'; ?>"
                        class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Profile Information -->
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow">
                        <div class="card-body text-center">
                            <!-- Profile Photo -->
                            <div class="position-relative d-inline-block mb-3">
                                <?php if (!empty($user['foto_profil'])): ?>
                                    <img src="../uploads/profiles/<?php echo htmlspecialchars($user['foto_profil']); ?>"
                                        class="rounded-circle" width="150" height="150" alt="Profile Photo"
                                        style="object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                        style="width: 150px; height: 150px;">
                                        <i class="fas fa-user text-white fa-4x"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="position-absolute bottom-0 end-0">
                                    <label for="foto_profil" class="btn btn-primary btn-sm rounded-circle cursor-pointer">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                </div>
                            </div>

                            <h4><?php echo htmlspecialchars($user['nama']); ?></h4>
                            <p class="text-muted mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                            <span class="badge 
                                <?php echo $user['role'] == 'admin' ? 'bg-danger' : ($user['role'] == 'pemilik' ? 'bg-success' : 'bg-primary'); ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>

                            <?php if ($user['is_verified']): ?>
                                <div class="mt-2">
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Terverifikasi
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="mt-2">
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>Belum Terverifikasi
                                    </span>
                                </div>
                            <?php endif; ?>

                            <hr>

                            <!-- Quick Stats -->
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="mb-0">
                                        <?php
                                        if ($user['role'] === 'pemilik') {
                                            $kos_count_query = "SELECT COUNT(*) as count FROM kos WHERE user_id = :user_id";
                                            $kos_count_stmt = $db->prepare($kos_count_query);
                                            $kos_count_stmt->bindParam(':user_id', $user_id);
                                            $kos_count_stmt->execute();
                                            $kos_count = $kos_count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                            echo $kos_count;
                                        } else {
                                            echo '0';
                                        }
                                        ?>
                                    </h5>
                                    <small class="text-muted">Kos</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="mb-0">
                                        <?php
                                        $laporan_count_query = "SELECT COUNT(*) as count FROM laporan WHERE user_id = :user_id";
                                        $laporan_count_stmt = $db->prepare($laporan_count_query);
                                        $laporan_count_stmt->bindParam(':user_id', $user_id);
                                        $laporan_count_stmt->execute();
                                        $laporan_count = $laporan_count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                        echo $laporan_count;
                                        ?>
                                    </h5>
                                    <small class="text-muted">Laporan</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="card border-0 shadow mt-4">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Informasi Akun</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-12">
                                    <small class="text-muted">ID User</small>
                                    <p class="mb-2"><code>#<?php echo $user['id']; ?></code></p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Bergabung</small>
                                    <p class="mb-2"><?php echo date('d F Y', strtotime($user['created_at'])); ?></p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Terakhir Update</small>
                                    <p class="mb-0"><?php echo date('d F Y H:i', strtotime($user['updated_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Edit Profil</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="profil.php" enctype="multipart/form-data" id="profileForm">
                                <input type="file" id="foto_profil" name="foto_profil" accept="image/*" class="d-none"
                                    onchange="previewImage(this)">

                                <div class="row g-3">
                                    <!-- Basic Information -->
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2">Informasi Dasar</h6>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama" name="nama" required
                                            value="<?php echo htmlspecialchars($user['nama'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required
                                            value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="telepon" class="form-label">Nomor Telepon</label>
                                        <input type="tel" class="form-control" id="telepon" name="telepon"
                                            value="<?php echo htmlspecialchars($user['telepon'] ?? ''); ?>"
                                            placeholder="Contoh: 081234567890">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="L" <?php echo ($user['jenis_kelamin'] ?? '') == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="P" <?php echo ($user['jenis_kelamin'] ?? '') == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                                            value="<?php echo $user['tanggal_lahir'] ?? ''; ?>">
                                    </div>

                                    <div class="col-12">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="3"
                                            placeholder="Alamat lengkap..."><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label for="bio" class="form-label">Bio</label>
                                        <textarea class="form-control" id="bio" name="bio" rows="3"
                                            placeholder="Ceritakan tentang diri Anda..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                        <div class="form-text">Maksimal 500 karakter</div>
                                    </div>

                                    <!-- Password Change -->
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mt-4">Ubah Password</h6>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="ubah_password"
                                                onchange="togglePasswordFields()">
                                            <label class="form-check-label" for="ubah_password">
                                                Ubah Password
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-4 password-field" style="display: none;">
                                        <label for="password_lama" class="form-label">Password Lama</label>
                                        <input type="password" class="form-control" id="password_lama" name="password_lama">
                                    </div>

                                    <div class="col-md-4 password-field" style="display: none;">
                                        <label for="password_baru" class="form-label">Password Baru</label>
                                        <input type="password" class="form-control" id="password_baru" name="password_baru">
                                    </div>

                                    <div class="col-md-4 password-field" style="display: none;">
                                        <label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
                                        <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password">
                                        <div class="invalid-feedback" id="password-error">
                                            Password tidak cocok
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="card border-0 shadow mt-4 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0">Zona Berbahaya</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Tindakan ini tidak dapat dibatalkan. Harap berhati-hati.
                            </p>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteAccountModal">
                                    <i class="fas fa-trash me-2"></i>Hapus Akun
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Hapus Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus akun Anda?</p>
                <p class="text-danger">
                    <small>
                        <strong>Peringatan:</strong> Semua data termasuk kos, laporan, dan informasi lainnya akan dihapus secara permanen.
                    </small>
                </p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmDelete">
                    <label class="form-check-label" for="confirmDelete">
                        Saya mengerti dan ingin menghapus akun saya
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="deleteAccountBtn" disabled>
                    Hapus Akun
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle password fields
    function togglePasswordFields() {
        const passwordFields = document.querySelectorAll('.password-field');
        const ubahPassword = document.getElementById('ubah_password').checked;

        passwordFields.forEach(field => {
            field.style.display = ubahPassword ? 'block' : 'none';
        });

        if (!ubahPassword) {
            document.getElementById('password_lama').value = '';
            document.getElementById('password_baru').value = '';
            document.getElementById('konfirmasi_password').value = '';
            document.getElementById('konfirmasi_password').classList.remove('is-invalid');
            document.getElementById('password-error').style.display = 'none';
        }
    }

    // Password confirmation validation
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const ubahPassword = document.getElementById('ubah_password').checked;

        if (ubahPassword) {
            const passwordBaru = document.getElementById('password_baru').value;
            const konfirmasiPassword = document.getElementById('konfirmasi_password').value;
            const passwordError = document.getElementById('password-error');

            if (passwordBaru && passwordBaru !== konfirmasiPassword) {
                e.preventDefault();
                document.getElementById('konfirmasi_password').classList.add('is-invalid');
                passwordError.style.display = 'block';
                return;
            }
        }
    });

    // Real-time password confirmation check
    document.getElementById('konfirmasi_password').addEventListener('input', function() {
        const passwordBaru = document.getElementById('password_baru').value;
        const konfirmasiPassword = this.value;
        const passwordError = document.getElementById('password-error');

        if (konfirmasiPassword && passwordBaru !== konfirmasiPassword) {
            this.classList.add('is-invalid');
            passwordError.style.display = 'block';
        } else {
            this.classList.remove('is-invalid');
            passwordError.style.display = 'none';
        }
    });

    // Image preview
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Create a new image element or update existing one
                const existingImg = document.querySelector('.rounded-circle[width="150"]');
                if (existingImg) {
                    existingImg.src = e.target.result;
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Delete account confirmation
    document.getElementById('confirmDelete').addEventListener('change', function() {
        document.getElementById('deleteAccountBtn').disabled = !this.checked;
    });

    // Bio character counter
    document.getElementById('bio').addEventListener('input', function() {
        const maxLength = 500;
        const currentLength = this.value.length;
        const counter = document.getElementById('bio-counter') || (function() {
            const counter = document.createElement('div');
            counter.id = 'bio-counter';
            counter.className = 'form-text';
            this.parentNode.appendChild(counter);
            return counter;
        }).call(this);

        counter.textContent = `${currentLength}/${maxLength} karakter`;

        if (currentLength > maxLength) {
            this.classList.add('is-invalid');
            counter.classList.add('text-danger');
        } else {
            this.classList.remove('is-invalid');
            counter.classList.remove('text-danger');
        }
    });

    // Initialize bio counter
    document.addEventListener('DOMContentLoaded', function() {
        const bio = document.getElementById('bio');
        if (bio.value) {
            bio.dispatchEvent(new Event('input'));
        }
    });
</script>

<?php include '../includes/footer.php'; ?>