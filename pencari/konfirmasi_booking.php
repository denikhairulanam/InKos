<?php
include '../controler/pencari/konfirmasi_booking.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Booking - INKOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }

        .success-icon {
            font-size: 4rem;
            color: #28a745;
        }

        .confirmation-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card confirmation-card text-center">
                    <div class="card-body p-5">
                        <div class="success-icon mb-4">
                            <i class="fas fa-check-circle"></i>
                        </div>

                        <h3 class="text-success mb-3">Booking Berhasil!</h3>
                        <p class="text-muted mb-4">
                            Pemesanan Anda telah berhasil dikirim. Silakan tunggu konfirmasi dari pemilik kos.
                        </p>

                        <div class="booking-details text-start mb-4">
                            <h5>Detail Pemesanan:</h5>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Kos:</strong><br>
                                    <strong>Periode:</strong><br>
                                    <strong>Durasi:</strong><br>
                                    <strong>Total:</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <?= htmlspecialchars($pemesanan['nama_kos']) ?><br>
                                    <?= date('d M Y', strtotime($pemesanan['tanggal_mulai'])) ?> -
                                    <?= date('d M Y', strtotime($pemesanan['tanggal_selesai'])) ?><br>
                                    <?= $pemesanan['durasi_bulan'] ?> Bulan<br>
                                    <span class="text-success fw-bold">
                                        Rp <?= number_format($pemesanan['total_harga'], 0, ',', '.') ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="pemesanan.php" class="btn btn-primary">
                                <i class="fas fa-history me-2"></i>Lihat Riwayat Pemesanan
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>