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

$description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
$amount = isset($data['amount']) ? $conn->real_escape_string($data['amount']) : '';
$category = isset($data['category']) ? $conn->real_escape_string($data['category']) : '';
$date = isset($data['date']) ? $conn->real_escape_string($data['date']) : '';


$period_id = get_active_period_id($conn);
if (!$period_id) {
    echo json_encode(["status" => "error", "message" => "No active period found. Please ask Admin to reset."]);
    exit();
}

$sql = "INSERT INTO expenses (description, amount, category, date, period_id) VALUES ('$description', '$amount', '$category', '$date', '$period_id')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Expense Added Successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
}

$conn->close();
?>