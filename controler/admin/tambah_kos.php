<?php
checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Tambah Kos - INKOS";
include '../includes/header/admin_header.php';

include '../config.php';
$database = new Database();
$db = $database->getConnection();

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

    // Handle file uploads
    $foto_utama = null;
    $foto_lainnya = [];

    // Upload foto utama
    if (isset($_FILES['foto_utama']) && $_FILES['foto_utama']['error'] === UPLOAD_ERR_OK) {
        $foto_utama = uploadFoto($_FILES['foto_utama'], 'utama');
        if (!$foto_utama) {
            $error_message = "Gagal mengupload foto utama";
        }
    }

    // Upload foto lainnya
    if (isset($_FILES['foto_lainnya']) && !empty($_FILES['foto_lainnya']['name'][0])) {
        foreach ($_FILES['foto_lainnya']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['foto_lainnya']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['foto_lainnya']['name'][$key],
                    'type' => $_FILES['foto_lainnya']['type'][$key],
                    'tmp_name' => $tmp_name,
                    'error' => $_FILES['foto_lainnya']['error'][$key],
                    'size' => $_FILES['foto_lainnya']['size'][$key]
                ];

                $uploaded_foto = uploadFoto($file, 'lainnya');
                if ($uploaded_foto) {
                    $foto_lainnya[] = $uploaded_foto;
                }
            }
        }
    }

    // Jika tidak ada error upload, lanjutkan insert ke database
    if (!isset($error_message)) {
        try {
            $foto_lainnya_json = !empty($foto_lainnya) ? json_encode($foto_lainnya) : null;

            $query = "INSERT INTO kos (nama_kos, deskripsi, alamat, daerah_id, harga_bulanan, 
                      tipe_kos, ukuran_kamar, kamar_mandi, fasilitas, foto_utama, foto_lainnya, user_id, status, featured) 
                      VALUES (:nama_kos, :deskripsi, :alamat, :daerah_id, :harga_bulanan, 
                      :tipe_kos, :ukuran_kamar, :kamar_mandi, :fasilitas, :foto_utama, :foto_lainnya, :user_id, :status, :featured)";

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
            $stmt->bindParam(':foto_utama', $foto_utama);
            $stmt->bindParam(':foto_lainnya', $foto_lainnya_json);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':featured', $featured);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Kos berhasil ditambahkan";
                ob_end_clean();
                header('Location: kos.php');
                exit();
            } else {
                $error_message = "Gagal menambahkan kos";
            }
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Function to handle file upload
function uploadFoto($file, $type)
{
    $uploadDir = '../uploads/';

    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $type . '_' . uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }

    return false;
}
