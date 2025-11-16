<?php
// controller/admin/pembayaran.php

require_once '../config.php';

class PembayaranController
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // Get all payments with filters
    public function getPembayaran($page = 1, $limit = 10, $search = '', $status = '')
    {
        $offset = ($page - 1) * $limit;

        $query = "SELECT 
                    pb.*,
                    p.id as pemesanan_id,
                    p.total_harga,
                    p.status as status_pemesanan,
                    p.bukti_pembayaran as bukti_pemesanan,
                    u_pencari.nama as nama_pencari,
                    u_pencari.email as email_pencari,
                    u_pemilik.nama as nama_pemilik,
                    k.nama_kos,
                    k.harga_bulanan
                 FROM pembayaran pb
                 JOIN pemesanan p ON pb.pemesanan_id = p.id
                 JOIN users u_pencari ON p.pencari_id = u_pencari.id
                 JOIN users u_pemilik ON p.pemilik_id = u_pemilik.id
                 JOIN kos k ON p.kos_id = k.id
                 WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $query .= " AND (u_pencari.nama LIKE ? OR u_pencari.email LIKE ? OR k.nama_kos LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($status)) {
            $query .= " AND pb.status_pembayaran = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY pb.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $pembayaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $countQuery = "SELECT COUNT(*) as total 
                          FROM pembayaran pb
                          JOIN pemesanan p ON pb.pemesanan_id = p.id
                          JOIN users u_pencari ON p.pencari_id = u_pencari.id
                          JOIN users u_pemilik ON p.pemilik_id = u_pemilik.id
                          JOIN kos k ON p.kos_id = k.id
                          WHERE 1=1";

            if (!empty($search)) {
                $countQuery .= " AND (u_pencari.nama LIKE ? OR u_pencari.email LIKE ? OR k.nama_kos LIKE ?)";
            }

            if (!empty($status)) {
                $countQuery .= " AND pb.status_pembayaran = ?";
            }

            $countStmt = $this->db->prepare($countQuery);
            $countParams = [];

            if (!empty($search)) {
                $searchTerm = "%$search%";
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
            }

            if (!empty($status)) {
                $countParams[] = $status;
            }

            $countStmt->execute($countParams);
            $total_count = $countStmt->fetch()['total'];

            return [
                'pembayaran' => $pembayaran,
                'total_count' => $total_count,
                'total_pages' => ceil($total_count / $limit),
                'current_page' => $page
            ];
        } catch (PDOException $e) {
            return [
                'pembayaran' => [],
                'total_count' => 0,
                'total_pages' => 0,
                'current_page' => $page
            ];
        }
    }

    // Get payment detail by ID
    public function getDetailPembayaran($id)
    {
        $query = "SELECT 
                    pb.*,
                    p.id as pemesanan_id,
                    p.tanggal_mulai,
                    p.tanggal_selesai,
                    p.durasi_bulan,
                    p.total_harga,
                    p.status as status_pemesanan,
                    p.status_pembayaran as status_pembayaran_pemesanan,
                    p.bukti_pembayaran as bukti_pemesanan,
                    p.catatan_pembatalan,
                    u_pencari.id as pencari_id,
                    u_pencari.nama as nama_pencari,
                    u_pencari.email as email_pencari,
                    u_pencari.telepon as telepon_pencari,
                    u_pencari.alamat as alamat_pencari,
                    u_pencari.universitas as universitas_pencari,
                    u_pemilik.id as pemilik_id,
                    u_pemilik.nama as nama_pemilik,
                    u_pemilik.email as email_pemilik,
                    u_pemilik.telepon as telepon_pemilik,
                    k.id as kos_id,
                    k.nama_kos,
                    k.alamat as alamat_kos,
                    k.harga_bulanan,
                    k.tipe_kos,
                    k.foto_utama
                 FROM pembayaran pb
                 JOIN pemesanan p ON pb.pemesanan_id = p.id
                 JOIN users u_pencari ON p.pencari_id = u_pencari.id
                 JOIN users u_pemilik ON p.pemilik_id = u_pemilik.id
                 JOIN kos k ON p.kos_id = k.id
                 WHERE pb.id = ?";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // Update payment status
    public function updateStatusPembayaran($id, $status, $admin_id)
    {
        $query = "UPDATE pembayaran 
                 SET status_pembayaran = ?, 
                     tanggal_bayar = CASE WHEN ? = 'lunas' THEN NOW() ELSE tanggal_bayar END
                 WHERE id = ?";

        try {
            $stmt = $this->db->prepare($query);
            $success = $stmt->execute([$status, $status, $id]);

            // Also update pemesanan status_pembayaran
            if ($success && $status == 'lunas') {
                $pemesananQuery = "UPDATE pemesanan 
                                  SET status_pembayaran = 'lunas',
                                      status = 'dikonfirmasi'
                                  WHERE id = (SELECT pemesanan_id FROM pembayaran WHERE id = ?)";
                $pemesananStmt = $this->db->prepare($pemesananQuery);
                $pemesananStmt->execute([$id]);
            }

            return $success;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Delete payment
    public function deletePembayaran($id)
    {
        $query = "DELETE FROM pembayaran WHERE id = ?";

        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Initialize controller
$pembayaranController = new PembayaranController($db);

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $payment_id = $_POST['payment_id'];
        $new_status = $_POST['status_pembayaran'];
        $admin_id = $_SESSION['user_id'];

        if ($pembayaranController->updateStatusPembayaran($payment_id, $new_status, $admin_id)) {
            $_SESSION['success_message'] = "Status pembayaran berhasil diperbarui!";
        } else {
            $_SESSION['error_message'] = "Gagal memperbarui status pembayaran!";
        }

        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $payment_id);
        exit;
    }

    if (isset($_POST['delete_id'])) {
        $payment_id = $_POST['delete_id'];

        if ($pembayaranController->deletePembayaran($payment_id)) {
            $_SESSION['success_message'] = "Data pembayaran berhasil dihapus!";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus data pembayaran!";
        }

        header("Location: pembayaran.php");
        exit;
    }
}

// Get parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$limit = 10;

// Get payment data
$data = $pembayaranController->getPembayaran($page, $limit, $search, $status_filter);
$pembayaran = $data['pembayaran'];
$total_count = $data['total_count'];
$total_pages = $data['total_pages'];
$current_page = $data['current_page'];
$offset = ($page - 1) * $limit;

// Get detail if ID provided
$detail_pembayaran = null;
if (isset($_GET['id'])) {
    $detail_pembayaran = $pembayaranController->getDetailPembayaran($_GET['id']);
}
