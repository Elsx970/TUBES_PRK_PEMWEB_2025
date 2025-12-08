<?php
require_once 'config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

$conn = getConnection();

if ($action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT id, username, email, password, full_name, role FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            echo json_encode(['success' => true, 'role' => $user['role']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Password salah']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Username/Email tidak ditemukan']);
    }
    $stmt->close();
}

elseif ($action === 'register') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    
    // Validasi
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        exit;
    }
    
    // Cek username/email sudah ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username atau Email sudah terdaftar']);
        $stmt->close();
        exit;
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'user')");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $full_name);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registrasi berhasil']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registrasi gagal']);
    }
    $stmt->close();
}

elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
}

$conn->close();
?>
