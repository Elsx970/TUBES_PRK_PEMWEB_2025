<?php
require_once '../config.php';
requireAdmin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$conn = getConnection();

if ($action === 'stats') {
    // Total campaigns
    $totalCampaigns = $conn->query("SELECT COUNT(*) as count FROM campaigns WHERE status = 'active'")->fetch_assoc()['count'];
    
    // Total donations
    $totalDonations = $conn->query("SELECT SUM(amount) as total FROM donations WHERE status = 'verified'")->fetch_assoc()['total'] ?? 0;
    
    // Pending donations
    $pendingDonations = $conn->query("SELECT COUNT(*) as count FROM donations WHERE status = 'pending'")->fetch_assoc()['count'];
    
    // Total donors
    $totalDonors = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM donations WHERE status = 'verified'")->fetch_assoc()['count'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_campaigns' => (int)$totalCampaigns,
            'total_donations' => (float)$totalDonations,
            'pending_donations' => (int)$pendingDonations,
            'total_donors' => (int)$totalDonors
        ]
    ]);
}

elseif ($action === 'list_campaigns') {
    $stmt = $conn->prepare("SELECT c.*, u.full_name as creator_name 
                           FROM campaigns c
                           LEFT JOIN users u ON c.created_by = u.id
                           ORDER BY c.created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $campaigns = [];
    while ($row = $result->fetch_assoc()) {
        $campaigns[] = $row;
    }
    
    echo json_encode(['success' => true, 'campaigns' => $campaigns]);
    $stmt->close();
}

elseif ($action === 'get_campaign') {
    $id = $_GET['id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT * FROM campaigns WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        echo json_encode(['success' => true, 'campaign' => $result->fetch_assoc()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Campaign tidak ditemukan']);
    }
    $stmt->close();
}

elseif ($action === 'create_campaign') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $background = $_POST['background'] ?? '';
    $target_amount = $_POST['target_amount'] ?? 0;
    $deadline = $_POST['deadline'] ?? '';
    $category = $_POST['category'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $video_url = $_POST['video_url'] ?? '';
    $created_by = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO campaigns (title, description, background, target_amount, deadline, category, image_url, video_url, created_by, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("sssdssssi", $title, $description, $background, $target_amount, $deadline, $category, $image_url, $video_url, $created_by);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Campaign berhasil dibuat']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat campaign']);
    }
    $stmt->close();
}

elseif ($action === 'update_campaign') {
    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $background = $_POST['background'] ?? '';
    $target_amount = $_POST['target_amount'] ?? 0;
    $deadline = $_POST['deadline'] ?? '';
    $category = $_POST['category'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $video_url = $_POST['video_url'] ?? '';
    
    $stmt = $conn->prepare("UPDATE campaigns SET title = ?, description = ?, background = ?, target_amount = ?, deadline = ?, category = ?, image_url = ?, video_url = ? WHERE id = ?");
    $stmt->bind_param("sssdssssi", $title, $description, $background, $target_amount, $deadline, $category, $image_url, $video_url, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Campaign berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate campaign']);
    }
    $stmt->close();
}

elseif ($action === 'close_campaign') {
    $id = $_POST['id'] ?? 0;
    
    $stmt = $conn->prepare("UPDATE campaigns SET status = 'closed' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Campaign berhasil ditutup']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menutup campaign']);
    }
    $stmt->close();
}

elseif ($action === 'delete_campaign') {
    $id = $_POST['id'] ?? 0;
    
    $stmt = $conn->prepare("DELETE FROM campaigns WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Campaign berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus campaign']);
    }
    $stmt->close();
}

elseif ($action === 'list_donations') {
    $stmt = $conn->prepare("SELECT d.*, c.title as campaign_title
                           FROM donations d
                           JOIN campaigns c ON d.campaign_id = c.id
                           ORDER BY d.created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $donations = [];
    while ($row = $result->fetch_assoc()) {
        $donations[] = $row;
    }
    
    echo json_encode(['success' => true, 'donations' => $donations]);
    $stmt->close();
}

elseif ($action === 'verify_donation') {
    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? 'pending';
    
    $stmt = $conn->prepare("UPDATE donations SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        // Update campaign current_amount if verified
        if ($status === 'verified') {
            $donationStmt = $conn->prepare("SELECT campaign_id, amount FROM donations WHERE id = ?");
            $donationStmt->bind_param("i", $id);
            $donationStmt->execute();
            $donation = $donationStmt->get_result()->fetch_assoc();
            $donationStmt->close();
            
            if ($donation) {
                $updateStmt = $conn->prepare("UPDATE campaigns SET current_amount = current_amount + ? WHERE id = ?");
                $updateStmt->bind_param("di", $donation['amount'], $donation['campaign_id']);
                $updateStmt->execute();
                $updateStmt->close();
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Status donasi berhasil diubah']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengubah status donasi']);
    }
    $stmt->close();
}

elseif ($action === 'add_update') {
    $campaign_id = $_POST['campaign_id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO campaign_updates (campaign_id, title, content, image_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $campaign_id, $title, $content, $image_url);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Update berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan update']);
    }
    $stmt->close();
}

elseif ($action === 'add_report') {
    $campaign_id = $_POST['campaign_id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $expense_amount = $_POST['expense_amount'] ?? 0;
    $receipt_image = $_POST['receipt_image'] ?? '';
    $distribution_image = $_POST['distribution_image'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO fund_reports (campaign_id, title, description, expense_amount, receipt_image, distribution_image) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdss", $campaign_id, $title, $description, $expense_amount, $receipt_image, $distribution_image);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Laporan berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan laporan']);
    }
    $stmt->close();
}

elseif ($action === 'statistics') {
    // Donation statistics (last 7 days)
    $donationData = [];
    $donationLabels = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $donationLabels[] = date('d M', strtotime($date));
        
        $stmt = $conn->prepare("SELECT SUM(amount) as total FROM donations WHERE DATE(created_at) = ? AND status = 'verified'");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $donationData[] = (float)($result['total'] ?? 0);
        $stmt->close();
    }
    
    // Campaign statistics (top 5 by amount)
    $campaignStmt = $conn->query("SELECT title, current_amount FROM campaigns ORDER BY current_amount DESC LIMIT 5");
    $campaignLabels = [];
    $campaignData = [];
    while ($row = $campaignStmt->fetch_assoc()) {
        $campaignLabels[] = substr($row['title'], 0, 20) . '...';
        $campaignData[] = (float)$row['current_amount'];
    }
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'donation_labels' => $donationLabels,
            'donation_data' => $donationData,
            'campaign_labels' => $campaignLabels,
            'campaign_data' => $campaignData
        ]
    ]);
}

$conn->close();
?>
