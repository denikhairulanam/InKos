<?php
session_start();
require_once '../config.php';
require_once '../includes/auth.php';

// Check authentication
checkAuth();

// Only pemilik can access
if (getUserRole() !== 'pemilik') {
    $_SESSION['error'] = "Akses ditolak. Hanya pemilik kos yang dapat mengakses halaman ini.";
    header('Location: ../login.php');
    exit();
}

// Check if kos ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID kos tidak valid.";
    header('Location: kos_saya.php');
    exit();
}

$kos_id = $_GET['id'];
$pemilik_id = $_SESSION['user_id'];

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

try {
    // Begin transaction
    $db->beginTransaction();

    // 1. Check if kos exists and belongs to the pemilik
    $query_check = "SELECT k.*, d.nama as nama_daerah 
                   FROM kos k 
                   LEFT JOIN daerah d ON k.daerah_id = d.id 
                   WHERE k.id = :kos_id AND k.user_id = :pemilik_id";
    $stmt_check = $db->prepare($query_check);
    $stmt_check->bindParam(':kos_id', $kos_id);
    $stmt_check->bindParam(':pemilik_id', $pemilik_id);
    $stmt_check->execute();
    $kos = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$kos) {
        $_SESSION['error'] = "Kos tidak ditemukan atau Anda tidak memiliki akses untuk menghapus kos ini.";
        header('Location: kos_saya.php');
        exit();
    }

    // 2. Check if there are active pemesanan for this kos
    $query_check_pemesanan = "SELECT COUNT(*) as total_pemesanan 
                             FROM pemesanan 
                             WHERE kos_id = :kos_id 
                             AND status IN ('menunggu', 'dikonfirmasi')";
    $stmt_check_pemesanan = $db->prepare($query_check_pemesanan);
    $stmt_check_pemesanan->bindParam(':kos_id', $kos_id);
    $stmt_check_pemesanan->execute();
    $result_pemesanan = $stmt_check_pemesanan->fetch(PDO::FETCH_ASSOC);

    if ($result_pemesanan['total_pemesanan'] > 0) {
        $_SESSION['error'] = "Tidak dapat menghapus kos karena terdapat pemesanan aktif. Batalkan atau selesaikan pemesanan terlebih dahulu.";
        header('Location: kos_saya.php');
        exit();
    }

    // 3. Get photos to delete from server
    $photos_to_delete = [];

    // Add main photo
    if (!empty($kos['foto_utama']) && $kos['foto_utama'] != 'default.jpg') {
        $photos_to_delete[] = $kos['foto_utama'];
    }

    // Add other photos from JSON field
    if (!empty($kos['foto_lainnya'])) {
        $foto_lainnya = json_decode($kos['foto_lainnya'], true);
        if (is_array($foto_lainnya)) {
            foreach ($foto_lainnya as $foto) {
                if (!empty($foto) && $foto != 'default.jpg') {
                    $photos_to_delete[] = $foto;
                }
            }
        }
    }

    // 4. Delete related data first (to maintain referential integrity)

    // Delete from favorit if exists
    try {
        $query_delete_favorit = "DELETE FROM favorit WHERE kos_id = :kos_id";
        $stmt_favorit = $db->prepare($query_delete_favorit);
        $stmt_favorit->bindParam(':kos_id', $kos_id);
        $stmt_favorit->execute();
    } catch (PDOException $e) {
        // Table might not exist, continue
    }

    // Delete completed/cancelled pemesanan history
    try {
        $query_delete_pemesanan = "DELETE FROM pemesanan WHERE kos_id = :kos_id AND status IN ('selesai', 'ditolak', 'dibatalkan')";
        $stmt_pemesanan = $db->prepare($query_delete_pemesanan);
        $stmt_pemesanan->bindParam(':kos_id', $kos_id);
        $stmt_pemesanan->execute();
    } catch (PDOException $e) {
        // Table might not exist, continue
    }

    // 5. Finally delete the kos
    $query_delete_kos = "DELETE FROM kos WHERE id = :kos_id AND user_id = :pemilik_id";
    $stmt_delete = $db->prepare($query_delete_kos);
    $stmt_delete->bindParam(':kos_id', $kos_id);
    $stmt_delete->bindParam(':pemilik_id', $pemilik_id);
    $stmt_delete->execute();

    $rows_affected = $stmt_delete->rowCount();

    if ($rows_affected === 0) {
        throw new Exception("Gagal menghapus kos. Data tidak ditemukan.");
    }

    // Commit transaction
    $db->commit();

    // 6. Delete photos from server
    $upload_dir = '../uploads/';
    foreach ($photos_to_delete as $photo) {
        $file_path = $upload_dir . $photo;
        if (file_exists($file_path) && is_file($file_path)) {
            unlink($file_path);
        }
    }

    $_SESSION['success'] = "Kos '{$kos['nama_kos']}' berhasil dihapus.";
    header('Location: kos_saya.php');
    exit();
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    error_log("Database Error in hapus_kos.php: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan database saat menghapus kos. Silakan coba lagi.";
    header('Location: kos_saya.php');
    exit();
} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    error_log("Error in hapus_kos.php: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header('Location: kos_saya.php');
    exit();
}
