<?php
include '../config.php';

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Ambil data daerah untuk dropdown
$daerah_list = [];
try {
    $query = "SELECT id, nama, kota FROM daerah ORDER BY kota, nama";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $daerah_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error mengambil data daerah: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Ambil data dari form
        $nama_kos = $_POST['nama_kos'];
        $deskripsi = $_POST['deskripsi'];
        $alamat = $_POST['alamat'];
        $daerah_id = $_POST['daerah_id'];

        // Handle harga - hapus format titik
        $harga_bulanan = str_replace('.', '', $_POST['harga_bulanan']);

        $tipe_kos = $_POST['tipe_kos'];
        $ukuran_kamar = $_POST['ukuran_kamar'];
        $kamar_mandi = $_POST['kamar_mandi'];

        // Validasi harga minimal
        if ($harga_bulanan < 100000) {
            throw new Exception("Harga minimal Rp 100.000");
        }

        // Handle fasilitas (array to JSON)
        $fasilitas = isset($_POST['fasilitas']) ? json_encode($_POST['fasilitas']) : json_encode([]);

        // Handle foto utama upload
        $foto_utama = null;
        if (isset($_FILES['foto_utama']) && $_FILES['foto_utama']['error'] == 0) {
            $uploadDir = '../uploads/kos';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = pathinfo($_FILES['foto_utama']['name'], PATHINFO_EXTENSION);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array(strtolower($fileExtension), $allowedExtensions)) {
                $fileName = uniqid() . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['foto_utama']['tmp_name'], $filePath)) {
                    $foto_utama = $filePath;
                }
            }
        }

        // Handle multiple foto upload
        $foto_lainnya = [];
        if (isset($_FILES['foto_lainnya'])) {
            foreach ($_FILES['foto_lainnya']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['foto_lainnya']['error'][$key] == 0) {
                    $fileExtension = pathinfo($_FILES['foto_lainnya']['name'][$key], PATHINFO_EXTENSION);
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array(strtolower($fileExtension), $allowedExtensions)) {
                        $fileName = uniqid() . '_' . $key . '.' . $fileExtension;
                        $filePath = $uploadDir . $fileName;

                        if (move_uploaded_file($tmp_name, $filePath)) {
                            $foto_lainnya[] = $filePath;
                        }
                    }
                }
            }
        }
        $foto_lainnya_json = json_encode($foto_lainnya);

        // Generate ID otomatis
        $query = "SELECT MAX(id) as max_id FROM kos";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $new_id = $row['max_id'] ? $row['max_id'] + 1 : 1;

        // Insert data ke database
        $query = "INSERT INTO kos (id, nama_kos, deskripsi, alamat, daerah_id, harga_bulanan, tipe_kos, ukuran_kamar, kamar_mandi, fasilitas, foto_utama, foto_lainnya, user_id) 
                  VALUES (:id, :nama_kos, :deskripsi, :alamat, :daerah_id, :harga_bulanan, :tipe_kos, :ukuran_kamar, :kamar_mandi, :fasilitas, :foto_utama, :foto_lainnya, :user_id)";

        $stmt = $db->prepare($query);

        // Bind parameters
        $stmt->bindParam(':id', $new_id);
        $stmt->bindParam(':nama_kos', $nama_kos);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':daerah_id', $daerah_id);
        $stmt->bindParam(':harga_bulanan', $harga_bulanan);
        $stmt->bindParam(':tipe_kos', $tipe_kos);
        $stmt->bindParam(':ukuran_kamar', $ukuran_kamar);
        $stmt->bindParam(':kamar_mandi', $kamar_mandi);
        $stmt->bindParam(':fasilitas', $fasilitas);
        $stmt->bindParam(':foto_utama', $foto_utama);
        $stmt->bindParam(':foto_lainnya', $foto_lainnya_json);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Data kos berhasil ditambahkan!";
            header("Location:kos_saya.php");
            exit();
        } else {
            $error = "Gagal menambahkan data kos.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

?>