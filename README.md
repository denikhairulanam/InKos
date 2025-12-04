# 🚀 Final Project RPL — Sistem InKos

<p align="center">
  <img src="https://img.shields.io/badge/InKos-Platform%20Pencarian%20Kos-blue?style=for-the-badge&logo=homeassistantcommunity" />
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql" />
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap" />
</p>

## 👥 Identitas Kelompok
- **Nama Kelompok :** Kelompok 10
- **Anggota & Jobdesk :**

| Nama Anggota       | Tugas / Jobdesk            |
|--------------------|----------------------------|
| Aldi Darmawan      | Membuat dokumen srs bab 1235(activity diagram), squence, membuat ppt, hosting, memperbaiki bug pada menu dashboard pemilik  |
| Surah Annisa       | Membuat readme, mendemokan web, merevisi dokumen srs |
| Deni Khairul Anam  | Membuat web,  bab 4 ,push github & membuat repository/maintener, ui desain |

## 📱 Deskripsi Singkat Proyek
Sistem ini dibuat berdasarkan permintaan dari klien Kelompok 4 dengan tujuan untuk menyelesaikan permasalahan:
- Tujuan proyek ini adalah untuk menyediakan sistem berbasis web yang mempermudah pengguna dalam mencari, memesan, dan membayar kost secara online, sekaligus membantu pemilik kost mengelola data kamar, penghuni, dan pembayaran dengan lebih cepat, aman, dan terstruktur tanpa harus melakukan proses manual atau survei langsung.

Solusi yang dikembangkan berupa aplikasi:
- Solusi yang dikembangkan berupa Aplikasi Web yang memungkinkan pengguna untuk mencari, memesan, dan mengelola data kost secara online melalui browser tanpa perlu instalasi tambahan.
- Aplikasi ini menyediakan fitur login, pencarian kost, input dan pengelolaan data kamar, pemesanan kamar, pembayaran online, serta pembuatan laporan.

## 🛠 Teknologi yang Digunakan
**Backend**
1. PHP 8.2+ - Bahasa pemrograman utama
2. MySQL - Database management system
3. PDO - Database connection dengan prepared statements
4. Sessions - Manajemen autentikasi

**Frontend**
1. Bootstrap 5.3 
2. Custom CSS - Styling tambahan

## 🚀 Cara Menjalankan Aplikasi
### Cara Instalasi
1. Clone repository:
bash
git clone https://github.com/denikhairulanam/InKos.git

2. Pindah ke direktori project:

bash
cd InKos

3. Pastikan XAMPP/Laragon sudah terinstal

*Cara Konfigurasi*
1. Import database:
- Buka phpMyAdmin (`http://localhost/phpmyadmin`)
- Buat database baru bernama `inkos`
- Import file `inkos.sql` yang ada di folder project
2. Konfigurasi koneksi database:
- Buka file `config.php`
- Sesuaikan konfigurasi database:

define('DB_HOST', 'localhost');
define('DB_NAME', 'inkos');
define('DB_USER', 'root');
define('DB_PASS', '');

*🔑 Akun Demo*
*Admin:*
- Username: `admin@gmail.com`
- Password: `123456`

*Pemilik:*
- Username: `pemilik@gmail.com`
- Password: `123456`

*Pencari:*
- Username: `pencari@gmail.com`
- Password: `123456`

*🌐 Link Deployment*
- *Website InKos:* https://inkost.kesug.com

## 🏠 Home Page
![Home Page](https://raw.githubusercontent.com/denikhairulanam/InKos/main/FotoDemo/Screenshot%202025-12-01%20205554.png)

## 🛠️ Admin Dashboard
![Demo](https://raw.githubusercontent.com/denikhairulanam/InKos/main/FotoDemo/Screenshot%202025-12-01%20205813.png)


© 2025 InKos - Kelompok 10 RPL
