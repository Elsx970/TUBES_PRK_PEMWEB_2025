Anggota Kelompok

Najlaa' Nafisha Aulia	2355061001

Iin Sumarni	2315061040

Putri Naiya Ramadhani	2315061025

Dyvta Avryansyah	2315061128

# CareU – Sistem Donasi dan Pengelolaan Kegiatan Sosial Mahasiswa

CareU adalah sebuah platform berbasis web yang dirancang untuk mendukung kegiatan sosial mahasiswa, seperti penggalangan dana, dokumentasi kegiatan, dan distribusi donasi. Sistem ini memungkinkan mahasiswa maupun pihak terkait untuk melakukan kontribusi secara transparan, mudah, dan terorganisir.

---

## ✨ Fitur Utama

### 1. Manajemen Campaign
- Membuat dan mengelola campaign sosial
- Menampilkan detail campaign, tujuan, dan perkembangan
- Menyediakan informasi donasi yang masuk

### 2. Sistem Donasi Online
- Formulir donasi yang sederhana
- Riwayat donasi yang dapat diakses pengguna
- Penyimpanan data donasi secara terstruktur

### 3. Autentikasi Pengguna
- Registrasi akun
- Login dan logout
- Sistem keamanan berbasis session

### 4. Dashboard Admin
- Kelola campaign
- Kelola data donatur
- Mengontrol aktivitas sistem secara keseluruhan

### 5. API Endpoint
- Mendukung integrasi dengan halaman lain atau aplikasi tambahan
- Mengakses data campaign dan donasi melalui API internal

---

## ERD
<img width="941" height="824" alt="erdtubes drawio 1" src="https://github.com/user-attachments/assets/fc26b026-a128-422e-9e59-f6ce0ec47b51" />


## 📁 Struktur Direktori

<pre>
├── admin/               # Halaman admin dan kelola data
├── api/                 # Endpoint API internal
├── database/            # Skrip dan struktur database
├── js/                  # File JavaScript tambahan
├── auth.php             # Logic autentikasi
├── campaign_detail.php  # Halaman detail kampanye
├── config.php           # Koneksi dan konfigurasi database
├── donation_history.php # Riwayat donasi
├── index.php            # Halaman utama
├── login.php            # Halaman login
├── logout.php           # Proses logout
├── register.php         # Halaman registrasi
└── README.md            # Dokumentasi proyek
</pre>

---

## 🛠️ Teknologi yang Digunakan
- PHP Native  
- MySQL 
- HTML & CSS  
- JavaScript  
- GitHub (Version Control)

---

## 🚀 Cara Menjalankan Proyek

### 1. Clone Repository
```bash
git clone https://github.com/your-repo-link/careu.git

Berikut adalah kelanjutan file `README.md` dalam format **Markdown** yang benar:

````markdown
## 🚀 Cara Menjalankan Proyek

### 1. Clone Repository
```bash
git clone https://github.com/Elsx970/TUBES_PRK_PEMWEB_2025.git
````

### 2. Siapkan Database

Masuk ke folder `database` di dalam repository yang sudah di-clone. Anda akan menemukan file SQL 

* Buka klien database MySQL atau MariaDB Anda 
* Buat database baru careu_db.
* Impor file SQL dari folder `database` untuk membuat tabel dan struktur yang diperlukan.
  

### 3. Konfigurasi Koneksi Database

* Buka folder proyek dan temukan file konfigurasi seperti config.php.
* Perbarui pengaturan koneksi database dengan kredensial database lokal Anda.

Contoh konfigurasi:

```php
<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'careu_db');

?>
```

### 4. Jalankan Web Server

* Jika Anda menggunakan XAMPP, WAMP, atau Laragon, buka control panel dan mulai layanan Apache dan MySQL.
* Jika menggunakan server web kustom, pastikan server menunjuk ke direktori proyek Anda.

### 5. Akses Aplikasi Web

Buka browser dan akses URL berikut:

```
http://localhost/kelompok/kelompok_07/
```

Anda akan melihat halaman utama aplikasi.

## Screenshoot

### Tampilan Akun User

<img width="1904" height="916" alt="gambar" src="https://github.com/user-attachments/assets/5ff326a3-371e-4b45-afe1-936cd738d0f5" />

<img width="1906" height="916" alt="gambar" src="https://github.com/user-attachments/assets/417b8514-70fe-4ba2-9d12-bb364851c056" />

<img width="1894" height="852" alt="gambar" src="https://github.com/user-attachments/assets/f3c9aa0c-359b-45a9-99b8-841c35517c5e" />

### Tampilan Akun Admin

<img width="1900" height="916" alt="gambar" src="https://github.com/user-attachments/assets/f85d98ac-7cbd-40b5-9cc9-af0cb4a1e231" />

<img width="1890" height="910" alt="gambar" src="https://github.com/user-attachments/assets/b858d3f4-a6bf-4f12-8c4d-84ba017b9d2a" />

<img width="1897" height="909" alt="gambar" src="https://github.com/user-attachments/assets/f78eb786-eb5c-458a-9fd6-df5f50afde3b" />

<img width="1896" height="906" alt="gambar" src="https://github.com/user-attachments/assets/df92ad64-d7d9-48dd-8938-7f7f400cc78b" />

<img width="1890" height="909" alt="gambar" src="https://github.com/user-attachments/assets/3833a475-21fe-48d8-871e-52c614cef0b9" />

<img width="1890" height="906" alt="gambar" src="https://github.com/user-attachments/assets/b64558b6-fdd2-4d9a-8980-f364c7461c2b" />









