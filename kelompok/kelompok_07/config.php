<?php
session_start();

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'careu_db');

// Koneksi Database
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            // Jika database belum ada, buat database
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
            $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $conn->select_db(DB_NAME);
            
            // Buat tabel users
            $createUsers = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(255) NOT NULL,
                role ENUM('admin', 'user') DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $conn->query($createUsers);
            
            // Buat tabel campaigns
            $createCampaigns = "CREATE TABLE IF NOT EXISTS campaigns (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                background TEXT,
                target_amount DECIMAL(15,2) NOT NULL,
                current_amount DECIMAL(15,2) DEFAULT 0,
                deadline DATE NOT NULL,
                category VARCHAR(100),
                image_url VARCHAR(500),
                qris_image VARCHAR(500),
                video_url VARCHAR(500),
                status ENUM('active', 'closed', 'completed') DEFAULT 'active',
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )";
            $conn->query($createCampaigns);
            
            // Tambahkan kolom qris_image jika belum ada (untuk database yang sudah ada)
            // Cek apakah kolom sudah ada
            $checkQrisColumn = $conn->query("SHOW COLUMNS FROM campaigns LIKE 'qris_image'");
            if (!$checkQrisColumn || $checkQrisColumn->num_rows == 0) {
                // Kolom belum ada, tambahkan
                $conn->query("ALTER TABLE campaigns ADD COLUMN qris_image VARCHAR(500) DEFAULT NULL AFTER image_url");
            }
            
            // Buat tabel donations
            $createDonations = "CREATE TABLE IF NOT EXISTS donations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                campaign_id INT NOT NULL,
                user_id INT,
                amount DECIMAL(15,2) NOT NULL,
                payment_method ENUM('qris', 'transfer') NOT NULL,
                proof_image VARCHAR(500),
                is_anonymous BOOLEAN DEFAULT FALSE,
                status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )";
            $conn->query($createDonations);
            
            // Buat tabel campaign_updates
            $createUpdates = "CREATE TABLE IF NOT EXISTS campaign_updates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                campaign_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                image_url VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
            )";
            $conn->query($createUpdates);
            
            // Buat tabel fund_reports
            $createReports = "CREATE TABLE IF NOT EXISTS fund_reports (
                id INT AUTO_INCREMENT PRIMARY KEY,
                campaign_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                expense_amount DECIMAL(15,2) NOT NULL,
                receipt_image VARCHAR(500),
                distribution_image VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
            )";
            $conn->query($createReports);
            
            // Buat admin default
            $adminCheck = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
            if ($adminCheck->num_rows == 0) {
                $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
                $conn->query("INSERT INTO users (username, email, password, full_name, role) 
                             VALUES ('admin', 'admin@careu.com', '$adminPassword', 'Administrator', 'admin')");
            }
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
}
?>