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
        $uploadDir = 'uploads/proofs/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['proof_image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $targetPath)) {
            $proof_image = $targetPath;
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
