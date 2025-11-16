<?php
include '../controler/admin/profile.php';
include '../includes/header/admin_header.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin/profile.css">
</head>

<body>
    <div class="profile-container">
        <div class="profile-content">
            <!-- Bagian Kiri - Foto -->
            <div class="profile-left">
                <div class="profile-photo">
                    <?php
                    $target_dir = "../uploads/profiles/";
                    if (!empty($user['foto_profil']) && file_exists($target_dir . $user['foto_profil'])):
                    ?>
                        <img src="<?php echo $target_dir . htmlspecialchars($user['foto_profil']); ?>" alt="Foto Profil">
                    <?php else: ?>
                        <div style="font-size: 60px; color: #bdc3c7;">
                            <?php echo !empty($user['nama']) ? strtoupper(substr($user['nama'], 0, 1)) : 'U'; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="profile-name"><?php echo htmlspecialchars($user['nama']); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>

            <!-- Bagian Kanan - Informasi -->
            <div class="profile-right">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                            <i class="bi bi-person me-2"></i>Profil
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                            <i class="bi bi-shield-lock me-2"></i>Ubah Password
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="delete-tab" data-bs-toggle="tab" data-bs-target="#delete" type="button" role="tab">
                            <i class="bi bi-trash me-2"></i>Hapus Akun
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="profileTabsContent">
                    <!-- Tab Profil -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success mt-4"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger mt-4"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" id="profileForm">
                            <div class="info-grid">
                                <!-- Baris 1 -->
                                <div class="info-card">
                                    <div class="info-label">Nama Lengkap</div>
                                    <div class="info-value view-mode"><?php echo htmlspecialchars($user['nama']); ?></div>
                                    <div class="edit-mode" style="display: none;">
                                        <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($user['nama']); ?>">
                                    </div>
                                </div>

                                <div class="info-card">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>

                                <!-- Baris 2 -->
                                <div class="info-card">
                                    <div class="info-label">Telepon</div>
                                    <div class="info-value view-mode"><?php echo !empty($user['telepon']) ? htmlspecialchars($user['telepon']) : '-'; ?></div>
                                    <div class="edit-mode" style="display: none;">
                                        <input type="text" name="telepon" class="form-control" value="<?php echo htmlspecialchars($user['telepon']); ?>">
                                    </div>
                                </div>

                                <div class="info-card">
                                    <div class="info-label">Jenis Kelamin</div>
                                    <div class="info-value view-mode">
                                        <?php
                                        if ($user['jenis_kelamin'] == 'L') echo 'Laki-laki';
                                        elseif ($user['jenis_kelamin'] == 'P') echo 'Perempuan';
                                        else echo '-';
                                        ?>
                                    </div>
                                    <div class="edit-mode" style="display: none;">
                                        <select name="jenis_kelamin" class="form-select">
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="L" <?= $user['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="P" <?= $user['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Baris 3 -->
                                <div class="info-card">
                                    <div class="info-label">Tanggal Lahir</div>
                                    <div class="info-value view-mode"><?php echo !empty($user['tanggal_lahir']) ? date('d/m/Y', strtotime($user['tanggal_lahir'])) : '-'; ?></div>
                                    <div class="edit-mode" style="display: none;">
                                        <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo htmlspecialchars($user['tanggal_lahir']); ?>">
                                    </div>
                                </div>

                                <div class="info-card edit-mode" style="display: none;">
                                    <div class="info-label">Foto Profil</div>
                                    <input type="file" name="foto_profil" class="form-control">
                                    <small class="text-muted mt-2 d-block">Format: JPG, JPEG, PNG, GIF (Maks. 2MB)</small>
                                </div>

                                <!-- Baris 4 - Full Width -->
                                <div class="info-card full-width-field">
                                    <div class="info-label">Alamat</div>
                                    <div class="info-value view-mode"><?php echo !empty($user['alamat']) ? htmlspecialchars($user['alamat']) : '-'; ?></div>
                                    <div class="edit-mode" style="display: none;">
                                        <textarea name="alamat" class="form-control" rows="3"><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                                    </div>
                                </div>

                                <!-- Baris 5 - Full Width -->
                                <div class="info-card full-width-field">
                                    <div class="info-label">Bio</div>
                                    <div class="info-value view-mode"><?php echo !empty($user['bio']) ? htmlspecialchars($user['bio']) : '-'; ?></div>
                                    <div class="edit-mode" style="display: none;">
                                        <textarea name="bio" class="form-control" rows="4" placeholder="Ceritakan tentang diri Anda..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="action-buttons">
                                <button type="button" class="btn btn-primary view-mode" onclick="toggleEdit()">
                                    <i class="bi bi-pencil me-2"></i>Edit Profil
                                </button>
                                <div class="edit-mode" style="display: none;">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggleEdit()">
                                        <i class="bi bi-x-lg me-2"></i>Batal
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Ubah Password -->
                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <?php if (isset($success_password)): ?>
                            <div class="alert alert-success mt-4"><?php echo htmlspecialchars($success_password); ?></div>
                        <?php endif; ?>

                        <?php if (isset($error_password)): ?>
                            <div class="alert alert-danger mt-4"><?php echo htmlspecialchars($error_password); ?></div>
                        <?php endif; ?>

                        <div class="info-grid mt-4">
                            <div class="info-card full-width-field">
                                <form method="POST">
                                    <div class="mb-4">
                                        <div class="info-label">Password Lama</div>
                                        <input type="password" name="password_lama" class="form-control" required>
                                    </div>

                                    <div class="mb-4">
                                        <div class="info-label">Password Baru</div>
                                        <input type="password" name="password_baru" class="form-control" required>
                                        <small class="text-muted mt-2 d-block">Minimal 6 karakter</small>
                                    </div>

                                    <div class="mb-4">
                                        <div class="info-label">Konfirmasi Password Baru</div>
                                        <input type="password" name="konfirmasi" class="form-control" required>
                                    </div>

                                    <button type="submit" name="update_password" class="btn btn-primary">
                                        <i class="bi bi-key me-2"></i>Ubah Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Hapus Akun -->
                    <div class="tab-pane fade" id="delete" role="tabpanel">
                        <?php if (isset($error_delete)): ?>
                            <div class="alert alert-danger mt-4"><?php echo htmlspecialchars($error_delete); ?></div>
                        <?php endif; ?>

                        <div class="info-grid mt-4">
                            <div class="info-card full-width-field">
                                <div class="alert alert-warning">
                                    <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>Peringatan!</h5>
                                    <p class="mb-2">Tindakan ini akan menghapus akun Anda secara permanen. Semua data yang terkait dengan akun ini akan hilang dan tidak dapat dikembalikan.</p>
                                </div>

                                <form method="POST" class="mt-4">
                                    <div class="mb-4">
                                        <div class="info-label">Konfirmasi Penghapusan</div>
                                        <input type="text" name="konfirmasi_hapus" class="form-control" placeholder="Ketik 'HAPUS' untuk mengonfirmasi" required>
                                    </div>

                                    <button type="submit" name="hapus_akun" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus akun? Tindakan ini tidak dapat dibatalkan!')">
                                        <i class="bi bi-trash me-2"></i>Hapus Akun Saya
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src='../JavaScript/admin/profile.js'></script>
</body>

</html>