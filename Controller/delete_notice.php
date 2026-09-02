<?php


session_start();
include '../Model/config.php';

//Only Admin can delete notices
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}

error_reporting(0);
header('Content-Type: application/json');

//Get id
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$notice_id = isset($data['id']) ? $conn->real_escape_string($data['id']) : '';

//delete from Database
$sql = "DELETE FROM notices WHERE id = '$notice_id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Notice deleted!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
}

$conn->close();
?>