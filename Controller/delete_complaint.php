<?php

session_start();
include '../Model/config.php';

// Only Admin can delete complaints
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}


error_reporting(0);
header('Content-Type: application/json');

//Get id from Request
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$complaint_id = isset($data['id']) ? $conn->real_escape_string($data['id']) : '';

//Delete from Database
$sql = "DELETE FROM complaints WHERE id = '$complaint_id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Complaint resolved/removed!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
}

$conn->close();
?>