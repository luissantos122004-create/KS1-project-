<?php
header('Content-Type: application/json');
require_once 'db_config.php';

// Get data from frontend
$data = json_decode(file_get_contents('php://input'), true);
$school_id = $data['school_id'] ?? null;
$story_id = $data['story_id'] ?? null;
$is_perfect = isset($data['is_perfect']) ? ($data['is_perfect'] ? 1 : 0) : 0;

// Prevent admin progress from being recorded
if ($school_id === 'admin') {
    echo json_encode(["success" => true, "message" => "Admin progress is not tracked."]);
    exit;
}

if (!$school_id || !$story_id) {
    echo json_encode(["success" => false, "message" => "Missing required data."]);
    exit;
}

// Check if the user already has a record for this specific story
$stmt = $conn->prepare("SELECT completed FROM user_progress WHERE school_id = ? AND story_id = ?");
$stmt->bind_param("ss", $school_id, $story_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // RECORD EXISTS: Update based on this attempt
    $row = $result->fetch_assoc();
    $new_completed_status = ($row['completed'] == 1 || $is_perfect == 1) ? 1 : 0;

    if ($is_perfect == 0) {
        // If not perfect, increment retakes
        $update_stmt = $conn->prepare("UPDATE user_progress SET retakes = retakes + 1, completed = ?, last_attempt = CURRENT_TIMESTAMP WHERE school_id = ? AND story_id = ?");
        $update_stmt->bind_param("iss", $new_completed_status, $school_id, $story_id);
    } else {
        // If perfect, just update completed status (don't touch retakes)
        $update_stmt = $conn->prepare("UPDATE user_progress SET completed = ?, last_attempt = CURRENT_TIMESTAMP WHERE school_id = ? AND story_id = ?");
        $update_stmt->bind_param("iss", $new_completed_status, $school_id, $story_id);
    }
    $update_stmt->execute();
    $update_stmt->close();

} else {
    // NO RECORD: This is their first attempt.
    // A failed first attempt counts as 1 retake. A successful one is 0.
    $retakes_on_first_attempt = ($is_perfect == 0) ? 1 : 0;
    $insert_stmt = $conn->prepare("INSERT INTO user_progress (school_id, story_id, retakes, completed, last_attempt) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
    $insert_stmt->bind_param("ssii", $school_id, $story_id, $retakes_on_first_attempt, $is_perfect);
    $insert_stmt->execute();
    $insert_stmt->close();
}

$stmt->close();
echo json_encode(["success" => true]);
$conn->close();
?>