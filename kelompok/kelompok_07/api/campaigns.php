<?php
require_once '../config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$conn = getConnection();

if ($action === 'list') {
    $sort = $_GET['sort'] ?? 'latest';
    $category = $_GET['category'] ?? '';
    
    $query = "SELECT c.*, u.full_name as creator_name, 
              COUNT(DISTINCT d.id) as donor_count,
              (c.current_amount / c.target_amount * 100) as progress
              FROM campaigns c
              LEFT JOIN users u ON c.created_by = u.id
              LEFT JOIN donations d ON c.id = d.campaign_id AND d.status = 'verified'
              WHERE c.status = 'active'";
    
    if (!empty($category)) {
        $query .= " AND c.category = ?";
    }
    
    $query .= " GROUP BY c.id";
    
    switch ($sort) {
        case 'urgent':
            $query .= " ORDER BY c.deadline ASC";
            break;
        case 'nearly_reached':
            $query .= " ORDER BY progress DESC";
            break;
        case 'latest':
        default:
            $query .= " ORDER BY c.created_at DESC";
            break;
    }
    
    $stmt = $conn->prepare($query);
    if (!empty($category)) {
        $stmt->bind_param("s", $category);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $campaigns = [];
    while ($row = $result->fetch_assoc()) {
        $campaigns[] = $row;
    }
    
    echo json_encode(['success' => true, 'campaigns' => $campaigns]);
    $stmt->close();
}

elseif ($action === 'detail') {
    $id = $_GET['id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT c.*, u.full_name as creator_name 
                           FROM campaigns c
                           LEFT JOIN users u ON c.created_by = u.id
                           WHERE c.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $campaign = $result->fetch_assoc();
        
        // Get updates
        $updatesStmt = $conn->prepare("SELECT * FROM campaign_updates WHERE campaign_id = ? ORDER BY created_at DESC");
        $updatesStmt->bind_param("i", $id);
        $updatesStmt->execute();
        $updates = $updatesStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $campaign['updates'] = $updates;
        $updatesStmt->close();
        
        // Get reports
        $reportsStmt = $conn->prepare("SELECT * FROM fund_reports WHERE campaign_id = ? ORDER BY created_at DESC");
        $reportsStmt->bind_param("i", $id);
        $reportsStmt->execute();
        $reports = $reportsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $campaign['reports'] = $reports;
        $reportsStmt->close();
        
        // Get donation stats
        $statsStmt = $conn->prepare("SELECT 
            COUNT(*) as total_donations,
            SUM(amount) as total_verified
            FROM donations 
            WHERE campaign_id = ? AND status = 'verified'");
        $statsStmt->bind_param("i", $id);
        $statsStmt->execute();
        $stats = $statsStmt->get_result()->fetch_assoc();
        $campaign['stats'] = $stats;
        $statsStmt->close();
        
        echo json_encode(['success' => true, 'campaign' => $campaign]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Campaign tidak ditemukan']);
    }
    $stmt->close();
}

$conn->close();
?>
