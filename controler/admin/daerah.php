<?php
checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Manajemen Daerah - INKOS";
include '../includes/header/admin_header.php';

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Handle delete
if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    try {
        $query = "DELETE FROM daerah WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Daerah berhasil dihapus";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus daerah";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    header('Location: daerah.php');
    exit();
}

// Get all daerah with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE nama LIKE :search OR kota LIKE :search";
    $params[':search'] = "%$search%";
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM daerah $where";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_count / $limit);

// Get data
$query = "SELECT * FROM daerah $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$daerah = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>