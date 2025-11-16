<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pencari') {
    header('Location: ../login.php');
    exit;
}

$pencari_id = $_SESSION['user_id'];
$pemesanan_id = $_GET['pemesanan_id'] ?? null;

if (!$pemesanan_id) {
    header('Location: pemesanan.php');
    exit;
}

// Ambil data pemesanan termasuk rekening dari tabel kos
$query = "SELECT p.*, k.nama_kos, k.alamat, k.id as kos_id, k.rekening_pemilik, k.nama_rekening, k.bank,
                 d.nama as nama_daerah, u.nama as nama_pemilik, u.telepon as telepon_pemilik,
                 pb.id as pembayaran_id, pb.jumlah_bayar, pb.status_pembayaran,
                 pb.bukti_bayar, pb.metode_pembayaran, pb.tanggal_bayar
          FROM pemesanan p 
          JOIN kos k ON p.kos_id = k.id 
          JOIN daerah d ON k.daerah_id = d.id
          JOIN users u ON p.pemilik_id = u.id
          LEFT JOIN pembayaran pb ON p.id = pb.pemesanan_id
          WHERE p.id = ? AND p.pencari_id = ? AND p.status = 'dikonfirmasi'";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $pemesanan_id, $pencari_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Pemesanan tidak ditemukan atau tidak dapat melakukan pembayaran");
}

