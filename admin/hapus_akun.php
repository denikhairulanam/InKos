<?php
include '../includes/auth.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../config.php';
    $database = new Database();
    $db = $database->getConnection();

    $user_id = $_SESSION['user_id'];

    try {
        // Start transaction
        $db->beginTransaction();

        // Delete user's data (kos, laporan, etc.)
        // Implement sesuai kebutuhan

        // Delete user
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();

        $db->commit();

        // Logout and redirect
        session_destroy();
        header('Location: ../index.php?message=account_deleted');
        exit();
    } catch (PDOException $e) {
        $db->rollBack();
        $_SESSION['error_message'] = "Gagal menghapus akun: " . $e->getMessage();
        header('Location: profil.php');
        exit();
    }
}
