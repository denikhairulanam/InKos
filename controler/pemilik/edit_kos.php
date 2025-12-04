<?php
// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Koneksi database
include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Get kos ID
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: kos_saya.php');
    exit();
}

// Get kos data
try {
    $query = "SELECT * FROM kos WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $kos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kos) {
        $_SESSION['error_message'] = "Kos tidak ditemukan";
        header('Location: kos_saya.php');
        exit();
    }

    // Parse JSON fields
    $fasilitas = $kos['fasilitas'] ? json_decode($kos['fasilitas'], true) : [];
    $foto_lainnya = $kos['foto_lainnya'] ? json_decode($kos['foto_lainnya'], true) : [];

    // Gabungkan semua foto dalam satu array
    $semua_foto = [];
    if ($kos['foto_utama']) {
        $semua_foto[] = [
            'filename' => $kos['foto_utama'],
            'type' => 'utama',
            'is_main' => true
        ];
    }
    foreach ($foto_lainnya as $foto) {
        $semua_foto[] = [
            'filename' => $foto,
            'type' => 'lainnya',
            'is_main' => false
        ];
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: kos_saya.php');
    exit();
}

// Get common facilities
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

    // Get basic info
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

    // Handle existing photos (keep as is if not deleted)
    $current_photos = $semua_foto;
    $new_photos = [];

    // Handle photo deletions
    $photos_to_keep = [];
    if (isset($_POST['keep_photos']) && is_array($_POST['keep_photos'])) {
        foreach ($current_photos as $photo) {
            if (in_array($photo['filename'], $_POST['keep_photos'])) {
                $photos_to_keep[] = $photo;
            } else {
                // Delete file from server
                $file_path = "../uploads/" . $photo['filename'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }
    } else {
        // Keep all current photos if none selected for deletion
        $photos_to_keep = $current_photos;
    }

    // Handle new photos upload
    if (!empty($_FILES['new_fotos']['name'][0])) {
        for ($i = 0; $i < count($_FILES['new_fotos']['name']); $i++) {
            if ($_FILES['new_fotos']['error'][$i] == 0) {
                $file = [
                    'name' => $_FILES['new_fotos']['name'][$i],
                    'type' => $_FILES['new_fotos']['type'][$i],
                    'tmp_name' => $_FILES['new_fotos']['tmp_name'][$i],
                    'error' => $_FILES['new_fotos']['error'][$i],
                    'size' => $_FILES['new_fotos']['size'][$i]
                ];

                $upload_result = uploadFoto($file);
                if ($upload_result['success']) {
                    $new_photos[] = [
                        'filename' => $upload_result['filename'],
                        'type' => 'baru',
                        'is_main' => false
                    ];
                }
            }
        }
    }

    // Combine kept and new photos
    $all_photos = array_merge($photos_to_keep, $new_photos);

    // Determine main photo
    $new_foto_utama = null;
    $new_foto_lainnya = [];

    foreach ($all_photos as $photo) {
        if ($photo['is_main']) {
            $new_foto_utama = $photo['filename'];
        } else {
            $new_foto_lainnya[] = $photo['filename'];
        }
    }

    // If no main photo but there are photos, set first as main
    if (empty($new_foto_utama) && !empty($all_photos)) {
        $first_photo = $all_photos[0];
        $new_foto_utama = $first_photo['filename'];

        // Remove from lainnya if it was there
        $key = array_search($first_photo['filename'], $new_foto_lainnya);
        if ($key !== false) {
            unset($new_foto_lainnya[$key]);
            $new_foto_lainnya = array_values($new_foto_lainnya);
        }
    }

    // Prepare JSON for database
    $foto_lainnya_json = !empty($new_foto_lainnya) ? json_encode($new_foto_lainnya) : null;

    // Update database with ALL changes
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
                  featured = :featured,
                  foto_utama = :foto_utama,
                  foto_lainnya = :foto_lainnya,
                  updated_at = NOW()
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
        $stmt->bindParam(':foto_utama', $new_foto_utama);
        $stmt->bindParam(':foto_lainnya', $foto_lainnya_json);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Data kos berhasil diperbarui";

            // Update displayed data
            $kos['nama_kos'] = $nama_kos;
            $kos['deskripsi'] = $deskripsi;
            $kos['alamat'] = $alamat;
            $kos['daerah_id'] = $daerah_id;
            $kos['harga_bulanan'] = $harga_bulanan;
            $kos['tipe_kos'] = $tipe_kos;
            $kos['ukuran_kamar'] = $ukuran_kamar;
            $kos['kamar_mandi'] = $kamar_mandi;
            $kos['user_id'] = $user_id;
            $kos['status'] = $status;
            $kos['featured'] = $featured;
            $kos['foto_utama'] = $new_foto_utama;
            $foto_lainnya = $new_foto_lainnya;
            $fasilitas = isset($_POST['fasilitas']) ? $_POST['fasilitas'] : [];

            // Update semua_foto untuk tampilan
            $semua_foto = [];
            if ($new_foto_utama) {
                $semua_foto[] = [
                    'filename' => $new_foto_utama,
                    'type' => 'utama',
                    'is_main' => true
                ];
            }
            foreach ($new_foto_lainnya as $foto) {
                $semua_foto[] = [
                    'filename' => $foto,
                    'type' => 'lainnya',
                    'is_main' => false
                ];
            }
        } else {
            $error_message = "Gagal memperbarui data kos";
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }

    header("Location: edit_kos.php?id=$id");
    exit();
}

// Function to upload photo
function uploadFoto($file)
{
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB

    // Check file type
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'error' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.'];
    }

    // Check file size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'Ukuran file maksimal 2MB.'];
    }

    // Generate unique filename
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'kos_' . uniqid() . '.' . $file_extension;

    // Upload directory
    $upload_dir = "../uploads/";

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Gagal mengupload file.'];
    }
}

// Get users for dropdown
$users_query = "SELECT id, nama FROM users WHERE role = 'pemilik' ORDER BY nama";
$users_stmt = $db->query($users_query);
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get daerah for dropdown
$daerah_query = "SELECT id, nama, kota FROM daerah ORDER BY nama";
$daerah_stmt = $db->query($daerah_query);
$daerah_list = $daerah_stmt->fetchAll(PDO::FETCH_ASSOC);
?>