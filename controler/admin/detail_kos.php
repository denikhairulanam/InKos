<?php
checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Detail Kos - INKOS";
include '../includes/header/header.php';

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Get kos data
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: kos.php');
    exit();
}

try {
    $query = "SELECT k.*, u.nama as pemilik_nama, u.email as pemilik_email, 
                     u.telepon as pemilik_telepon, u.foto_profil as pemilik_foto,
                     d.nama as daerah_nama, d.kota, d.latitude, d.longitude
              FROM kos k 
              LEFT JOIN users u ON k.user_id = u.id 
              LEFT JOIN daerah d ON k.daerah_id = d.id 
              WHERE k.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $kos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kos) {
        $_SESSION['error_message'] = "Kos tidak ditemukan";
        header('Location: kos.php');
        exit();
    }

    // Parse JSON fields
    $fasilitas = $kos['fasilitas'] ? json_decode($kos['fasilitas'], true) : [];
    $foto_lainnya = $kos['foto_lainnya'] ? json_decode($kos['foto_lainnya'], true) : [];
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: kos.php');
    exit();
}
?>