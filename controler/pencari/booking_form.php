<?php
// booking_form.php
session_start();
require_once '../config.php';

// Init database connection
$db = new Database();
$conn = $db->getConnection();

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pencari') {
    header('Location: ../login.php');
    exit;
}

// Ambil data kos
$kos_id = $_GET['kos_id'] ?? null;
if (!$kos_id) {
    header('Location: cari_kos.php');
    exit;
}

// Query data kos
$query = "SELECT k.*, u.nama as nama_pemilik, u.id as pemilik_id, d.nama as nama_daerah 
          FROM kos k 
          JOIN users u ON k.user_id = u.id
          JOIN daerah d ON k.daerah_id = d.id 
          WHERE k.id = ? AND k.status = 'tersedia'";

$stmt = $conn->prepare($query);
$stmt->execute([$kos_id]);
$kos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kos) {
    die("Kos tidak ditemukan atau tidak tersedia");
}

// Proses form booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $durasi_bulan = $_POST['durasi_bulan'];
    $catatan_tambahan = $_POST['catatan_tambahan'] ?? '';

    // Validasi input
    if (empty($tanggal_mulai) || empty($durasi_bulan)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Hitung
        $tanggal_selesai = date('Y-m-d', strtotime($tanggal_mulai . " + $durasi_bulan months"));
        $total_harga = $kos['harga_bulanan'] * $durasi_bulan;

        try {
            // Begin transaction
            $conn->beginTransaction();

            // Insert pemesanan
            $query = "INSERT INTO pemesanan 
                        (kos_id, pencari_id, pemilik_id, tanggal_mulai, tanggal_selesai,
                         durasi_bulan, total_harga, status, catatan)
                      VALUES (?, ?, ?, ?, ?, ?, ?, 'menunggu', ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $kos_id,
                $_SESSION['user_id'],
                $kos['pemilik_id'],
                $tanggal_mulai,
                $tanggal_selesai,
                $durasi_bulan,
                $total_harga,
                $catatan_tambahan
            ]);

            $pemesanan_id = $conn->lastInsertId();

            // Update status kos → dipesan
            $stmtUpdate = $conn->prepare("UPDATE kos SET status = 'dipesan' WHERE id = ?");
            $stmtUpdate->execute([$kos_id]);

            // Commit
            $conn->commit();

            // Redirect
            header("Location: konfirmasi_booking.php?id=$pemesanan_id");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Gagal melakukan pemesanan: " . $e->getMessage();
        }
    }
}
