<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$action = $_GET['action'] ?? '';

if ($action === 'list_users') {
    $sql = "SELECT u.name, u.school_id, 
            COUNT(CASE WHEN up.completed = 1 THEN 1 END) as total_completed,
            SUM(COALESCE(up.retakes, 0)) as total_retakes
            FROM users u 
            LEFT JOIN user_progress up ON u.school_id = up.school_id
            WHERE u.school_id != 'admin'
            GROUP BY u.school_id, u.name 
            ORDER BY total_completed DESC, u.name ASC";
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(["success" => false, "message" => $conn->error]);
        exit;
    }

    $users = [];
    while($row = $result->fetch_assoc()) $users[] = $row;
    echo json_encode(["success" => true, "users" => $users]);

} elseif ($action === 'dashboard_stats') {
    // This action is for the admin dashboard to get summary statistics.
    $stats = [
        'total_users' => 0,
        'total_completed' => 0,
        'total_retakes' => 0
    ];

    // Get total users (excluding admin)
    $result_users = $conn->query("SELECT COUNT(*) as total FROM users WHERE school_id != 'admin'");
    if ($result_users) $stats['total_users'] = $result_users->fetch_assoc()['total'];

    // Get total completed stories and total retakes from the progress table
    $result_progress = $conn->query("SELECT COUNT(CASE WHEN completed = 1 THEN 1 END) as total_completed, SUM(COALESCE(retakes, 0)) as total_retakes FROM user_progress WHERE school_id != 'admin'");
    if ($result_progress) {
        $progress_data = $result_progress->fetch_assoc();
        $stats['total_completed'] = $progress_data['total_completed'] ?? 0;
        $stats['total_retakes'] = $progress_data['total_retakes'] ?? 0;
    }

    echo json_encode(["success" => true, "stats" => $stats]);

} elseif ($action === 'user_details') {
    $sid = $_GET['school_id'] ?? '';
    if (empty($sid)) { exit(json_encode(["success" => false, "message" => "Missing School ID."])); }

    $stmt = $conn->prepare("SELECT story_id, retakes, completed FROM user_progress WHERE school_id = ?");
    $stmt->bind_param("s", $sid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        echo json_encode(["success" => false, "message" => $stmt->error]);
        exit;
    }

    $records = [];
    while($row = $result->fetch_assoc()) $records[] = $row;
    echo json_encode(["success" => true, "records" => $records]);
    $stmt->close();

} elseif ($action === 'get_progress') {
    $sid = $_GET['school_id'] ?? '';
    if (empty($sid)) { exit(json_encode(["success" => false, "message" => "Missing School ID."])); }

    $stmt = $conn->prepare("SELECT story_id FROM user_progress WHERE school_id = ? AND completed = 1");
    $stmt->bind_param("s", $sid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        echo json_encode(["success" => false, "message" => $stmt->error]);
        exit;
    }

    $completed = [];
    while($row = $result->fetch_assoc()) $completed[] = $row['story_id'];
    echo json_encode(["success" => true, "completed" => $completed]);
    $stmt->close();
}

$conn->close();