<?php
session_start();

// Panggil koneksi PDO
require_once '../config.php';
$database = new Database();
$conn = $database->getConnection();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pencari') {
    header('Location: ../../login.php');
    exit;
}

$pencari_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'all';

// Query dasar - Diperbaiki untuk handle error kolom yang tidak ada
$query = "SELECT p.*, k.nama_kos, k.alamat, k.foto_utama, k.deskripsi, k.fasilitas,
                 d.nama as nama_daerah,
                 u.nama as nama_pemilik, u.telepon as telepon_pemilik, u.email as email_pemilik,
                 pb.id as pembayaran_id, pb.status_pembayaran, pb.bukti_bayar,
                 pb.tanggal_bayar, pb.metode_pembayaran";

// Cek apakah kolom alasan_penolakan ada di tabel pembayaran
try {
    $check_column = $conn->prepare("SHOW COLUMNS FROM pembayaran LIKE 'alasan_penolakan'");
    $check_column->execute();
    $column_exists = $check_column->fetch();

    if ($column_exists) {
        $query .= ", pb.alasan_penolakan";
    }
} catch (Exception $e) {
    // Jika error, lanjutkan tanpa kolom alasan_penolakan
}

$query .= ", k.status as status_kos
          FROM pemesanan p 
          JOIN kos k ON p.kos_id = k.id 
          JOIN daerah d ON k.daerah_id = d.id
          JOIN users u ON p.pemilik_id = u.id
          LEFT JOIN pembayaran pb ON p.id = pb.pemesanan_id
          WHERE p.pencari_id = :id";

if ($status_filter !== 'all') {
    $query .= " AND p.status = :status";
}

$query .= " ORDER BY p.tanggal_pemesanan DESC";

$stmt = $conn->prepare($query);

if ($status_filter !== 'all') {
    $stmt->bindParam(':id', $pencari_id);
    $stmt->bindParam(':status', $status_filter);
} else {
    $stmt->bindParam(':id', $pencari_id);
}

$stmt->execute();
$pemesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistik
$query_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
    SUM(CASE WHEN status = 'dikonfirmasi' THEN 1 ELSE 0 END) as dikonfirmasi,
    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
    SUM(CASE WHEN status = 'dibatalkan' THEN 1 ELSE 0 END) as dibatalkan
    FROM pemesanan WHERE pencari_id = :id";

$stmt_stats = $conn->prepare($query_stats);
$stmt_stats->bindParam(':id', $pencari_id);
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
