<?php
session_start();
require_once '../config.php';

// Init Database
$db = new Database();
$conn = $db->getConnection();

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$pemesanan_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Query data pemesanan
$query = "SELECT p.*, k.nama_kos, k.alamat, 
                 u.nama as nama_pemilik, u.telepon as telepon_pemilik,
                 uc.nama as nama_pencari, uc.telepon as telepon_pencari,
                 pb.status_pembayaran, pb.tanggal_bayar, pb.metode_pembayaran
          FROM pemesanan p 
          JOIN kos k ON p.kos_id = k.id 
          JOIN users u ON p.pemilik_id = u.id
          JOIN users uc ON p.pencari_id = uc.id
          LEFT JOIN pembayaran pb ON p.id = pb.pemesanan_id
          WHERE p.id = ?";

// Filter berdasarkan role user
$params = [$pemesanan_id];
if ($user_role === 'pencari') {
    $query .= " AND p.pencari_id = ?";
    $params[] = $user_id;
} elseif ($user_role === 'pemilik') {
    $query .= " AND p.pemilik_id = ?";
    $params[] = $user_id;
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$data = $stmt->fetch();

if (!$data) {
    die("Data tidak ditemukan atau akses ditolak");
}

// Format data
$invoice_data = [
    'no_invoice' => 'INV-' . $data['id'] . '-' . date('Ym'),
    'no_pemesanan' => $data['id'],
    'tanggal_pemesanan' => date('d/m/Y H:i', strtotime($data['tanggal_pemesanan'])),
    'status_pemesanan' => ucfirst($data['status']),
    'status_pembayaran' => ucfirst($data['status_pembayaran'] ?? 'Belum Bayar'),
    'metode_bayar' => ucfirst($data['metode_pembayaran'] ?? '-'),
    'tanggal_bayar' => $data['tanggal_bayar'] ? date('d/m/Y H:i', strtotime($data['tanggal_bayar'])) : '-',

    'nama_kos' => htmlspecialchars($data['nama_kos']),
    'alamat_kos' => htmlspecialchars($data['alamat']),
    'periode_sewa' => date('d M Y', strtotime($data['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($data['tanggal_selesai'])),
    'durasi' => $data['durasi_bulan'] . ' bulan',
    'total_harga' => 'Rp ' . number_format($data['total_harga'], 0, ',', '.'),

    'nama_penyewa' => htmlspecialchars($data['nama_pencari']),
    'telepon_penyewa' => $data['telepon_pencari'],

    'nama_pemilik' => htmlspecialchars($data['nama_pemilik']),
    'telepon_pemilik' => $data['telepon_pemilik'],

    'dicetak_oleh' => htmlspecialchars($_SESSION['user_name'] ?? 'User'),
    'role_pencetak' => ucfirst($user_role),
    'tanggal_cetak' => date('d/m/Y H:i')
];

// Cek dompdf dengan error handling
$useDompdf = false;
$dompdf_error = '';

try {
    if (file_exists('../vendor/autoload.php')) {
        require_once '../vendor/autoload.php';
        if (class_exists('Dompdf\Dompdf')) {
            $useDompdf = true;
        }
    }
} catch (Exception $e) {
    $dompdf_error = $e->getMessage();
    $useDompdf = false;
}

if ($useDompdf) {
    try {
        // Generate PDF dengan dompdf
        $options = new Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', false); // Disable remote for stability

        $dompdf = new Dompdf\Dompdf($options);

        $html = generateInvoiceHTML($invoice_data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output PDF
        $dompdf->stream("invoice_" . $invoice_data['no_invoice'] . ".pdf", [
            "Attachment" => true,
            "compress" => true
        ]);
        exit;
    } catch (Exception $e) {
        // Fallback ke HTML jika PDF gagal
        $useDompdf = false;
        error_log("DOMPDF Error: " . $e->getMessage());
    }
}

// Jika dompdf tidak tersedia atau error, gunakan HTML
downloadHTMLInvoice($invoice_data);

function generateInvoiceHTML($data)
{
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Invoice - ' . $data['no_invoice'] . '</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 15px; 
                font-size: 12px;
                line-height: 1.4;
            }
            .header { 
                text-align: center; 
                margin-bottom: 20px; 
                border-bottom: 2px solid #333; 
                padding-bottom: 10px; 
            }
            .company-name {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .invoice-title {
                font-size: 14px;
                margin: 8px 0;
            }
            .info-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-bottom: 15px;
            }
            .info-table td { 
                padding: 6px 8px;
                vertical-align: top;
                border: 1px solid #ddd;
            }
            .info-table .label {
                font-weight: bold;
                width: 30%;
                background: #f9f9f9;
            }
            .section {
                margin-bottom: 15px;
            }
            .section-title {
                background: #333;
                color: white;
                padding: 6px;
                font-weight: bold;
                margin-bottom: 8px;
                font-size: 11px;
            }
            .total-box {
                background: #f8f9fa;
                border: 1px solid #ddd;
                padding: 12px;
                text-align: center;
                margin: 15px 0;
            }
            .total-amount {
                font-size: 14px;
                font-weight: bold;
                color: #28a745;
            }
            .footer { 
                margin-top: 30px; 
                text-align: center; 
                border-top: 1px solid #ddd; 
                padding-top: 10px;
                font-size: 9px;
                color: #666;
            }
            .signature {
                margin-top: 40px;
            }
            .signature-box {
                display: inline-block;
                width: 40%;
                text-align: center;
                margin: 0 5%;
            }
            .signature-line {
                border-top: 1px solid #000;
                width: 150px;
                margin: 25px auto 5px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="company-name">INKOS</div>
            <div class="invoice-title">INVOICE PEMESANAN KOS</div>
            <div>No. Invoice: ' . $data['no_invoice'] . '</div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">No. Pemesanan</td>
                <td>' . $data['no_pemesanan'] . '</td>
                <td class="label">Tanggal Pemesanan</td>
                <td>' . $data['tanggal_pemesanan'] . '</td>
            </tr>
            <tr>
                <td class="label">Status Pemesanan</td>
                <td>' . $data['status_pemesanan'] . '</td>
                <td class="label">Status Pembayaran</td>
                <td>' . $data['status_pembayaran'] . '</td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">INFORMASI KOS</div>
            <table class="info-table">
                <tr>
                    <td class="label">Nama Kos</td>
                    <td colspan="3">' . $data['nama_kos'] . '</td>
                </tr>
                <tr>
                    <td class="label">Alamat</td>
                    <td colspan="3">' . $data['alamat_kos'] . '</td>
                </tr>
                <tr>
                    <td class="label">Periode Sewa</td>
                    <td colspan="3">' . $data['periode_sewa'] . ' (' . $data['durasi'] . ')</td>
                </tr>
            </table>
        </div>

        <div class="total-box">
            <div><strong>TOTAL HARGA SEWA</strong></div>
            <div class="total-amount">' . $data['total_harga'] . '</div>
        </div>

        <div class="section">
            <div class="section-title">INFORMASI PENYEWA</div>
            <table class="info-table">
                <tr>
                    <td class="label">Nama</td>
                    <td>' . $data['nama_penyewa'] . '</td>
                    <td class="label">Telepon</td>
                    <td>' . $data['telepon_penyewa'] . '</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">INFORMASI PEMILIK</div>
            <table class="info-table">
                <tr>
                    <td class="label">Nama</td>
                    <td>' . $data['nama_pemilik'] . '</td>
                    <td class="label">Telepon</td>
                    <td>' . $data['telepon_pemilik'] . '</td>
                </tr>
            </table>
        </div>';

    if ($data['tanggal_bayar'] != '-') {
        $html .= '
        <div class="section">
            <div class="section-title">INFORMASI PEMBAYARAN</div>
            <table class="info-table">
                <tr>
                    <td class="label">Metode Pembayaran</td>
                    <td>' . $data['metode_bayar'] . '</td>
                    <td class="label">Tanggal Pembayaran</td>
                    <td>' . $data['tanggal_bayar'] . '</td>
                </tr>
            </table>
        </div>';
    }

    $html .= '
        <div class="signature">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Penyewa</div>
                <div>(' . $data['nama_penyewa'] . ')</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Pemilik Kos</div>
                <div>(' . $data['nama_pemilik'] . ')</div>
            </div>
        </div>

        <div class="footer">
            <div><strong>INKOS - Sistem Informasi Kos</strong></div>
            <div>Invoice ini sah dan dapat digunakan sebagai bukti pemesanan</div>
            <div>Dicetak oleh ' . $data['dicetak_oleh'] . ' (' . $data['role_pencetak'] . ') pada ' . $data['tanggal_cetak'] . '</div>
        </div>
    </body>
    </html>';
}

function downloadHTMLInvoice($data)
{
    // Set headers untuk download HTML
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="invoice_' . $data['no_invoice'] . '.html"');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice - ' . $data['no_invoice'] . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 14px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #333; }
        .company-name { font-size: 18px; font-weight: bold; }
        .invoice-title { font-size: 16px; margin: 10px 0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .info-table td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }
        .label { font-weight: bold; background: #f9f9f9; width: 30%; }
        .section { margin: 15px 0; }
        .section-title { background: #333; color: white; padding: 8px; font-weight: bold; margin-bottom: 8px; }
        .total-box { background: #f8f9fa; border: 1px solid #ddd; padding: 15px; text-align: center; margin: 20px 0; }
        .total-amount { font-size: 18px; font-weight: bold; color: #28a745; }
        .footer { margin-top: 40px; text-align: center; padding-top: 15px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
        .signature { margin-top: 50px; text-align: center; }
        .signature-box { display: inline-block; margin: 0 40px; }
        .signature-line { border-top: 1px solid #000; width: 200px; margin: 30px auto 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">INKOS</div>
        <div class="invoice-title">INVOICE PEMESANAN KOS</div>
        <div>No. Invoice: ' . $data['no_invoice'] . '</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">No. Pemesanan</td>
            <td>' . $data['no_pemesanan'] . '</td>
            <td class="label">Tanggal Pemesanan</td>
            <td>' . $data['tanggal_pemesanan'] . '</td>
        </tr>
        <tr>
            <td class="label">Status Pemesanan</td>
            <td>' . $data['status_pemesanan'] . '</td>
            <td class="label">Status Pembayaran</td>
            <td>' . $data['status_pembayaran'] . '</td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">INFORMASI KOS</div>
        <table class="info-table">
            <tr>
                <td class="label">Nama Kos</td>
                <td colspan="3">' . $data['nama_kos'] . '</td>
            </tr>
            <tr>
                <td class="label">Alamat</td>
                <td colspan="3">' . $data['alamat_kos'] . '</td>
            </tr>
            <tr>
                <td class="label">Periode Sewa</td>
                <td colspan="3">' . $data['periode_sewa'] . ' (' . $data['durasi'] . ')</td>
            </tr>
        </table>
    </div>

    <div class="total-box">
        <div><strong>TOTAL HARGA SEWA</strong></div>
        <div class="total-amount">' . $data['total_harga'] . '</div>
    </div>

    <div class="section">
        <div class="section-title">INFORMASI PENYEWA</div>
        <table class="info-table">
            <tr>
                <td class="label">Nama</td>
                <td>' . $data['nama_penyewa'] . '</td>
                <td class="label">Telepon</td>
                <td>' . $data['telepon_penyewa'] . '</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">INFORMASI PEMILIK</div>
        <table class="info-table">
            <tr>
                <td class="label">Nama</td>
                <td>' . $data['nama_pemilik'] . '</td>
                <td class="label">Telepon</td>
                <td>' . $data['telepon_pemilik'] . '</td>
            </tr>
        </table>
    </div>';

    if ($data['tanggal_bayar'] != '-') {
        echo '
    <div class="section">
        <div class="section-title">INFORMASI PEMBAYARAN</div>
        <table class="info-table">
            <tr>
                <td class="label">Metode Pembayaran</td>
                <td>' . $data['metode_bayar'] . '</td>
                <td class="label">Tanggal Pembayaran</td>
                <td>' . $data['tanggal_bayar'] . '</td>
            </tr>
        </table>
    </div>';
    }

    echo '
    <div class="signature">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Penyewa</div>
            <div>(' . $data['nama_penyewa'] . ')</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Pemilik Kos</div>
            <div>(' . $data['nama_pemilik'] . ')</div>
        </div>
    </div>

    <div class="footer">
        <div><strong>INKOS - Sistem Informasi Kos</strong></div>
        <div>Invoice ini sah dan dapat digunakan sebagai bukti pemesanan</div>
        <div>Dicetak oleh ' . $data['dicetak_oleh'] . ' (' . $data['role_pencetak'] . ') pada ' . $data['tanggal_cetak'] . '</div>
    </div>
</body>
</html>';

    exit;
}
