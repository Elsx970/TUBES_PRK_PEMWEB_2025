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
