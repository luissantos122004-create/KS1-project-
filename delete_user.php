<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$data = json_decode(file_get_contents("php://input"), true);

$admin_id = $data['admin_id'] ?? null;
$sid_to_delete = $data['school_id_to_delete'] ?? null;

// Server-side validation to ensure only an admin can delete users.
if ($admin_id !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(["success" => false, "message" => "Unauthorized action. Admin access required."]);
    exit;
}

if (!$sid_to_delete) {
    echo json_encode(["success" => false, "message" => "Missing user School ID to delete."]);
    exit;
}

if ($sid_to_delete === 'admin') {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "The admin account cannot be deleted."]);
    exit;
}

$conn->begin_transaction();

try {
    // First, delete user's progress records to maintain data integrity
    $stmt1 = $conn->prepare("DELETE FROM user_progress WHERE school_id = ?");
    $stmt1->bind_param("s", $sid_to_delete);
    $stmt1->execute();
    $stmt1->close();

    // Then, delete the user from the users table
    $stmt2 = $conn->prepare("DELETE FROM users WHERE school_id = ?");
    $stmt2->bind_param("s", $sid_to_delete);
    $stmt2->execute();
    $stmt2->close();

    $conn->commit();
    echo json_encode(["success" => true]);
} catch (mysqli_sql_exception $exception) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Error deleting user: " . $exception->getMessage()]);
}

$conn->close();
?>