// Proses upload bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_pembayaran'])) {
    if ($_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/bukti_bayar/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            if ($_FILES['bukti_pembayaran']['size'] <= $max_file_size) {
                $file_name = 'bukti_' . $pemesanan_id . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $file_path)) {
                    try {
                        $conn->begin_transaction();

                        // Status pembayaran setelah upload
                        $status_pembayaran = 'menunggu';

                        if ($data['pembayaran_id']) {
                            // Update pembayaran yang sudah ada
                            $query_update = "UPDATE pembayaran 
                                            SET bukti_bayar = ?, status_pembayaran = ?, 
                                                tanggal_bayar = NOW(), metode_pembayaran = 'transfer'
                                            WHERE id = ?";
                            $stmt_update = $conn->prepare($query_update);
                            $stmt_update->bind_param("ssi", $file_name, $status_pembayaran, $data['pembayaran_id']);
                            $stmt_update->execute();
                        } else {
                            // Buat record pembayaran baru
                            $query_insert = "INSERT INTO pembayaran 
                                            (pemesanan_id, jumlah_bayar, metode_pembayaran, 
                                             status_pembayaran, bukti_bayar, tanggal_bayar) 
                                            VALUES (?, ?, 'transfer', ?, ?, NOW())";
                            $stmt_insert = $conn->prepare($query_insert);
                            $stmt_insert->bind_param("idss", $pemesanan_id, $data['total_harga'], $status_pembayaran, $file_name);
                            $stmt_insert->execute();
                        }

                        // Update status_pembayaran di tabel pemesanan
                        $query_pemesanan = "UPDATE pemesanan SET status_pembayaran = ? WHERE id = ?";
                        $stmt_pemesanan = $conn->prepare($query_pemesanan);
                        $stmt_pemesanan->bind_param("si", $status_pembayaran, $pemesanan_id);
                        $stmt_pemesanan->execute();

                        $conn->commit();
                        $_SESSION['success'] = "Bukti pembayaran berhasil diupload! Menunggu verifikasi dari pemilik kos.";
                        header("Location: pembayaran.php?pemesanan_id=" . $pemesanan_id);
                        exit;
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error = "Terjadi kesalahan: " . $e->getMessage();

                        // Hapus file yang sudah diupload jika ada error
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                } else {
                    $error = "Gagal mengupload file bukti pembayaran";
                }
            } else {
                $error = "Ukuran file terlalu besar. Maksimal 5MB";
            }
        } else {
            $error = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau PDF";
        }
    } else {
        $error = "Harap pilih file bukti pembayaran";
    }
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bukti Pembayaran - INKOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            padding-top: 80px;
            background-color: #f8f9fa;
        }

        .payment-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 2rem;
        }

        .bank-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
        }

        .upload-area {
            border: 2px dashed #007bff;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            background: #f8f9ff;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-area:hover {
            background: #e3f2fd;
        }

        .status-pending {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
        }

        .status-success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
        }

        .status-failed {
            background: linear-gradient(135deg, #f78ca0 0%, #f9748f 100%);
            color: white;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>

<body>
    <?php include '../includes/header/header.php'; ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="payment-card">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-credit-card me-2"></i>Pembayaran Kos
                        </h3>

                        <?php if (isset($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i><?= $success ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Info Pemesanan -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($data['nama_kos']) ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($data['alamat']) ?>
                                </p>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Periode Sewa:</small>
                                        <p class="mb-1 fw-bold">
                                            <?= date('d M Y', strtotime($data['tanggal_mulai'])) ?> -
                                            <?= date('d M Y', strtotime($data['tanggal_selesai'])) ?>
                                        </p>
                                        <small class="text-muted">(<?= $data['durasi_bulan'] ?> bulan)</small>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted">Total Pembayaran:</small>
                                        <h4 class="text-success mb-0">Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tampilkan status berdasarkan kondisi -->
                        <?php
                        $current_payment_status = $data['status_pembayaran'] ?? 'menunggu';
                        $has_payment_record = $data['pembayaran_id'] ?? false;

                        if (!$has_payment_record || $current_payment_status === 'menunggu' || $current_payment_status === 'gagal'):
                        ?>
                            <!-- Form Upload Bukti Bayar -->
                            <?php if ($data['rekening_pemilik'] && $data['bank']): ?>
                                <div class="bank-info mb-4">
                                    <h5 class="text-center mb-3">
                                        <i class="fas fa-university me-2"></i>Transfer ke Rekening Berikut
                                    </h5>
                                    <div class="text-center">
                                        <h4 class="mb-2"><?= htmlspecialchars($data['bank']) ?></h4>
                                        <h3 class="mb-2"><?= htmlspecialchars($data['rekening_pemilik']) ?></h3>
                                        <p class="mb-0">a.n. <?= htmlspecialchars($data['nama_rekening']) ?></p>
                                    </div>
                                    <div class="mt-3 p-3 bg-white bg-opacity-10 rounded">
                                        <small>
                                            <i class="fas fa-info-circle me-1"></i>
                                            Transfer tepat sesuai jumlah <strong>Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></strong> dan upload bukti transfer Anda
                                        </small>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Informasi rekening belum tersedia. Silakan hubungi pemilik:
                                    <strong><?= htmlspecialchars($data['nama_pemilik']) ?> - <?= htmlspecialchars($data['telepon_pemilik']) ?></strong>
                                </div>
                            <?php endif; ?>

                            <?php if ($current_payment_status === 'gagal'): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Pembayaran sebelumnya ditolak. Silakan upload ulang bukti transfer yang valid.
                                </div>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Upload Bukti Transfer</label>
                                    <div class="upload-area" id="uploadArea">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-3"></i>
                                        <p class="mb-2">Klik atau drag file ke sini</p>
                                        <small class="text-muted">Format: JPG, JPEG, PNG, PDF (Maks. 5MB)</small>
                                        <input type="file" name="bukti_pembayaran" id="buktiPembayaran"
                                            accept=".jpg,.jpeg,.png,.pdf" style="display: none;" required>
                                        <div id="fileName" class="mt-2 fw-bold text-success"></div>
                                        <div id="fileSize" class="mt-1 small text-muted"></div>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn" disabled>
                                        <i class="fas fa-paper-plane me-2"></i>
                                        <?= $current_payment_status === 'gagal' ? 'Upload Ulang Bukti Pembayaran' : 'Upload Bukti Pembayaran' ?>
                                    </button>
                                </div>
                            </form>

                        <?php elseif ($current_payment_status === 'menunggu'): ?>
                            <!-- Status Menunggu Verifikasi -->
                            <div class="status-pending">
                                <i class="fas fa-clock fa-3x mb-3"></i>
                                <h4>Menunggu Verifikasi</h4>
                                <p class="mb-3">Bukti pembayaran Anda sedang menunggu verifikasi dari pemilik kos.</p>

                                <?php if ($data['bukti_bayar']): ?>
                                    <div class="mt-3">
                                        <a href="../uploads/bukti_bayar/<?= $data['bukti_bayar'] ?>"
                                            target="_blank" class="btn btn-light btn-sm">
                                            <i class="fas fa-eye me-1"></i>Lihat Bukti yang Diupload
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                        <?php elseif ($current_payment_status === 'lunas'): ?>
                            <!-- Status Lunas -->
                            <div class="status-success">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <h4>Pembayaran Berhasil!</h4>
                                <p class="mb-3">Pembayaran Anda telah diverifikasi dan diterima.</p>

                                <?php if ($data['tanggal_bayar']): ?>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar me-1"></i>
                                        Tanggal Bayar: <?= date('d M Y H:i', strtotime($data['tanggal_bayar'])) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                        <?php endif; ?>

                        <!-- Navigation -->
                        <div class="text-center mt-4">
                            <a href="pemesanan.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Riwayat Pemesanan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('buktiPembayaran');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const submitBtn = document.getElementById('submitBtn');
            const maxSize = 5 * 1024 * 1024; // 5MB

            // Click upload area
            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });

            // Drag and drop functionality
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.style.background = '#e3f2fd';
                uploadArea.style.borderColor = '#0d6efd';
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadArea.style.background = '#f8f9ff';
                uploadArea.style.borderColor = '#007bff';
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.style.background = '#f8f9ff';
                uploadArea.style.borderColor = '#007bff';

                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    handleFileSelection(e.dataTransfer.files[0]);
                }
            });

            // File input change
            fileInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    handleFileSelection(e.target.files[0]);
                }
            });

            function handleFileSelection(file) {
                if (file.size > maxSize) {
                    alert('Ukuran file terlalu besar. Maksimal 5MB');
                    resetFileInput();
                    return;
                }

                fileName.textContent = `File: ${file.name}`;
                fileSize.textContent = `Ukuran: ${(file.size / 1024 / 1024).toFixed(2)} MB`;
                submitBtn.disabled = false;
            }

            function resetFileInput() {
                fileInput.value = '';
                fileName.textContent = '';
                fileSize.textContent = '';
                submitBtn.disabled = true;
            }

            // Form validation
            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                if (!fileInput.files.length) {
                    e.preventDefault();
                    alert('Harap pilih file bukti pembayaran');
                    return;
                }

                const file = fileInput.files[0];

                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('Ukuran file maksimal 5MB');
                    return;
                }

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengupload...';
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>