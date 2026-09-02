<?php
session_start();
include '../Model/config.php';
include 'period_utils.php';
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'resident' && $_SESSION['role'] !== 'supervisor')) {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}

error_reporting(0);
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$resident = $_SESSION['username'];

if ($_SESSION['role'] === 'supervisor' && isset($data['target_user']) && !empty($data['target_user'])) {
    $resident = $conn->real_escape_string($data['target_user']);
}
$date = isset($data['date']) ? $conn->real_escape_string($data['date']) : '';
$original_date = isset($data['original_date']) ? $conn->real_escape_string($data['original_date']) : '';

$lunch = isset($data['lunch']) ? (int) $data['lunch'] : 0;
$dinner = isset($data['dinner']) ? (int) $data['dinner'] : 0;

if (!empty($original_date) && $original_date !== $date) {
    $del_sql = "DELETE FROM meals WHERE resident_name='$resident' AND date='$original_date'";
    $conn->query($del_sql);
}

$period_id = get_active_period_id($conn);
if (!$period_id) {
    echo json_encode(["status" => "error", "message" => "No active period found!"]);
    exit();
}

$sql = "INSERT INTO meals (date, resident_name, lunch, dinner, period_id) 
        VALUES ('$date', '$resident', '$lunch', '$dinner', '$period_id')
        ON DUPLICATE KEY UPDATE lunch='$lunch', dinner='$dinner'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Meal preference updated!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
}

$conn->close();
?>