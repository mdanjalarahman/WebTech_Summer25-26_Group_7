<?php
session_start();
include '../Model/config.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'resident' && $_SESSION['role'] !== 'supervisor')) {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}
 
error_reporting(0);
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$resident = $_SESSION['username'];
$message = isset($data['message']) ? $conn->real_escape_string($data['message']) : '';

$sql = "INSERT INTO complaints (resident_name, message) VALUES ('$resident', '$message')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Complaint Submitted Successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
}

$conn->close();
?>