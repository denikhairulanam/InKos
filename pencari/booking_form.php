<?php 
include '../controler/pencari/booking_form.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Kos - INKOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/pencari/booking_form.css">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card booking-card">
                    <div class="kos-info p-4">
                        <h3 class="mb-2"><?= htmlspecialchars($kos['nama_kos']) ?></h3>
                        <p class="mb-1">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?= htmlspecialchars($kos['alamat']) ?>, <?= htmlspecialchars($kos['nama_daerah']) ?>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            Pemilik: <?= htmlspecialchars($kos['nama_pemilik']) ?>
                        </p>
                    </div>

                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-calendar-plus me-2"></i>Form Pemesanan
                        </h4>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="bookingForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt me-2"></i>Tanggal Mulai Sewa
                                    </label>
                                    <input type="date" class="form-control" name="tanggal_mulai"
                                        min="<?= date('Y-m-d') ?>"
                                        value="<?= date('Y-m-d') ?>"
                                        required>
                                    <div class="form-text">Pilih tanggal mulai menempati kos</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-clock me-2"></i>Durasi Sewa
                                    </label>
                                    <select class="form-control" name="durasi_bulan" required>
                                        <option value="">Pilih Durasi</option>
                                        <option value="1">1 Bulan</option>
                                        <option value="3">3 Bulan</option>
                                        <option value="6">6 Bulan</option>
                                        <option value="12">12 Bulan</option>
                                    </select>
                                    <div class="form-text">Pilih lama sewa</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-sticky-note me-2"></i>Catatan Tambahan (Opsional)
                                </label>
                                <textarea class="form-control" name="catatan_tambahan" rows="3"
                                    placeholder="Masukkan catatan khusus untuk pemilik kos, seperti permintaan khusus atau pertanyaan..."></textarea>
                            </div>

                            <!-- Informasi Kos -->
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Informasi Kos
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong>Tipe:</strong>
                                                <span class="badge bg-<?= $kos['tipe_kos'] == 'putra' ? 'primary' : ($kos['tipe_kos'] == 'putri' ? 'danger' : 'warning') ?>">
                                                    <?= ucfirst($kos['tipe_kos']) ?>
                                                </span>
                                            </p>
                                            <p class="mb-2">
                                                <strong>Ukuran Kamar:</strong> <?= htmlspecialchars($kos['ukuran_kamar']) ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong>Kamar Mandi:</strong> <?= ucfirst($kos['kamar_mandi']) ?>
                                            </p>
                                            <p class="mb-0">
                                                <strong>Fasilitas:</strong>
                                                <?php
                                                $fasilitas = json_decode($kos['fasilitas'] ?? '[]', true);
                                                if (is_array($fasilitas) && !empty($fasilitas)) {
                                                    echo implode(', ', array_slice($fasilitas, 0, 3));
                                                    if (count($fasilitas) > 3) {
                                                        echo '...';
                                                    }
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ringkasan Biaya -->
                            <div class="booking-summary p-4 bg-light rounded mb-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-receipt me-2"></i>Ringkasan Biaya
                                </h5>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-2">Harga per bulan:</p>
                                        <p class="mb-2">Durasi sewa:</p>
                                        <p class="mb-0"><strong>Total biaya:</strong></p>
                                    </div>
                                    <div class="col-6 text-end">
                                        <p class="mb-2">Rp <?= number_format($kos['harga_bulanan'], 0, ',', '.') ?></p>
                                        <p class="mb-2" id="summary-durasi">0 Bulan</p>
                                        <p class="mb-0">
                                            <strong class="text-success fs-5" id="total-harga">
                                                Rp <?= number_format($kos['harga_bulanan'], 0, ',', '.') ?>
                                            </strong>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg py-3">
                                    <i class="fas fa-calendar-check me-2"></i>Konfirmasi Pemesanan
                                </button>
                                <a href="detail_kos.php?id=<?= $kos_id ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Detail Kos
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update total harga berdasarkan durasi
        document.addEventListener('DOMContentLoaded', function() {
            const hargaPerBulan = <?= $kos['harga_bulanan'] ?>;
            const durasiSelect = document.querySelector('select[name="durasi_bulan"]');
            const summaryDurasi = document.getElementById('summary-durasi');
            const totalHargaElement = document.getElementById('total-harga');

            function updateTotalHarga() {
                const durasi = durasiSelect.value || 1;
                const totalHarga = hargaPerBulan * durasi;

                summaryDurasi.textContent = durasi + ' Bulan';
                totalHargaElement.textContent = 'Rp ' + totalHarga.toLocaleString('id-ID');
            }

            durasiSelect.addEventListener('change', updateTotalHarga);

            // Initialize
            updateTotalHarga();

            // Form validation
            document.getElementById('bookingForm').addEventListener('submit', function(e) {
                const tanggalMulai = document.querySelector('input[name="tanggal_mulai"]').value;
                const durasi = document.querySelector('select[name="durasi_bulan"]').value;

                if (!tanggalMulai || !durasi) {
                    e.preventDefault();
                    alert('Harap lengkapi semua field yang wajib diisi!');
                    return;
                }

                const konfirmasi = confirm('Apakah Anda yakin ingin memesan kos ini? Kos akan berstatus "Dipesan" dan tidak bisa dipesan orang lain.');
                if (!konfirmasi) {
                    e.preventDefault();
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>