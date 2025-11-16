<?php
checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Tambah Daerah - INKOS";

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $kota = $_POST['kota'];

    // Handle empty latitude/longitude - convert to NULL
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    try {
        $query = "INSERT INTO daerah (nama, kota, latitude, longitude) 
                  VALUES (:nama, :kota, :latitude, :longitude)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':kota', $kota);
        $stmt->bindParam(':latitude', $latitude);
        $stmt->bindParam(':longitude', $longitude);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Daerah berhasil ditambahkan";
            header('Location: daerah.php');
            exit();
        } else {
            $error_message = "Gagal menambahkan daerah";
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
include '../includes/header/admin_header.php';
?>