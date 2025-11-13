<?php
session_start();
include '../config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$pemesanan_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Query data pemesanan lengkap berdasarkan role user
$query = "SELECT p.*, k.nama_kos, k.alamat, k.foto_utama, k.deskripsi, k.fasilitas,
                 d.nama as nama_daerah,
                 u.nama as nama_pemilik, u.telepon as telepon_pemilik, u.email as email_pemilik,
                 uc.nama as nama_pencari, uc.telepon as telepon_pencari, uc.email as email_pencari,
                 pb.id as pembayaran_id, pb.status_pembayaran, pb.bukti_bayar,
                 pb.tanggal_bayar, pb.metode_pembayaran
          FROM pemesanan p 
          JOIN kos k ON p.kos_id = k.id 
          JOIN daerah d ON k.daerah_id = d.id
          JOIN users u ON p.pemilik_id = u.id
          JOIN users uc ON p.pencari_id = uc.id
          LEFT JOIN pembayaran pb ON p.id = pb.pemesanan_id
          WHERE p.id = ?";

// Tambahkan kondisi berdasarkan role
if ($user_role === 'pencari') {
    $query .= " AND p.pencari_id = ?";
    $param_types = "ii";
    $params = [$pemesanan_id, $user_id];
} elseif ($user_role === 'pemilik') {
    $query .= " AND p.pemilik_id = ?";
    $param_types = "ii";
    $params = [$pemesanan_id, $user_id];
} else {
    // Admin bisa melihat semua pemesanan
    $param_types = "i";
    $params = [$pemesanan_id];
}

$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Data pemesanan tidak ditemukan atau Anda tidak memiliki akses");
}

// Cek jika dompdf tersedia
$useDompdf = false;
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
    if (class_exists('Dompdf\Dompdf')) {
        $useDompdf = true;
    }
}

