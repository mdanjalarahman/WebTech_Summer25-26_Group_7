<?php

session_start();
include '../Model/config.php';
include 'period_utils.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}

error_reporting(0);
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$id = isset($data['id']) ? $conn->real_escape_string($data['id']) : '';
$resident_name = isset($data['resident_name']) ? $conn->real_escape_string($data['resident_name']) : '';
$amount = isset($data['amount']) ? $conn->real_escape_string($data['amount']) : '';
$date = isset($data['date']) ? $conn->real_escape_string($data['date']) : '';

if (empty($id)) {
    echo json_encode(["status" => "error", "message" => "Invalid ID"]);
    exit();
}

// Ensure we are only editing data belonging to the current active month/period.
// Ensure we are only editing data belonging to the current active month/period.
// $period_id = get_active_period_id($conn);
// $check = $conn->query("SELECT period_id FROM deposits WHERE id='$id'");
// $row = $check->fetch_assoc();

// if (!$row || $row['period_id'] != $period_id) {
//     echo json_encode(["status" => "error", "message" => "Cannot edit data from a closed period."]);
//     exit();
// }

$sql = "UPDATE deposits SET resident_name='$resident_name', amount='$amount', date='$date' WHERE id='$id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Deposit Updated Successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
}

$conn->close();
?>