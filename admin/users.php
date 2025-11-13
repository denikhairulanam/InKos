<?php
include '../includes/auth.php';
checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Manajemen User - INKOS";
include '../includes/admin_header.php';

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Handle delete
if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    try {
        // Check if user has related data in kos table
        $check_query = "SELECT COUNT(*) as count FROM kos WHERE user_id = :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $id);
        $check_stmt->execute();
        $kos_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];

        if ($kos_count > 0) {
            $_SESSION['error_message'] = "Tidak dapat menghapus user karena memiliki data kos terkait";
        } else {
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "User berhasil dihapus";
            } else {
                $_SESSION['error_message'] = "Gagal menghapus user";
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    header('Location: users.php');
    exit();
}

// Handle verification
if (isset($_POST['verify_id'])) {
    $id = $_POST['verify_id'];
    try {
        $query = "UPDATE users SET is_verified = 1 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "User berhasil diverifikasi";
        } else {
            $_SESSION['error_message'] = "Gagal memverifikasi user";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    header('Location: users.php');
    exit();
}

// Get all users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search and filter functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$verification_filter = isset($_GET['verification']) ? $_GET['verification'] : '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(nama LIKE :search OR email LIKE :search OR telepon LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($role_filter)) {
    $where[] = "role = :role";
    $params[':role'] = $role_filter;
}

if ($verification_filter === 'verified') {
    $where[] = "is_verified = 1";
} elseif ($verification_filter === 'unverified') {
    $where[] = "is_verified = 0";
}

$where_clause = '';
if (!empty($where)) {
    $where_clause = "WHERE " . implode(' AND ', $where);
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM users $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_count / $limit);

// Get data
$query = "SELECT *, 
          (SELECT COUNT(*) FROM kos WHERE user_id = users.id) as kos_count 
          FROM users 
          $where_clause 
          ORDER BY created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if (!$user['is_verified']): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="verify_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-success" title="Verifikasi"
                                                        onclick="return confirm('Verifikasi user <?php echo htmlspecialchars($user['nama']); ?>?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($user['role'] !== 'admin'): ?>
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal<?php echo $user['id']; ?>" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
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

<?php include '../includes/footer.php'; ?>