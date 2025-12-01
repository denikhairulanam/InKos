<?php

checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Manajemen Kos - INKOS";
include '../includes/header/admin_header.php';

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Handle delete
if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    try {
        // Hapus gambar terkait terlebih dahulu
        $query_select = "SELECT foto_utama FROM kos WHERE id = :id";
        $stmt_select = $db->prepare($query_select);
        $stmt_select->bindParam(':id', $id);
        $stmt_select->execute();
        $kos = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if ($kos && !empty($kos['foto_utama'])) {
            $file_path = "../uploads/" . $kos['foto_utama'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Hapus data kos
        $query = "DELETE FROM kos WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Kos berhasil dihapus";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus kos";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    header('Location: kos.php');
    exit();
}

// Handle featured toggle
if (isset($_POST['toggle_featured'])) {
    $id = $_POST['kos_id'];
    $featured = $_POST['featured_status'];

    try {
        $query = "UPDATE kos SET featured = :featured WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':featured', $featured, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $status_text = $featured ? 'featured' : 'biasa';
            $_SESSION['success_message'] = "Status kos berhasil diubah menjadi " . $status_text;
        } else {
            $_SESSION['error_message'] = "Gagal mengubah status featured";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    header('Location: kos.php');
    exit();
}

// Handle status toggle
if (isset($_POST['toggle_status'])) {
    $id = $_POST['kos_id'];
    $status = $_POST['kos_status'];

    try {
        $query = "UPDATE kos SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $status_text = $status == 'tersedia' ? 'tersedia' : 'tidak tersedia';
            $_SESSION['success_message'] = "Status kos berhasil diubah menjadi " . $status_text;
        } else {
            $_SESSION['error_message'] = "Gagal mengubah status kos";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    header('Location: kos.php');
    exit();
}

// Get all kos with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1; // Pastikan page tidak negatif
$limit = 10;
$offset = ($page - 1) * $limit;

// Search and filter functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipe_filter = isset($_GET['tipe']) ? $_GET['tipe'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$featured_filter = isset($_GET['featured']) ? $_GET['featured'] : '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(k.nama_kos LIKE :search OR k.alamat LIKE :search OR u.nama LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($tipe_filter)) {
    $where[] = "k.tipe_kos = :tipe";
    $params[':tipe'] = $tipe_filter;
}

if (!empty($status_filter)) {
    $where[] = "k.status = :status";
    $params[':status'] = $status_filter;
}

if ($featured_filter === 'featured') {
    $where[] = "k.featured = 1";
} elseif ($featured_filter === 'not_featured') {
    $where[] = "k.featured = 0";
}

$where_clause = '';
if (!empty($where)) {
    $where_clause = "WHERE " . implode(' AND ', $where);
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM kos k 
                LEFT JOIN users u ON k.user_id = u.id 
                $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_count / $limit);

// Validasi page number
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// Get data
$query = "SELECT k.*, u.nama as pemilik_nama, u.telepon as pemilik_telepon, 
          d.nama as daerah_nama, d.kota 
          FROM kos k 
          LEFT JOIN users u ON k.user_id = u.id 
          LEFT JOIN daerah d ON k.daerah_id = d.id 
          $where_clause 
          ORDER BY k.created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$kos_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get quick stats
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'tersedia' THEN 1 ELSE 0 END) as tersedia,
    SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) as featured,
    SUM(views) as total_views
    FROM kos";
$stats_stmt = $db->query($stats_query);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Set default values jika null
$stats['tersedia'] = $stats['tersedia'] ?? 0;
$stats['featured'] = $stats['featured'] ?? 0;
$stats['total_views'] = $stats['total_views'] ?? 0;
?>