<?php
session_start();
include '../config.php';

// Pastikan koneksi PDO diambil dari class Database
$db = new Database();
$conn = $db->getConnection();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pencari') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pemesanan_id = $_POST['pemesanan_id'];
    $pencari_id = $_SESSION['user_id'];

    try {
        // Ambil kos_id dari pemesanan
        $query_kos = "SELECT kos_id FROM pemesanan WHERE id = ? AND pencari_id = ?";
        $stmt_kos = $conn->prepare($query_kos);
        $stmt_kos->execute([$pemesanan_id, $pencari_id]);
        $kos_data = $stmt_kos->fetch();

        if ($kos_data) {
            // Mulai transaksi PDO
            $conn->beginTransaction();

            // Update status pemesanan
            $query = "UPDATE pemesanan 
                      SET status = 'dibatalkan' 
                      WHERE id = ? AND pencari_id = ? AND status = 'menunggu'";
            $stmt = $conn->prepare($query);
            $stmt->execute([$pemesanan_id, $pencari_id]);

            // Update status kos jadi tersedia
            $query_update_kos = "UPDATE kos SET status = 'tersedia' WHERE id = ?";
            $stmt_update = $conn->prepare($query_update_kos);
            $stmt_update->execute([$kos_data['kos_id']]);

            $conn->commit();
            $_SESSION['success'] = "Pemesanan berhasil dibatalkan dan kos kembali tersedia";
        } else {
            $_SESSION['error'] = "Pemesanan tidak ditemukan";
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Gagal membatalkan pemesanan: " . $e->getMessage();
    }

    header("Location: pemesanan.php");
    exit;
}
