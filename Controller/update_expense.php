<?php

session_start();
include '../Model/config.php';
include 'period_utils.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}

// Setup JSON Response
error_reporting(0);
header('Content-Type: application/json');

// Get Data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$id = isset($data['id']) ? $conn->real_escape_string($data['id']) : '';
$description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
$amount = isset($data['amount']) ? $conn->real_escape_string($data['amount']) : '';
$category = isset($data['category']) ? $conn->real_escape_string($data['category']) : '';
$date = isset($data['date']) ? $conn->real_escape_string($data['date']) : '';

if (empty($id)) {
    echo json_encode(["status" => "error", "message" => "Invalid ID"]);
    exit();
}

//Period Validation

// $period_id = get_active_period_id($conn);
// $check = $conn->query("SELECT period_id FROM expenses WHERE id='$id'");
// $row = $check->fetch_assoc();

// if (!$row || $row['period_id'] != $period_id) {
//     echo json_encode(["status" => "error", "message" => "Cannot edit data from a closed period."]);
//     exit();
// }

//Update Record
$sql = "UPDATE expenses SET description='$description', amount='$amount', category='$category', date='$date' WHERE id='$id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Expense Updated Successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
}

$conn->close();
?>