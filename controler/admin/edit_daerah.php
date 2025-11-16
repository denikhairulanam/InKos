<?php
checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Edit Daerah - INKOS";

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Get daerah data
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: daerah.php');
    exit();
}

try {
    $query = "SELECT * FROM daerah WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $daerah = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$daerah) {
        $_SESSION['error_message'] = "Daerah tidak ditemukan";
        header('Location: daerah.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: daerah.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $kota = $_POST['kota'];

    // Handle empty latitude/longitude - convert to NULL
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    try {
        $query = "UPDATE daerah SET nama = :nama, kota = :kota, latitude = :latitude, longitude = :longitude 
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':kota', $kota);
        $stmt->bindParam(':latitude', $latitude);
        $stmt->bindParam(':longitude', $longitude);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Daerah berhasil diperbarui";
            header('Location: daerah.php');
            exit();
        } else {
            $error_message = "Gagal memperbarui daerah";
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
include '../includes/header/admin_header.php';
?>