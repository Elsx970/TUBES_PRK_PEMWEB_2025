<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$conn = getConnection();

if ($action === 'create') {
    $campaign_id = $_POST['campaign_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    
    // Handle file upload
    $proof_image = '';
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === 0) {
        // Path relatif dari api/ ke root project
        $uploadDir = '../uploads/proofs/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Validasi file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['proof_image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP']);
            $conn->close();
            exit;
        }
        
        // Validasi file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($_FILES['proof_image']['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB']);
            $conn->close();
            exit;
        }
        
        // Generate unique filename
        $fileExtension = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['proof_image']['name'], '.' . $fileExtension)) . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $targetPath)) {
            // Simpan path relatif dari root (tanpa ../)
            $proof_image = 'uploads/proofs/' . $fileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengupload file. Pastikan folder uploads/proofs/ memiliki permission write']);
            $conn->close();
            exit;
        }
    }
    
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO donations (campaign_id, user_id, amount, payment_method, proof_image, is_anonymous, status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iidssi", $campaign_id, $user_id, $amount, $payment_method, $proof_image, $is_anonymous);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Donasi berhasil dibuat']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat donasi']);
    }
    $stmt->close();
}

elseif ($action === 'history') {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT d.*, c.title as campaign_title, c.image_url as campaign_image
                           FROM donations d
                           JOIN campaigns c ON d.campaign_id = c.id
                           WHERE d.user_id = ?
                           ORDER BY d.created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $donations = [];
    while ($row = $result->fetch_assoc()) {
        $donations[] = $row;
    }
    
    echo json_encode(['success' => true, 'donations' => $donations]);
    $stmt->close();
}

$conn->close();
?>
