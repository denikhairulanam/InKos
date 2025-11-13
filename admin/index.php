<?php
include '../includes/auth.php';
include '../includes/admin_header.php';

checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Dashboard Admin - INKOS";

// Include database connection
include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];
$queries = [
    'total_users' => "SELECT COUNT(*) as count FROM users",
    'total_pemilik' => "SELECT COUNT(*) as count FROM users WHERE role = 'pemilik'",
    'total_pencari' => "SELECT COUNT(*) as count FROM users WHERE role = 'pencari'",
    'verified_users' => "SELECT COUNT(*) as count FROM users WHERE is_verified = TRUE",
    'total_kos' => "SELECT COUNT(*) as count FROM kos",
    'kos_tersedia' => "SELECT COUNT(*) as count FROM kos WHERE status = 'tersedia'",
    'total_banners' => "SELECT COUNT(*) as count FROM home_banners WHERE status = 'aktif'",
    'total_daerah' => "SELECT COUNT(*) as count FROM daerah"
];

foreach ($queries as $key => $query) {
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats[$key] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    } catch (PDOException $e) {
        $stats[$key] = 0;
        error_log("Error fetching stat {$key}: " . $e->getMessage());
    }
}

// Get recent activities
$recent_activities = [];
try {
    // Recent user registrations
    $query_users = "SELECT nama, role, created_at, is_verified FROM users ORDER BY created_at DESC LIMIT 5";
    $stmt_users = $db->prepare($query_users);
    $stmt_users->execute();
    $recent_users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

    foreach ($recent_users as $user) {
        $status = $user['is_verified'] ? 'Success' : 'Pending';
        $badge_class = $user['is_verified'] ? 'bg-success' : 'bg-warning';
        $activity = "Registrasi akun " . ($user['role'] == 'pemilik' ? 'pemilik kos' : 'pencari kos');

        $recent_activities[] = [
            'user' => $user['nama'],
            'activity' => $activity,
            'time' => $user['created_at'],
            'status' => $status,
            'badge_class' => $badge_class
        ];
    }

    // Recent kos entries
    $query_kos = "SELECT k.nama_kos, u.nama as pemilik, k.created_at, k.status 
                  FROM kos k 
                  LEFT JOIN users u ON k.user_id = u.id 
                  ORDER BY k.created_at DESC LIMIT 3";
    $stmt_kos = $db->prepare($query_kos);
    $stmt_kos->execute();
    $recent_kos = $stmt_kos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($recent_kos as $kos) {
        $status = $kos['status'] == 'tersedia' ? 'Completed' : 'Unavailable';
        $badge_class = $kos['status'] == 'tersedia' ? 'bg-success' : 'bg-secondary';

        $recent_activities[] = [
            'user' => $kos['pemilik'],
            'activity' => "Menambahkan kos: " . $kos['nama_kos'],
            'time' => $kos['created_at'],
            'status' => $status,
            'badge_class' => $badge_class
        ];
    }

    // Sort activities by time (newest first)
    usort($recent_activities, function ($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    // Take only 5 most recent activities
    $recent_activities = array_slice($recent_activities, 0, 5);
} catch (PDOException $e) {
    error_log("Error fetching recent activities: " . $e->getMessage());
    // Fallback activities if database query fails
    $recent_activities = [
        [
            'user' => 'Admin',
            'activity' => 'Login ke sistem',
            'time' => date('Y-m-d H:i:s'),
            'status' => 'Success',
            'badge_class' => 'bg-success'
        ]
    ];
}
?>

<!-- Main Content -->
<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark">Dashboard Admin</h1>
            <p class="text-muted mb-0">Selamat datang di panel administrasi INKOS</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Users -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-dark mb-1"><?php echo number_format($stats['total_users']); ?></h4>
                            <p class="card-text text-muted mb-0">Total User</p>
                            <small class="text-muted">
                                <?php echo number_format($stats['total_pemilik']); ?> pemilik,
                                <?php echo number_format($stats['total_pencari']); ?> pencari
                            </small>
                        </div>
                        <div class="bg-primary rounded-circle p-3">
                            <i class="fas fa-users fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kos Statistics -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-dark mb-1"><?php echo number_format($stats['total_kos']); ?></h4>
                            <p class="card-text text-muted mb-0">Total Kos</p>
                            <small class="text-muted">
                                <?php echo number_format($stats['kos_tersedia']); ?> tersedia
                            </small>
                        </div>
                        <div class="bg-success rounded-circle p-3">
                            <i class="fas fa-building fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verified Users -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-dark mb-1"><?php echo number_format($stats['verified_users']); ?></h4>
                            <p class="card-text text-muted mb-0">User Terverifikasi</p>
                            <small class="text-muted">
                                <?php
                                $verification_rate = $stats['total_users'] > 0 ?
                                    round(($stats['verified_users'] / $stats['total_users']) * 100, 1) : 0;
                                echo $verification_rate . '% terverifikasi';
                                ?>
                            </small>
                        </div>
                        <div class="bg-info rounded-circle p-3">
                            <i class="fas fa-check-circle fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded p-3 me-3">
                            <i class="fas fa-user-tie fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5 class="card-title text-dark mb-1"><?php echo number_format($stats['total_pemilik']); ?></h5>
                            <p class="card-text text-muted mb-0">Pemilik Kos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded p-3 me-3">
                            <i class="fas fa-user fa-2x text-success"></i>
                        </div>
                        <div>
                            <h5 class="card-title text-dark mb-1"><?php echo number_format($stats['total_pencari']); ?></h5>
                            <p class="card-text text-muted mb-0">Pencari Kos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded p-3 me-3">
                            <i class="fas fa-map-marker-alt fa-2x text-info"></i>
                        </div>
                        <div>
                            <h5 class="card-title text-dark mb-1"><?php echo number_format($stats['total_daerah']); ?></h5>
                            <p class="card-text text-muted mb-0">Daerah Tersedia</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-dark">
                            <i class="fas fa-history me-2 text-primary"></i>
                            Aktivitas Terbaru
                        </h5>
                        <a href="laporan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_activities)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Tidak ada aktivitas terbaru</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Aktivitas</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle p-2 me-2">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($activity['user']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                                            <td>
                                                <?php
                                                $time = strtotime($activity['time']);
                                                echo date('H:i', $time) . ' - ' . date('d/m/Y', $time);
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $activity['badge_class']; ?>">
                                                    <?php echo $activity['status']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Add some interactivity
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to cards
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.transition = 'transform 0.2s ease';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>