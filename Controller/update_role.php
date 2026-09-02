<?php


session_start();
include 'config.php';

// only for admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$user_id = isset($data['user_id']) ? $conn->real_escape_string($data['user_id']) : '';
$new_role = isset($data['new_role']) ? $conn->real_escape_string($data['new_role']) : '';

if ($new_role === 'admin') {
    echo json_encode(["status" => "error", "message" => "Cannot assign Admin role via this panel."]);
    exit();
}

$sql = "UPDATE users SET role = '$new_role' WHERE id = '$user_id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Role updated successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
}

$conn->close();
?>