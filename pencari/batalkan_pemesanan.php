<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pencari') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pemesanan_id = $_POST['pemesanan_id'];
    $pencari_id = $_SESSION['user_id'];

    // Ambil kos_id dari pemesanan
    $query_kos = "SELECT kos_id FROM pemesanan WHERE id = ? AND pencari_id = ?";
    $stmt_kos = $conn->prepare($query_kos);
    $stmt_kos->bind_param("ii", $pemesanan_id, $pencari_id);
    $stmt_kos->execute();
    $result_kos = $stmt_kos->get_result();
    $kos_data = $result_kos->fetch_assoc();

    if ($kos_data) {
        $conn->begin_transaction();

        try {
            // Update status pemesanan menjadi dibatalkan
            $query = "UPDATE pemesanan SET status = 'dibatalkan' 
                      WHERE id = ? AND pencari_id = ? AND status = 'menunggu'";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $pemesanan_id, $pencari_id);
            $stmt->execute();

            // Kembalikan status kos menjadi 'tersedia'
            $query_update_kos = "UPDATE kos SET status = 'tersedia' WHERE id = ?";
            $stmt_update_kos = $conn->prepare($query_update_kos);
            $stmt_update_kos->bind_param("i", $kos_data['kos_id']);
            $stmt_update_kos->execute();

            $conn->commit();
            $_SESSION['success'] = "Pemesanan berhasil dibatalkan dan kos kembali tersedia";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Gagal membatalkan pemesanan: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Pemesanan tidak ditemukan";
    }

    header("Location: pemesanan.php");
    exit;
}
