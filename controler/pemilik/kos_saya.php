<?php
include '../config.php';
include '../includes/auth.php';

// Check authentication dan role
checkAuth();

if (getUserRole() !== 'pemilik') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Set page title
$page_title = "Kos Saya - INKOS";

// Include header SETELAH semua processing
include '../includes/header/pemilik_header.php';

// Ambil data kos milik user
$kos_list = [];
try {
    $query = "SELECT * FROM kos WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $kos_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error mengambil data: " . $e->getMessage();
}
include '../includes/header/pemilik_header.php';
?>