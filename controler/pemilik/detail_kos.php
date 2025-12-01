<?php
include '../includes/auth.php';

checkAuth();

if (getUserRole() !== 'pemilik') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Detail Kos - INKOS";
include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Get kos data
$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$id) {
    header('Location: kos.php');
    exit();
}

try {
    // Query untuk mendapatkan data kos milik pemilik yang login
    $query = "SELECT k.*, u.nama as pemilik_nama, u.email as pemilik_email, 
                     u.telepon as pemilik_telepon, u.foto_profil as pemilik_foto,
                     d.nama as daerah_nama, d.kota, d.latitude, d.longitude,
                     (SELECT COUNT(*) FROM pemesanan p WHERE p.kos_id = k.id AND p.status = 'dikonfirmasi') as jumlah_pemesanan_aktif,
                     (SELECT COUNT(*) FROM pemesanan p WHERE p.kos_id = k.id) as total_pemesanan
              FROM kos k 
              LEFT JOIN users u ON k.user_id = u.id 
              LEFT JOIN daerah d ON k.daerah_id = d.id 
              WHERE k.id = :id AND k.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $kos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kos) {
        $_SESSION['error_message'] = "Kos tidak ditemukan atau Anda tidak memiliki akses";
        header('Location: kos.php');
        exit();
    }

    // Parse JSON fields
    $fasilitas = $kos['fasilitas'] ? json_decode($kos['fasilitas'], true) : [];
    $foto_lainnya = $kos['foto_lainnya'] ? json_decode($kos['foto_lainnya'], true) : [];

    // Get statistik pemesanan
    $query_stats = "SELECT 
        COUNT(*) as total_pemesanan,
        SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
        SUM(CASE WHEN status = 'dikonfirmasi' THEN 1 ELSE 0 END) as dikonfirmasi,
        SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
        SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak
        FROM pemesanan WHERE kos_id = :kos_id";
    $stmt_stats = $db->prepare($query_stats);
    $stmt_stats->bindParam(':kos_id', $id);
    $stmt_stats->execute();
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: kos.php');
    exit();
}
include '../includes/header/header.php';
?>