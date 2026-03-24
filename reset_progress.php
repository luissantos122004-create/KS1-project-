<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$data = json_decode(file_get_contents("php://input"), true);
$sid = $data['school_id'] ?? '';

if ($sid === 'admin') {
    // Silently succeed as there's no progress to reset for admin
    echo json_encode(["success" => true, "message" => "Admin progress is not tracked and cannot be reset."]);
    exit;
}

if (!$sid) {
    echo json_encode(["success" => false, "message" => "Missing School ID."]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM user_progress WHERE school_id = ?");
$stmt->bind_param("s", $sid);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Error resetting progress: " . $stmt->error]);
}
$stmt->close();
$conn->close();
?>