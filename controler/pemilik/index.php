<?php
include '../includes/auth.php';
include '../config.php'; // Include config FIRST for Database class
checkAuth();

if (getUserRole() !== 'pemilik') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Ambil data statistik
$total_kos = 0;
$kamar_terisi = 0;
$kamar_kosong = 0;
$total_pendapatan = 0;
$pemesanan_terbaru = [];
$notifikasi = [];
$notifikasi_count = 0;

try {
    // Total kos
    $query = "SELECT COUNT(*) as total FROM kos WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_kos = $result['total'];

    // Kamar terisi dan kosong
    $query = "SELECT 
                SUM(CASE WHEN status = 'tersedia' THEN 1 ELSE 0 END) as kosong,
                SUM(CASE WHEN status = 'tidak_tersedia' THEN 1 ELSE 0 END) as terisi
              FROM kos 
              WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $kamar_kosong = $result['kosong'] ?? 0;
    $kamar_terisi = $result['terisi'] ?? 0;

    // Total pendapatan
    $query = "SELECT COALESCE(SUM(harga_bulanan), 0) as total_pendapatan 
              FROM kos 
              WHERE user_id = :user_id AND status = 'tidak_tersedia'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_pendapatan = $result['total_pendapatan'] ?? 0;

    // Pemesanan terbaru - SESUAIKAN DENGAN STRUKTUR DATABASE BARU
    try {
        $checkTable = $db->query("SHOW TABLES LIKE 'pemesanan'")->fetch();
        if ($checkTable) {
            $query = "SELECT 
                        p.*, 
                        k.nama_kos, 
                        u.nama as nama_pemesan,
                        k.foto_utama,
                        k.alamat
                      FROM pemesanan p 
                      JOIN kos k ON p.kos_id = k.id 
                      JOIN users u ON p.pencari_id = u.id 
                      WHERE p.pemilik_id = :user_id 
                      ORDER BY p.tanggal_pemesanan DESC 
                      LIMIT 5";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $pemesanan_terbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $pemesanan_terbaru = [];
    }
} catch (PDOException $e) {
    $error = "Terjadi kesalahan saat mengambil data: " . $e->getMessage();
}

$page_title = "Dashboard Pemilik Kos - INKOS";

// Include header AFTER all variables are set
include '../includes/header/pemilik_header.php';

?>