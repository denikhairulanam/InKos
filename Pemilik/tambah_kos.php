<?php
include '../controler/pemilik/tambah_kos.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kos Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/pemilik/tambah_kos.css">
</head>

<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-plus-circle"></i> Tambah Kos Baru
                        </h3>
                        <a href="kos_saya.php" class="btn btn-light btn-sm float-end">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" id="formKos">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3"><i class="fas fa-info-circle"></i> Informasi Dasar</h5>

                                    <div class="mb-3">
                                        <label for="nama_kos" class="form-label required-label">Nama Kos</label>
                                        <input type="text" class="form-control" id="nama_kos" name="nama_kos"
                                            placeholder="Masukkan nama kos" required maxlength="100">
                                        <div class="form-text">Maksimal 100 karakter</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"
                                            placeholder="Deskripsikan fasilitas dan keunggulan kos"></textarea>
                                        <div class="form-text">Deskripsi detail tentang kos Anda</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="alamat" class="form-label required-label">Alamat Lengkap</label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="3"
                                            placeholder="Masukkan alamat lengkap kos" required></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="daerah_id" class="form-label">Lokasi Daerah</label>
                                        <select class="form-select" id="daerah_id" name="daerah_id" required>
                                            <option value="">Pilih Daerah</option>
                                            <?php
                                            $current_kota = '';
                                            foreach ($daerah_list as $daerah):
                                                if ($current_kota != $daerah['kota']):
                                                    $current_kota = $daerah['kota'];
                                            ?>
                                                    <optgroup label="<?php echo htmlspecialchars($daerah['kota']); ?>">
                                                    <?php endif; ?>
                                                    <option value="<?php echo htmlspecialchars($daerah['id']); ?>">
                                                        <?php echo htmlspecialchars($daerah['nama']); ?>
                                                    </option>
                                                    <?php
                                                    if (next($daerah_list) && $current_kota != next($daerah_list)['kota']):
                                                    ?>
                                                    </optgroup>
                                            <?php
                                                    endif;
                                                endforeach;
                                            ?>
                                        </select>
                                        <div class="form-text">Pilih daerah lokasi kos</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="harga_bulanan" class="form-label required-label">Harga Bulanan</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control currency-input" id="harga_bulanan"
                                                name="harga_bulanan"
                                                placeholder="100.000"
                                                required
                                                oninput="formatCurrency(this)"
                                                onblur="validateHarga(this)">
                                        </div>
                                        <div class="form-text">Harga sewa per bulan (minimal Rp 100.000)</div>
                                        <div id="harga-error" class="text-danger small mt-1"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3"><i class="fas fa-cogs"></i> Spesifikasi Kos</h5>

                                    <div class="mb-3">
                                        <label for="tipe_kos" class="form-label required-label">Tipe Kos</label>
                                        <select class="form-select" id="tipe_kos" name="tipe_kos" required>
                                            <option value="">Pilih Tipe Kos</option>
                                            <option value="putra">Putra</option>
                                            <option value="putri">Putri</option>
                                            <option value="campur">Campur</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="ukuran_kamar" class="form-label required-label">Ukuran Kamar</label>
                                        <input type="text" class="form-control" id="ukuran_kamar" name="ukuran_kamar"
                                            placeholder="Contoh: 3x4 meter, 4x5 meter" required maxlength="20">
                                        <div class="form-text">Contoh: 3x4, 4x5, 3x3.5</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="kamar_mandi" class="form-label required-label">Kamar Mandi</label>
                                        <select class="form-select" id="kamar_mandi" name="kamar_mandi" required>
                                            <option value="">Pilih Tipe Kamar Mandi</option>
                                            <option value="dalam">Dalam</option>
                                            <option value="luar">Luar</option>
                                        </select>
                                    </div>

                                    <!-- Fasilitas -->
                                    <div class="mb-3">
                                        <label class="form-label">Fasilitas</label>
                                        <div class="border rounded p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="fasilitas[]" value="wifi" id="wifi">
                                                        <label class="form-check-label" for="wifi">
                                                            <i class="fas fa-wifi text-primary"></i> WiFi
                                                        </label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="fasilitas[]" value="ac" id="ac">
                                                        <label class="form-check-label" for="ac">
                                                            <i class="fas fa-snowflake text-info"></i> AC
                                                        </label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="fasilitas[]" value="laundry" id="laundry">
                                                        <label class="form-check-label" for="laundry">
                                                            <i class="fas fa-tshirt text-success"></i> Laundry
                                                        </label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="fasilitas[]" value="kamar_mandi_dalam" id="kamar_mandi_dalam">
                                                        <label class="form-check-label" for="kamar_mandi_dalam">
                                                            <i class="fas fa-bath text-primary"></i> Kamar Mandi Dalam
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="fasilitas[]" value="parkir" id="parkir">
                                                        <label class="form-check-label" for="parkir">
                                                            <i class="fas fa-parking text-warning"></i> Parkir
                                                        </label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="fasilitas[]" value="dapur" id="dapur">
                                                        <label class="form-check-label" for="dapur">
                                                            <i class="fas fa-utensils text-danger"></i> Dapur
                                                        </label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="fasilitas[]" value="tv" id="tv">
                                                        <label class="form-check-label" for="tv">
                                                            <i class="fas fa-tv text-secondary"></i> TV
                                                        </label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="fasilitas[]" value="lemari" id="lemari">
                                                        <label class="form-check-label" for="lemari">
                                                            <i class="fas fa-archive text-info"></i> Lemari
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-text">Pilih fasilitas yang tersedia</div>
                                    </div>

                                    <!-- Upload Foto -->
                                    <div class="mb-3">
                                        <label for="foto_utama" class="form-label">Foto Utama</label>
                                        <input type="file" class="form-control" id="foto_utama" name="foto_utama"
                                            accept="image/*">
                                        <div class="form-text">Foto utama yang akan ditampilkan (jpg, jpeg, png, gif)</div>
                                        <div id="preview-utama" class="mt-2"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="foto_lainnya" class="form-label">Foto Lainnya</label>
                                        <input type="file" class="form-control" id="foto_lainnya" name="foto_lainnya[]"
                                            multiple accept="image/*">
                                        <div class="form-text">Pilih multiple foto tambahan (maksimal 5 foto)</div>
                                        <div id="preview-lainnya" class="mt-2 row"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="reset" class="btn btn-outline-secondary me-md-2" onclick="resetForm()">
                                            <i class="fas fa-undo"></i> Reset Form
                                        </button>
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-save"></i> Simpan Data Kos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Format currency function
        function formatCurrency(input) {
            // Hapus semua karakter non-digit
            let value = input.value.replace(/[^\d]/g, '');

            // Format ke Rupiah
            if (value) {
                input.value = new Intl.NumberFormat('id-ID').format(value);
            }

            // Validasi harga
            validateHarga(input);
        }

        // Validasi harga function
        function validateHarga(input) {
            const errorElement = document.getElementById('harga-error');
            const rawValue = input.value.replace(/[^\d]/g, '');
            const numericValue = parseInt(rawValue || 0);

            if (numericValue < 100000) {
                input.classList.add('is-invalid');
                errorElement.textContent = 'Harga minimal Rp 100.000';
            } else {
                input.classList.remove('is-invalid');
                errorElement.textContent = '';
            }
        }

        // Preview image untuk foto utama
        document.getElementById('foto_utama').addEventListener('change', function(e) {
            const preview = document.getElementById('preview-utama');
            preview.innerHTML = '';

            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-thumbnail preview-image';
                    preview.appendChild(img);
                }

                reader.readAsDataURL(this.files[0]);
            }
        });

        // Preview multiple images
        document.getElementById('foto_lainnya').addEventListener('change', function(e) {
            const preview = document.getElementById('preview-lainnya');
            preview.innerHTML = '';

            if (this.files) {
                // Limit to 5 files
                const files = Array.from(this.files).slice(0, 5);

                files.forEach((file, index) => {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-6 col-md-4 col-lg-3 mb-2';

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-thumbnail w-100';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';

                        col.appendChild(img);
                        preview.appendChild(col);
                    }

                    reader.readAsDataURL(file);
                });

                // Update file input if more than 5 files selected
                if (this.files.length > 5) {
                    const dt = new DataTransfer();
                    files.forEach(file => dt.items.add(file));
                    this.files = dt.files;
                    alert('Maksimal 5 foto yang dapat diupload. Foto lainnya akan diabaikan.');
                }
            }
        });

        // Form validation
        document.getElementById('formKos').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let valid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            // Validasi harga khusus
            const hargaInput = document.getElementById('harga_bulanan');
            const rawHarga = hargaInput.value.replace(/[^\d]/g, '');
            const numericHarga = parseInt(rawHarga || 0);

            if (numericHarga < 100000) {
                valid = false;
                hargaInput.classList.add('is-invalid');
                document.getElementById('harga-error').textContent = 'Harga minimal Rp 100.000';
            }

            if (!valid) {
                e.preventDefault();
                alert('Harap lengkapi semua field yang wajib diisi dengan benar!');
            }
        });

        // Real-time validation
        document.querySelectorAll('[required]').forEach(field => {
            if (field.id !== 'harga_bulanan') {
                field.addEventListener('blur', function() {
                    if (!this.value.trim()) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
            }
        });

        // Reset form function
        function resetForm() {
            document.getElementById('preview-utama').innerHTML = '';
            document.getElementById('preview-lainnya').innerHTML = '';
            document.getElementById('harga-error').textContent = '';
            document.querySelectorAll('.is-invalid').forEach(field => {
                field.classList.remove('is-invalid');
            });

            // Reset harga ke placeholder
            const hargaInput = document.getElementById('harga_bulanan');
            hargaInput.value = '';
        }

        // Set placeholder untuk harga saat load
        document.addEventListener('DOMContentLoaded', function() {
            const hargaInput = document.getElementById('harga_bulanan');
            hargaInput.placeholder = '100.000';
        });
    </script>
</body>

</html>