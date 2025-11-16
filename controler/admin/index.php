<?php

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
