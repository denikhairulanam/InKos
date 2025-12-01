<?php
include '../controler/admin/user.php';
?>

<!-- Main Content -->
<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark">Manajemen User</h1>
            <p class="text-muted mb-0">Kelola data pengguna sistem INKOS</p>
        </div>
        <a href="tambah_user.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah User
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="users.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Cari User</label>
                        <input type="text" class="form-control" name="search"
                            placeholder="Nama, email, atau telepon..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role">
                            <option value="">Semua Role</option>
                            <option value="pemilik" <?php echo $role_filter == 'pemilik' ? 'selected' : ''; ?>>Pemilik</option>
                            <option value="pencari" <?php echo $role_filter == 'pencari' ? 'selected' : ''; ?>>Pencari</option>
                            <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Verifikasi</label>
                        <select class="form-select" name="verification">
                            <option value="">Semua Status</option>
                            <option value="verified" <?php echo $verification_filter == 'verified' ? 'selected' : ''; ?>>Terverifikasi</option>
                            <option value="unverified" <?php echo $verification_filter == 'unverified' ? 'selected' : ''; ?>>Belum Terverifikasi</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-grid w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
                <?php if (!empty($search) || !empty($role_filter) || !empty($verification_filter)): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <a href="users.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-refresh me-2"></i>Reset Filter
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Daftar User</h5>
            <span class="badge bg-primary"><?php echo $total_count; ?> User</span>
        </div>
        <div class="card-body">
            <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada data user</h5>
                    <p class="text-muted">Silakan tambahkan user baru untuk memulai.</p>
                    <a href="tambah_user.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah User Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Kontak</th>
                                <th>Role</th>
                                <th>Kos</th>
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $index => $user): ?>
                                <tr>
                                    <td class="text-muted"><?php echo $offset + $index + 1; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($user['foto_profil']): ?>
                                                <img src="../uploads/profiles/<?php echo htmlspecialchars($user['foto_profil']); ?>"
                                                    class="rounded-circle me-2" width="32" height="32" alt="Profile">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['nama']); ?></strong>
                                                <br>
                                                <small class="text-muted">ID: <?php echo $user['id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                            <br>
                                            <small><?php echo $user['telepon'] ? htmlspecialchars($user['telepon']) : '<span class="text-muted">-</span>'; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?php echo $user['role'] == 'admin' ? 'bg-danger' : ($user['role'] == 'pemilik' ? 'bg-success' : 'bg-primary'); ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['role'] == 'pemilik'): ?>
                                            <span class="badge bg-info"><?php echo $user['kos_count']; ?> Kos</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['is_verified']): ?>
                                            <span class="badge bg-success">Terverifikasi</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Belum Verifikasi</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
                                        <br>
                                        <small class="text-muted"><?php echo date('H:i', strtotime($user['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Icon Detail -->
                                            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal"
                                                data-bs-target="#detailModal<?php echo $user['id']; ?>" title="Detail User">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <!-- Icon Edit -->
                                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <!-- Icon Verifikasi -->
                                            <?php if (!$user['is_verified']): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="verify_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-success" title="Verifikasi"
                                                        onclick="return confirm('Verifikasi user <?php echo htmlspecialchars($user['nama']); ?>?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Icon Hapus -->
                                            <?php if ($user['role'] !== 'admin'): ?>
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal<?php echo $user['id']; ?>" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Detail Modal -->
                                        <div class="modal fade" id="detailModal<?php echo $user['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detail User</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-4 text-center">
                                                                <?php if ($user['foto_profil']): ?>
                                                                    <img src="../uploads/profiles/<?php echo htmlspecialchars($user['foto_profil']); ?>"
                                                                        class="rounded-circle mb-3" width="120" height="120" alt="Profile">
                                                                <?php else: ?>
                                                                    <div class="bg-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                                                        style="width: 120px; height: 120px;">
                                                                        <i class="fas fa-user text-white fa-3x"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <h5><?php echo htmlspecialchars($user['nama']); ?></h5>
                                                                <span class="badge 
                                                                    <?php echo $user['role'] == 'admin' ? 'bg-danger' : ($user['role'] == 'pemilik' ? 'bg-success' : 'bg-primary'); ?>">
                                                                    <?php echo ucfirst($user['role']); ?>
                                                                </span>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <div class="row">
                                                                    <div class="col-6 mb-3">
                                                                        <label class="form-label text-muted small mb-1">Email</label>
                                                                        <p class="mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                                                                    </div>
                                                                    <div class="col-6 mb-3">
                                                                        <label class="form-label text-muted small mb-1">Telepon</label>
                                                                        <p class="mb-0"><?php echo $user['telepon'] ? htmlspecialchars($user['telepon']) : '<span class="text-muted">-</span>'; ?></p>
                                                                    </div>
                                                                    <div class="col-6 mb-3">
                                                                        <label class="form-label text-muted small mb-1">Status Verifikasi</label>
                                                                        <p class="mb-0">
                                                                            <?php if ($user['is_verified']): ?>
                                                                                <span class="badge bg-success">Terverifikasi</span>
                                                                            <?php else: ?>
                                                                                <span class="badge bg-warning">Belum Verifikasi</span>
                                                                            <?php endif; ?>
                                                                        </p>
                                                                    </div>
                                                                    
                                                                    <div class="col-6 mb-3">
                                                                        <label class="form-label text-muted small mb-1">Tanggal Daftar</label>
                                                                        <p class="mb-0"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></p>
                                                                    </div>
                                                                    <div class="col-6 mb-3">
                                                                        <label class="form-label text-muted small mb-1">Terakhir Update</label>
                                                                        <p class="mb-0"><?php echo date('d/m/Y H:i', strtotime($user['updated_at'])); ?></p>
                                                                    </div>
                                                                    <?php if ($user['role'] == 'pemilik' && $user['kos_count'] > 0): ?>
                                                                        <div class="col-12 mb-3">
                                                                            <label class="form-label text-muted small mb-1">Informasi Kos</label>
                                                                            <p class="mb-0">
                                                                                <small>User ini memiliki <?php echo $user['kos_count']; ?> data kos yang terdaftar dalam sistem.</small>
                                                                            </p>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                                                            <i class="fas fa-edit me-2"></i>Edit User
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Modal -->
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <div class="modal fade" id="deleteModal<?php echo $user['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Apakah Anda yakin ingin menghapus user <strong><?php echo htmlspecialchars($user['nama']); ?></strong>?</p>
                                                            <?php if ($user['kos_count'] > 0): ?>
                                                                <div class="alert alert-warning">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                                    User ini memiliki <?php echo $user['kos_count']; ?> data kos yang juga akan terhapus.
                                                                </div>
                                                            <?php endif; ?>
                                                            <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="delete_id" value="<?php echo $user['id']; ?>">
                                                                <button type="submit" class="btn btn-danger">Hapus</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&verification=<?php echo urlencode($verification_filter); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&verification=<?php echo urlencode($verification_filter); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&verification=<?php echo urlencode($verification_filter); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer/footer.php'; ?>