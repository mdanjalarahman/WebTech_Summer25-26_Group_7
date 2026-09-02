<?php

session_start();
include '../Model/config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

header('Content-Type: application/json');

$check = $conn->query("SELECT count(*) as cnt FROM trash_expenses");
$cnt_exp = $check->fetch_assoc()['cnt'];
$check = $conn->query("SELECT count(*) as cnt FROM trash_deposits");
$cnt_dep = $check->fetch_assoc()['cnt'];
$check = $conn->query("SELECT count(*) as cnt FROM trash_meals");
$cnt_meal = $check->fetch_assoc()['cnt'];

if (($cnt_exp + $cnt_dep + $cnt_meal) == 0) {
    echo json_encode(["status" => "error", "message" => "No data found in validation trash to restore."]);
    exit();
}

$sql_res_exp = "INSERT INTO expenses (description, amount, category, date, period_id)
                SELECT description, amount, category, date, period_id FROM trash_expenses";

$sql_res_dep = "INSERT INTO deposits (resident_name, amount, date, period_id)
                SELECT resident_name, amount, date, period_id FROM trash_deposits";

$sql_res_meal = "INSERT INTO meals (date, resident_name, lunch, dinner, period_id)
                 SELECT date, resident_name, lunch, dinner, period_id FROM trash_meals";

$res_ok = true;
if (!$conn->query($sql_res_exp))
    $res_ok = false;
if (!$conn->query($sql_res_dep))
    $res_ok = false;
if (!$conn->query($sql_res_meal))
    $res_ok = false;

if ($res_ok) {
    $conn->query("TRUNCATE TABLE trash_expenses");
    $conn->query("TRUNCATE TABLE trash_deposits");
    $conn->query("TRUNCATE TABLE trash_meals");
    echo json_encode(["status" => "success", "message" => "Reset Undone! Data restored."]);
} else {
    echo json_encode(["status" => "error", "message" => "Restoration failed: " . $conn->error]);
}

$conn->close();
?>