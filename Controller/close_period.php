<?php


session_start();
include '../Model/config.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

header('Content-Type: application/json');

// Generate a Label for this Archive (e.g., "Closed 2024-05-30 14:00")
$label = "Closed " . date("Y-m-d H:i");

// Archive Expenses: Copies all rows from 'expenses' -> 'archived_expenses' with the label
$sql_exp = "INSERT INTO archived_expenses (original_id, description, amount, category, date, archive_label)
            SELECT id, description, amount, category, date, '$label' FROM expenses";

// Archive Deposits
$sql_dep = "INSERT INTO archived_deposits (original_id, resident_name, amount, date, archive_label)
            SELECT id, resident_name, amount, date, '$label' FROM deposits";

// Archive Meals
$sql_meal = "INSERT INTO archived_meals (date, resident_name, lunch, dinner, archive_label)
             SELECT date, resident_name, lunch, dinner, '$label' FROM meals";

$err = "";
if (!$conn->query($sql_exp))
    $err .= "Exp Error: " . $conn->error . " ";
if (!$conn->query($sql_dep))
    $err .= "Dep Error: " . $conn->error . " ";
if (!$conn->query($sql_meal))
    $err .= "Meal Error: " . $conn->error . " ";

if ($err === "") {
    echo json_encode(["status" => "success", "message" => "Period ARCHIVED successfully! Data is saved in history but still visible on dashboard."]);
} else {
    echo json_encode(["status" => "error", "message" => $err]);
}

$conn->close();
?>