if ($useDompdf) {
    $options = new Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');

    $dompdf = new Dompdf\Dompdf($options);

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Invoice Pemesanan Kos - ' . $data['id'] . '</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 15px; 
                font-size: 12px;
            }
            .header { 
                text-align: center; 
                margin-bottom: 20px; 
                border-bottom: 2px solid #333; 
                padding-bottom: 10px; 
            }
            .horizontal-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-bottom: 15px;
            }
            .horizontal-table th, 
            .horizontal-table td { 
                padding: 6px 8px; 
                border: 1px solid #333; 
                text-align: left;
                vertical-align: top;
            }
            .horizontal-table th { 
                background: #f0f0f0; 
                font-weight: bold;
                width: 25%;
            }
            .horizontal-table td { 
                width: 25%;
            }
            .text-success { 
                color: #28a745; 
                font-weight: bold; 
            }
            .section-title {
                background: #333;
                color: white;
                padding: 8px;
                font-weight: bold;
                margin-top: 15px;
                margin-bottom: 8px;
            }
            .footer { 
                margin-top: 30px; 
                text-align: center; 
                border-top: 1px solid #ddd; 
                padding-top: 15px;
                font-size: 10px;
            }
            .signature-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 40px;
            }
            .signature-table td { 
                padding: 15px; 
                text-align: center; 
                vertical-align: bottom; 
                width: 50%;
            }
            .signature-line { 
                border-top: 1px solid #000; 
                width: 200px; 
                margin: 0 auto; 
                padding-top: 5px;
                margin-bottom: 5px;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h2 style="margin:0; font-size: 16px;">INVOICE PEMESANAN KOS</h2>
            <h3 style="margin:5px 0; font-size: 14px;">INKOS - Sistem Informasi Kos</h3>
            <p style="margin:5px 0; font-size: 11px;">No. Pemesanan: ' . $data['id'] . ' | Tanggal: ' . date('d/m/Y H:i', strtotime($data['tanggal_pemesanan'])) . '</p>
        </div>

        <div class="section-title">INFORMASI UMUM</div>
        <table class="horizontal-table">
            <tr>
                <th>No. Pemesanan</th>
                <td>' . $data['id'] . '</td>
                <th>Tanggal Pemesanan</th>
                <td>' . date('d/m/Y H:i', strtotime($data['tanggal_pemesanan'])) . '</td>
            </tr>
            <tr>
                <th>Status Pemesanan</th>
                <td>' . ucfirst($data['status']) . '</td>
                <th>Durasi Sewa</th>
                <td>' . $data['durasi_bulan'] . ' bulan</td>
            </tr>
            <tr>
                <th>Total Harga</th>
                <td class="text-success">Rp ' . number_format($data['total_harga'], 0, ',', '.') . '</td>
                <th>Periode Sewa</th>
                <td>' . date('d M Y', strtotime($data['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($data['tanggal_selesai'])) . '</td>
            </tr>
        </table>

        <div class="section-title">INFORMASI KOS</div>
        <table class="horizontal-table">
            <tr>
                <th>Nama Kos</th>
                <td>' . htmlspecialchars($data['nama_kos']) . '</td>
                <th>Daerah</th>
                <td>' . htmlspecialchars($data['nama_daerah']) . '</td>
            </tr>
            <tr>
                <th>Alamat Kos</th>
                <td colspan="3">' . htmlspecialchars($data['alamat']) . '</td>
            </tr>';

    if ($data['fasilitas']) {
        $html .= '
            <tr>
                <th>Fasilitas</th>
                <td colspan="3">' . nl2br(htmlspecialchars($data['fasilitas'])) . '</td>
            </tr>';
    }

    if ($data['deskripsi']) {
        $html .= '
            <tr>
                <th>Deskripsi</th>
                <td colspan="3">' . nl2br(htmlspecialchars($data['deskripsi'])) . '</td>
            </tr>';
    }

    $html .= '
        </table>

        <div class="section-title">INFORMASI PENYEWA</div>
        <table class="horizontal-table">
            <tr>
                <th>Nama Penyewa</th>
                <td>' . htmlspecialchars($data['nama_pencari']) . '</td>
                <th>Telepon</th>
                <td>' . $data['telepon_pencari'] . '</td>
            </tr>
            <tr>
                <th>Email</th>
                <td colspan="3">' . $data['email_pencari'] . '</td>
            </tr>
        </table>

        <div class="section-title">INFORMASI PEMILIK</div>
        <table class="horizontal-table">
            <tr>
                <th>Nama Pemilik</th>
                <td>' . htmlspecialchars($data['nama_pemilik']) . '</td>
                <th>Telepon</th>
                <td>' . $data['telepon_pemilik'] . '</td>
            </tr>
            <tr>
                <th>Email</th>
                <td colspan="3">' . $data['email_pemilik'] . '</td>
            </tr>
        </table>';

    if ($data['pembayaran_id']) {
        $html .= '
        <div class="section-title">INFORMASI PEMBAYARAN</div>
        <table class="horizontal-table">
            <tr>
                <th>Status Pembayaran</th>
                <td>' . ucfirst($data['status_pembayaran']) . '</td>
                <th>Metode Pembayaran</th>
                <td>' . ucfirst($data['metode_pembayaran'] ?? 'Transfer') . '</td>
            </tr>';

        if ($data['tanggal_bayar']) {
            $html .= '
            <tr>
                <th>Tanggal Pembayaran</th>
                <td colspan="3">' . date('d M Y H:i', strtotime($data['tanggal_bayar'])) . '</td>
            </tr>';
        }

        $html .= '
        </table>';
    }

    $html .= '
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-line"></div>
                    <p><strong>Penyewa</strong></p>
                    <p>(' . htmlspecialchars($data['nama_pencari']) . ')</p>
                </td>
                <td>
                    <div class="signature-line"></div>
                    <p><strong>Pemilik Kos</strong></p>
                    <p>(' . htmlspecialchars($data['nama_pemilik']) . ')</p>
                </td>
            </tr>
        </table>

        <div class="footer">
            <p><strong>INKOS - Sistem Informasi Kos</strong></p>
            <p>Invoice ini sah dan dapat digunakan sebagai bukti pemesanan</p>
            <p>Dicetak pada: ' . date('d/m/Y H:i') . ' oleh ' . htmlspecialchars($_SESSION['user_name'] ?? 'User') . ' (' . ucfirst($user_role) . ')</p>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Langsung download tanpa preview
    $dompdf->stream("invoice_pemesanan_kos_{$data['id']}.pdf", array("Attachment" => true));
    exit;
} else {
    // Fallback: Download sebagai file HTML jika dompdf tidak tersedia
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="invoice_pemesanan_kos_' . $data['id'] . '.html"');

    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Invoice Pemesanan Kos - ' . $data['id'] . '</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 15px; 
                font-size: 12px;
            }
            .header { 
                text-align: center; 
                margin-bottom: 20px; 
                border-bottom: 2px solid #333; 
                padding-bottom: 10px; 
            }
            .horizontal-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-bottom: 15px;
            }
            .horizontal-table th, 
            .horizontal-table td { 
                padding: 6px 8px; 
                border: 1px solid #333; 
                text-align: left;
                vertical-align: top;
            }
            .horizontal-table th { 
                background: #f0f0f0; 
                font-weight: bold;
                width: 25%;
            }
            .horizontal-table td { 
                width: 25%;
            }
            .text-success { 
                color: #28a745; 
                font-weight: bold; 
            }
            .section-title {
                background: #333;
                color: white;
                padding: 8px;
                font-weight: bold;
                margin-top: 15px;
                margin-bottom: 8px;
            }
            .footer { 
                margin-top: 30px; 
                text-align: center; 
                border-top: 1px solid #ddd; 
                padding-top: 15px;
                font-size: 10px;
            }
            .signature-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 40px;
            }
            .signature-table td { 
                padding: 15px; 
                text-align: center; 
                vertical-align: bottom; 
                width: 50%;
            }
            .signature-line { 
                border-top: 1px solid #000; 
                width: 200px; 
                margin: 0 auto; 
                padding-top: 5px;
                margin-bottom: 5px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h2 style="margin:0; font-size: 16px;">INVOICE PEMESANAN KOS</h2>
            <h3 style="margin:5px 0; font-size: 14px;">INKOS - Sistem Informasi Kos</h3>
            <p style="margin:5px 0; font-size: 11px;">No. Pemesanan: ' . $data['id'] . ' | Tanggal: ' . date('d/m/Y H:i', strtotime($data['tanggal_pemesanan'])) . '</p>
        </div>

        <div class="section-title">INFORMASI UMUM</div>
        <table class="horizontal-table">
            <tr>
                <th>No. Pemesanan</th>
                <td>' . $data['id'] . '</td>
                <th>Tanggal Pemesanan</th>
                <td>' . date('d/m/Y H:i', strtotime($data['tanggal_pemesanan'])) . '</td>
            </tr>
            <tr>
                <th>Status Pemesanan</th>
                <td>' . ucfirst($data['status']) . '</td>
                <th>Durasi Sewa</th>
                <td>' . $data['durasi_bulan'] . ' bulan</td>
            </tr>
            <tr>
                <th>Total Harga</th>
                <td class="text-success">Rp ' . number_format($data['total_harga'], 0, ',', '.') . '</td>
                <th>Periode Sewa</th>
                <td>' . date('d M Y', strtotime($data['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($data['tanggal_selesai'])) . '</td>
            </tr>
        </table>

        <div class="section-title">INFORMASI KOS</div>
        <table class="horizontal-table">
            <tr>
                <th>Nama Kos</th>
                <td>' . htmlspecialchars($data['nama_kos']) . '</td>
                <th>Daerah</th>
                <td>' . htmlspecialchars($data['nama_daerah']) . '</td>
            </tr>
            <tr>
                <th>Alamat Kos</th>
                <td colspan="3">' . htmlspecialchars($data['alamat']) . '</td>
            </tr>';

    if ($data['fasilitas']) {
        echo '
            <tr>
                <th>Fasilitas</th>
                <td colspan="3">' . nl2br(htmlspecialchars($data['fasilitas'])) . '</td>
            </tr>';
    }

    if ($data['deskripsi']) {
        echo '
            <tr>
                <th>Deskripsi</th>
                <td colspan="3">' . nl2br(htmlspecialchars($data['deskripsi'])) . '</td>
            </tr>';
    }

    echo '
        </table>

        <div class="section-title">INFORMASI PENYEWA</div>
        <table class="horizontal-table">
            <tr>
                <th>Nama Penyewa</th>
                <td>' . htmlspecialchars($data['nama_pencari']) . '</td>
                <th>Telepon</th>
                <td>' . $data['telepon_pencari'] . '</td>
            </tr>
            <tr>
                <th>Email</th>
                <td colspan="3">' . $data['email_pencari'] . '</td>
            </tr>
        </table>

        <div class="section-title">INFORMASI PEMILIK</div>
        <table class="horizontal-table">
            <tr>
                <th>Nama Pemilik</th>
                <td>' . htmlspecialchars($data['nama_pemilik']) . '</td>
                <th>Telepon</th>
                <td>' . $data['telepon_pemilik'] . '</td>
            </tr>
            <tr>
                <th>Email</th>
                <td colspan="3">' . $data['email_pemilik'] . '</td>
            </tr>
        </table>';

    if ($data['pembayaran_id']) {
        echo '
        <div class="section-title">INFORMASI PEMBAYARAN</div>
        <table class="horizontal-table">
            <tr>
                <th>Status Pembayaran</th>
                <td>' . ucfirst($data['status_pembayaran']) . '</td>
                <th>Metode Pembayaran</th>
                <td>' . ucfirst($data['metode_pembayaran'] ?? 'Transfer') . '</td>
            </tr>';

        if ($data['tanggal_bayar']) {
            echo '
            <tr>
                <th>Tanggal Pembayaran</th>
                <td colspan="3">' . date('d M Y H:i', strtotime($data['tanggal_bayar'])) . '</td>
            </tr>';
        }

        echo '
        </table>';
    }

    echo '
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-line"></div>
                    <p><strong>Penyewa</strong></p>
                    <p>(' . htmlspecialchars($data['nama_pencari']) . ')</p>
                </td>
                <td>
                    <div class="signature-line"></div>
                    <p><strong>Pemilik Kos</strong></p>
                    <p>(' . htmlspecialchars($data['nama_pemilik']) . ')</p>
                </td>
            </tr>
        </table>

        <div class="footer">
            <p><strong>INKOS - Sistem Informasi Kos</strong></p>
            <p>Invoice ini sah dan dapat digunakan sebagai bukti pemesanan</p>
            <p>Dicetak pada: ' . date('d/m/Y H:i') . ' oleh ' . htmlspecialchars($_SESSION['user_name'] ?? 'User') . ' (' . ucfirst($user_role) . ')</p>
        </div>
    </body>
    </html>';
    exit;
}
