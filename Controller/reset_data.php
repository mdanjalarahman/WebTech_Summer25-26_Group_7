<?php


session_start();
include '../Model/config.php';

// Security Check: Only Supervisor can reset data
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

header('Content-Type: application/json');

// Clear previous Trash
// We only keep one previous state to revert to.
$conn->query("TRUNCATE TABLE trash_expenses");
$conn->query("TRUNCATE TABLE trash_deposits");
$conn->query("TRUNCATE TABLE trash_meals");

// 3. Backup Live Data to Trash
$sql_backup_exp = "INSERT INTO trash_expenses (original_id, description, amount, category, date, period_id)
                   SELECT id, description, amount, category, date, period_id FROM expenses";
$sql_backup_dep = "INSERT INTO trash_deposits (original_id, resident_name, amount, date, period_id)
                   SELECT id, resident_name, amount, date, period_id FROM deposits";
$sql_backup_meal = "INSERT INTO trash_meals (date, resident_name, lunch, dinner, period_id)
                    SELECT date, resident_name, lunch, dinner, period_id FROM meals";

$backup_ok = true;
if (!$conn->query($sql_backup_exp))
    $backup_ok = false;
if (!$conn->query($sql_backup_dep))
    $backup_ok = false;
if (!$conn->query($sql_backup_meal))
    $backup_ok = false;

if (!$backup_ok) {
    echo json_encode(["status" => "error", "message" => "Backup failed. Aborting reset to prevent data loss."]);
    exit();
}

// 4. Delete Live Data (The actual Reset)
$conn->query("DELETE FROM expenses");
$conn->query("DELETE FROM deposits");
$conn->query("DELETE FROM meals");

echo json_encode(["status" => "success", "message" => "Data Reset. You can UNDO this action if needed."]);

$conn->close();
?>