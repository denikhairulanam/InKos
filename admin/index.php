<?php
include '../includes/auth.php';
include '../includes/header/admin_header.php';
include '../controler/admin/index.php';
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

<script src='../JavaScript/admin/index.js'></script>

<?php include '../includes/footer/footer.php'; ?>