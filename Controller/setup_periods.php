<?php
// setup_periods.php

include '../Model/config.php';

echo "Starting Migration...<br>";

$sql = "CREATE TABLE IF NOT EXISTS periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    start_date DATETIME NOT NULL,
    end_date DATETIME DEFAULT NULL,
    status ENUM('active', 'closed') DEFAULT 'active'
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'periods' checked/created.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

$check = $conn->query("SELECT id FROM periods WHERE status='active' LIMIT 1");
$active_period_id = 0;
if ($check->num_rows == 0) {
    $start = date("Y-m-d H:i:s");
    $conn->query("INSERT INTO periods (start_date, status) VALUES ('$start', 'active')");
    $active_period_id = $conn->insert_id;
    echo "Created initial Active Period (ID: $active_period_id).<br>";
} else {
    $row = $check->fetch_assoc();
    $active_period_id = $row['id'];
    echo "Active Period exists (ID: $active_period_id).<br>";
}

$tables = ['meals', 'expenses', 'deposits'];

foreach ($tables as $table) {
    $col_check = $conn->query("SHOW COLUMNS FROM $table LIKE 'period_id'");
    if ($col_check->num_rows == 0) {
        $alter = "ALTER TABLE $table ADD COLUMN period_id INT DEFAULT NULL";
        if ($conn->query($alter) === TRUE) {
            echo "Added 'period_id' to '$table'.<br>";
        } else {
            echo "Error altering '$table': " . $conn->error . "<br>";
        }
    } else {
        echo "'period_id' already exists in '$table'.<br>";
    }

    $update = "UPDATE $table SET period_id = $active_period_id WHERE period_id IS NULL";
    if ($conn->query($update) === TRUE) {
        // echo "Backfilled data in '$table'.<br>";
    } else {
        echo "Error backfilling '$table': " . $conn->error . "<br>";
    }
}

echo "Migration Complete.";
?>