<?php


session_start();
include 'config.php';

// only admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}

error_reporting(0);
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$id = isset($data['id']) ? $conn->real_escape_string($data['id']) : '';
// sql injection sanitize.
$phone = isset($data['phone']) ? $conn->real_escape_string($data['phone']) : '';
$emergency_contact = isset($data['emergency_contact']) ? $conn->real_escape_string($data['emergency_contact']) : '';
$nid = isset($data['nid']) ? $conn->real_escape_string($data['nid']) : '';
$occupation = isset($data['occupation']) ? $conn->real_escape_string($data['occupation']) : '';

$sql = "UPDATE users SET phone='$phone', emergency_contact='$emergency_contact', nid='$nid', occupation='$occupation' WHERE id='$id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "User details updated successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
}

$conn->close();
?>