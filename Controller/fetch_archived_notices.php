<?php


include '../Model/config.php';

//json output
error_reporting(0);
header('Content-Type: application/json');

//notices older than 24 hours
$sql = "SELECT * FROM notices WHERE created_at <= NOW() - INTERVAL 24 HOUR ORDER BY created_at DESC";
$result = $conn->query($sql);

$notices = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notices[] = $row;
    }
}

//return
echo json_encode(["status" => "success", "data" => $notices]);

$conn->close();
?>