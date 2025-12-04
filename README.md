# 🚀 Final Project RPL — Sistem InKos

<div align="center">

![InKos](https://img.shields.io/badge/InKos-Platform%20Pencarian%20Kos-blue?style=for-the-badge&logo=homeassistantcommunity)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap)

**Solusi Terpadu untuk Pencarian, Pemesanan, dan Pengelolaan Kost Online**

</div>

---

## 👥 Identitas Kelompok

**Kelompok 10 - Program RPL**
| Nama Anggota          | Tugas / Jobdesk                                                                                                                                |
| :-------------------- | :--------------------------------------------------------------------------------------------------------------------------------------------- |
| **Deni Khairul Anam** | • Membangun web <br>•Membuat Bab 4<br>• Melakukan push ke GitHub, membuat repository, dan mengatur peran maintainer<br>• Membuat desain antarmuka (UI Design) |
| **Aldi Darmawan**     | • Menyusun dokumen SRS Bab 1, 2, 3, dan 5<br>• Membuat Activity Diagram dan Sequence Diagram<br>• Membuat slide presentasi (PPT)<br>• Melakukan hosting aplikasi |
| **Surah Annisa**      | • Menyusun berkas README pada repository<br>• Mendemokan video terkait proyek<br>• Melakukan revisi pada dokumen SRS |


---

## 📱 Deskripsi Singkat Proyek

### Latar Belakang Masalah

Sistem ini dibuat berdasarkan permintaan klien untuk menyelesaikan permasalahan dalam pencarian dan pengelolaan kost yang masih dilakukan secara manual.

### Tujuan Proyek

Menyediakan sistem berbasis web yang mempermudah:

- 🔍 Pengguna mencari dan memesan kost secara online
- 💳 Proses pembayaran yang aman dan terstruktur
- 📊 Pemilik mengelola data kamar, penghuni, dan pembayaran dengan efisien

### Solusi yang Dikembangkan

✅ **Aplikasi Web Responsive** yang memungkinkan pengguna untuk:

- Mencari kost berdasarkan berbagai filter
- Melakukan pemesanan kamar secara online
- Melakukan pembayaran melalui sistem yang terintegrasi
- Mengelola data secara real-time tanpa perlu instalasi tambahan

### Fitur Utama

- 🔐 Sistem login & autentikasi teraman
- 🏘️ Pencarian dan filtering kost
- 📝 Pemesanan dan konfirmasi booking
- 💰 Sistem pembayaran online terintegrasi
- 📋 Laporan dan manajemen data
- 👤 Manajemen profil pengguna

---

## 🛠 Teknologi yang Digunakan

### Backend

- **PHP 8.1+** — Bahasa pemrograman utama
- **MySQL** — Database management system
- **PDO** — Database connection dengan prepared statements
- **Sessions** — Manajemen autentikasi & user session

### Frontend

- **Bootstrap 5.3**
- **Custom CSS** 
- **JavaScript** 

### Tools & Deployment

---
- **Laragon** — Local development environment
---

## 🚀 Panduan Instalasi & Konfigurasi

### Prasyarat

- XAMPP atau Laragon sudah terinstal
- PHP 8.1+
- MySQL 5.7+


### Langkah 1: Clone Repository

```bash
git clone https://github.com/denikhairulanam/InKos.git
cd InKos
```

### Langkah 2: Setup Database

1. Buka **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Buat database baru bernama `inkos`
3. Import file `inkos.sql` dari folder project
   - Pilih database `inkos`
   - Klik tab "Import"
   - Pilih file `inkos.sql` → Klik "Go"

### Langkah 3: Konfigurasi Koneksi Database

Buka file `config.php` dan sesuaikan konfigurasi:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'inkos');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Langkah 4: Jalankan Aplikasi

1. Pindahkan folder ke direktori web server (htdocs untuk XAMPP)
2. Akses via browser: `http://localhost/InKos`

---

## 🔑 Akun Demo

### Admin Account

```
Email: admin@gmail.com
Password: 123456
```

Akses untuk mengelola sistem secara keseluruhan

### Pemilik Account

```
Email: pemilik@gmail.com
Password: 123456
```

Akses untuk mengelola data kost dan pemesanan

### Pencari Account

```
Email: pencari@gmail.com
Password: 123456
```

Akses untuk mencari dan memesan kost

---

## 🌐 Link Live Demo

🔗 **Website InKos:** https://inkost.kesug.com

---

## 📸 Screenshots

### Home Page

![Home Page](https://raw.githubusercontent.com/denikhairulanam/InKos/main/FotoDemo/Screenshot%202025-12-01%20205554.png)

### Admin Dashboard

![Admin Dashboard](https://raw.githubusercontent.com/denikhairulanam/InKos/main/FotoDemo/Screenshot%202025-12-01%20205813.png)

---

## 📄 Dokumentasi Lengkap

- 📖 [SRS (Software Requirement Specification)](https://github.com/denikhairulanam/InKos/blob/main/documen/DokumenSRS_Kelompok10.pdf)
- 🎨 [UI/UX Design Prototype](https://github.com/denikhairulanam/InKos/blob/main/documen/InKos%20(2).png)
- 🎬 [Demo Video (YouTube)](https://youtu.be/NWGybUuSts8?feature=shared)
---


## 📚 Keterangan Tugas

Project ini dibuat untuk memenuhi **Tugas Final Project** Mata Kuliah Rekayasa Perangkat Lunak

### Dosen Pengampu
- **Nama:** Dila Nurlaila, M.Kom.
- **Mata Kuliah:** Rekayasa Perangkat Lunak
- **Program Studi:** Sistem Informasi
- **Universitas:** UIN STS Jambi

---

<div align="center">

### © 2025 InKos - Kelompok 10 RPL

**Semua hak cipta dilindungi**

Made with ❤️ by **Kelompok 10**

</div>
