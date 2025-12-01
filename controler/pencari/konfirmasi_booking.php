<?php
// konfirmasi_booking.php
session_start();
include '../config.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pencari') {
    header('Location: ../login.php');
    exit;
}

// Koneksi PDO
$db = new Database();
$conn = $db->getConnection();

$pemesanan_id = $_GET['id'] ?? null;
if (!$pemesanan_id) {
    header('Location: cari_kos.php');
    exit;
}

try {
    // Ambil data pemesanan
    $query = "SELECT p.*, k.nama_kos, k.alamat, k.foto_utama, d.nama as nama_daerah 
              FROM pemesanan p 
              JOIN kos k ON p.kos_id = k.id 
              JOIN daerah d ON k.daerah_id = d.id 
              WHERE p.id = :id AND p.pencari_id = :pencari_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $pemesanan_id, PDO::PARAM_INT);
    $stmt->bindParam(':pencari_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    $pemesanan = $stmt->fetch();

    if (!$pemesanan) {
        die("Pemesanan tidak ditemukan");
    }
} catch (PDOException $e) {
    die("Terjadi kesalahan: " . $e->getMessage());
}
?>