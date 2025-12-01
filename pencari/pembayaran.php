<?php 
include '../controler/pencari/pembayaran.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bukti Pembayaran - INKOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/pencari/pembayaran.css">
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