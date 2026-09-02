<?php


session_start();
include '../Model/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    die("Unauthorized");
}

$label = isset($_GET['label']) ? $conn->real_escape_string($_GET['label']) : '';

if (empty($label))
    die("Invalid Archive");

$sql_meals = "SELECT resident_name, SUM(lunch) as total_lunch, SUM(dinner) as total_dinner 
              FROM archived_meals WHERE archive_label = '$label' GROUP BY resident_name";
$res_meals = $conn->query($sql_meals);
$sql_exp = "SELECT * FROM archived_expenses WHERE archive_label = '$label' ORDER BY date DESC";
$res_exp = $conn->query($sql_exp);
$sql_dep = "SELECT * FROM archived_deposits WHERE archive_label = '$label' ORDER BY date DESC";
$res_dep = $conn->query($sql_dep);

echo "<h3>Meal Summary</h3>";
echo "<table><tr><th>Resident</th><th>Total Lunch</th><th>Total Dinner</th></tr>";
if ($res_meals && $res_meals->num_rows > 0) {
    while ($row = $res_meals->fetch_assoc()) {
        echo "<tr><td>{$row['resident_name']}</td><td>{$row['total_lunch']}</td><td>{$row['total_dinner']}</td></tr>";
    }
} else {
    echo "<tr><td colspan='3'>No data</td></tr>";
}
echo "</table>";

echo "<h3>Expenses Log</h3>";
echo "<table><tr><th>Date</th><th>Description</th><th>Amount</th><th>Category</th></tr>";
if ($res_exp && $res_exp->num_rows > 0) {
    $total_exp = 0;
    while ($row = $res_exp->fetch_assoc()) {
        $total_exp += $row['amount'];
        echo "<tr><td>{$row['date']}</td><td>{$row['description']}</td><td>{$row['amount']}</td><td>{$row['category']}</td></tr>";
    }
    echo "<tr><td colspan='2'><strong>Total</strong></td><td colspan='2'><strong>$total_exp</strong></td></tr>";
} else {
    echo "<tr><td colspan='4'>No data</td></tr>";
}
echo "</table>";

echo "<h3>Deposits Log</h3>";
echo "<table><tr><th>Date</th><th>Resident</th><th>Amount</th></tr>";
if ($res_dep && $res_dep->num_rows > 0) {
    $total_dep = 0;
    while ($row = $res_dep->fetch_assoc()) {
        $total_dep += $row['amount'];
        echo "<tr><td>{$row['date']}</td><td>{$row['resident_name']}</td><td>{$row['amount']}</td></tr>";
    }
    echo "<tr><td colspan='2'><strong>Total</strong></td><td><strong>$total_dep</strong></td></tr>";
} else {
    echo "<tr><td colspan='3'>No data</td></tr>";
}
echo "</table>";

$conn->close();
?>