<?php
include '../includes/auth.php';
include '../config.php';

// Check authentication dan role di awal - SEBELUM output apapun
checkAuth();
if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle delete - SEBELUM output apapun
if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    try {
        // Check if user has related data in kos table
        $check_query = "SELECT COUNT(*) as count FROM kos WHERE user_id = :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $id);
        $check_stmt->execute();
        $kos_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];

        if ($kos_count > 0) {
            $_SESSION['error_message'] = "Tidak dapat menghapus user karena memiliki data kos terkait";
        } else {
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "User berhasil dihapus";
            } else {
                $_SESSION['error_message'] = "Gagal menghapus user";
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    header('Location: users.php');
    exit();
}

// Handle verification - SEBELUM output apapun
if (isset($_POST['verify_id'])) {
    $id = $_POST['verify_id'];
    try {
        $query = "UPDATE users SET is_verified = 1 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "User berhasil diverifikasi";
        } else {
            $_SESSION['error_message'] = "Gagal memverifikasi user";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    header('Location: users.php');
    exit();
}

// Get all users with pagination - SEBELUM output apapun
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search and filter functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$verification_filter = isset($_GET['verification']) ? $_GET['verification'] : '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(nama LIKE :search OR email LIKE :search OR telepon LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($role_filter)) {
    $where[] = "role = :role";
    $params[':role'] = $role_filter;
}

if ($verification_filter === 'verified') {
    $where[] = "is_verified = 1";
} elseif ($verification_filter === 'unverified') {
    $where[] = "is_verified = 0";
}

$where_clause = '';
if (!empty($where)) {
    $where_clause = "WHERE " . implode(' AND ', $where);
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM users $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_count / $limit);

// Get data
$query = "SELECT *, 
          (SELECT COUNT(*) FROM kos WHERE user_id = users.id) as kos_count 
          FROM users 
          $where_clause 
          ORDER BY created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// SETELAH SEMUA PROCESSING, BARU INCLUDE HEADER
$page_title = "Manajemen User - INKOS";
include '../includes/header/admin_header.php';
