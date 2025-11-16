<?php
include '../includes/auth.php';

$page_title = "Edit Kos - INKOS";
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
    $query = "SELECT * FROM kos WHERE id = :id";
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

// Get data for dropdowns
$users_query = "SELECT id, nama FROM users WHERE role = 'pemilik' ORDER BY nama";
$users_stmt = $db->query($users_query);
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

$daerah_query = "SELECT id, nama, kota FROM daerah ORDER BY nama";
$daerah_stmt = $db->query($daerah_query);
$daerah_list = $daerah_stmt->fetchAll(PDO::FETCH_ASSOC);

// Common facilities
$common_facilities = [
    'WiFi',
    'AC',
    'Kipas Angin',
    'Lemari',
    'Kasur',
    'Meja',
    'Kursi',
    'Kamar Mandi Dalam',
    'Kamar Mandi Luar',
    'Dapur',
    'Laundry',
    'Parkir Motor',
    'Parkir Mobil',
    'Security',
    'CCTV',
    'Listrik Included'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kos = $_POST['nama_kos'];
    $deskripsi = $_POST['deskripsi'];
    $alamat = $_POST['alamat'];
    $daerah_id = $_POST['daerah_id'] ?: null;
    $harga_bulanan = $_POST['harga_bulanan'];
    $tipe_kos = $_POST['tipe_kos'];
    $ukuran_kamar = $_POST['ukuran_kamar'];
    $kamar_mandi = $_POST['kamar_mandi'];
    $user_id = $_POST['user_id'];
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Handle facilities
    $fasilitas = isset($_POST['fasilitas']) ? $_POST['fasilitas'] : [];
    $fasilitas_json = !empty($fasilitas) ? json_encode($fasilitas) : null;

    // Handle file uploads (you can implement this later)
    // $foto_utama = handleFileUpload('foto_utama');
    // $foto_lainnya = handleMultipleFileUpload('foto_lainnya');

    try {
        $query = "UPDATE kos SET 
                  nama_kos = :nama_kos, 
                  deskripsi = :deskripsi, 
                  alamat = :alamat, 
                  daerah_id = :daerah_id, 
                  harga_bulanan = :harga_bulanan, 
                  tipe_kos = :tipe_kos, 
                  ukuran_kamar = :ukuran_kamar, 
                  kamar_mandi = :kamar_mandi, 
                  fasilitas = :fasilitas, 
                  user_id = :user_id, 
                  status = :status, 
                  featured = :featured 
                  WHERE id = :id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama_kos', $nama_kos);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':daerah_id', $daerah_id);
        $stmt->bindParam(':harga_bulanan', $harga_bulanan);
        $stmt->bindParam(':tipe_kos', $tipe_kos);
        $stmt->bindParam(':ukuran_kamar', $ukuran_kamar);
        $stmt->bindParam(':kamar_mandi', $kamar_mandi);
        $stmt->bindParam(':fasilitas', $fasilitas_json);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':featured', $featured);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Kos berhasil diperbarui";
            header('Location: kos.php');
            exit();
        } else {
            $error_message = "Gagal memperbarui kos";
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>