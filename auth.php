<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$sid = $data['school_id'] ?? '';
$pass = $data['password'] ?? '';

if ($action === 'register') {
    $name = $data['name'] ?? '';
    $school = $data['school'] ?? '';

    if (empty($sid) || empty($pass) || empty($name) || empty($school)) {
        echo json_encode(["success" => false, "message" => "Kailangang punan ang lahat ng field."]); // "All fields must be filled."
        exit;
    }

    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (school_id, name, school, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $sid, $name, $school, $hashed_pass);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        // Check for duplicate entry error (MySQL error code 1062)
        $message = $stmt->errno === 1062 ? "Ginagamit na ang School ID na ito." : "Nabigo ang pagpaparehistro: " . $stmt->error;
        echo json_encode(["success" => false, "message" => $message]);
    }
    $stmt->close();
} elseif ($action === 'login') {
    if (empty($sid) || empty($pass)) {
        echo json_encode(["success" => false, "message" => "Kailangan ang School ID/Pangalan at password."]);
        exit;
    }

    // Allow login with either school_id or name
    $stmt = $conn->prepare("SELECT * FROM users WHERE school_id = ? OR name = ?");
    $stmt->bind_param("ss", $sid, $sid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $login_successful = false;
    if ($result->num_rows > 0) {
        // Loop through results in case of non-unique names
        while ($user = $result->fetch_assoc()) {
            if (password_verify($pass, $user['password'])) {
                unset($user['password']);
                echo json_encode(["success" => true, "user" => $user]);
                $login_successful = true;
                break; // Exit after successful login
            }
        }
    }
    if (!$login_successful) {
        echo json_encode(["success" => false, "message" => "Maling School ID/Pangalan o password."]);
    }
    $stmt->close();
}
?>