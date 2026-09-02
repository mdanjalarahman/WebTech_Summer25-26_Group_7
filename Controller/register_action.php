<?php

session_start();
include '../Model/config.php'; 
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data === null) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
    exit();
}
$username = isset($data['username']) ? $conn->real_escape_string($data['username']) : '';
$password = isset($data['password']) ? md5($conn->real_escape_string($data['password'])) : '';
$phone = isset($data['phone']) ? $conn->real_escape_string($data['phone']) : '';
$emergency_contact = isset($data['emergency_contact']) ? $conn->real_escape_string($data['emergency_contact']) : '';
$nid = isset($data['nid']) ? $conn->real_escape_string($data['nid']) : '';
$occupation = isset($data['occupation']) ? $conn->real_escape_string($data['occupation']) : '';

if (empty($username) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Username and Password are required"]);
    exit();
}

$checkSql = "SELECT * FROM users WHERE username = '$username'";
$result = $conn->query($checkSql);

if ($result && $result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Username already taken!"]);
} else {
    $alterQueries = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS emergency_contact VARCHAR(20) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS nid VARCHAR(50) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS occupation VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'pending'"
    ];

    foreach ($alterQueries as $alterSql) {
        $conn->query($alterSql);
    }

    $insertSql = "INSERT INTO users (username, password, role, phone, emergency_contact, nid, occupation, status) 
                  VALUES ('$username', '$password', 'resident', '$phone', '$emergency_contact', '$nid', '$occupation', 'pending')";

    if ($conn->query($insertSql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Registration Successful! Please Login."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
    }
}

$conn->close();
?>