<?php


session_start();
include '../Model/config.php';
include 'period_utils.php';

// Security Check: Only Supervisor can delete entries
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}

error_reporting(0);
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$id = isset($data['id']) ? $conn->real_escape_string($data['id']) : '';
$type = isset($data['type']) ? $conn->real_escape_string($data['type']) : ''; // 'expense' or 'deposit'

// Determine Target Table
$table = ($type === 'expense') ? 'expenses' : 'deposits';

// Period Validation: Ensure we are not deleting data from a closed/archived period.
// $period_id = get_active_period_id($conn);
// $check = $conn->query("SELECT period_id FROM $table WHERE id='$id'");
// $row = $check->fetch_assoc();

// if (!$row || $row['period_id'] != $period_id) {
//     echo json_encode(["status" => "error", "message" => "Cannot delete data from a closed period."]);
//     exit();
// }

$sql = "DELETE FROM $table WHERE id = '$id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Entry deleted successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
}

$conn->close();
?>