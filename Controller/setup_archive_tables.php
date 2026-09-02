<?php
include '../Model/config.php';

echo "Setting up Archive Tables...<br>";
$sql_exp = "CREATE TABLE IF NOT EXISTS archived_expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_id INT,
    description VARCHAR(255),
    amount DECIMAL(10,2),
    category VARCHAR(50),
    date DATE,
    resident_name VARCHAR(100), 
    archived_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    archive_label VARCHAR(100)
)";
if ($conn->query($sql_exp) === TRUE)
    echo "archived_expenses created.<br>";
else
    echo "Error expenses: " . $conn->error . "<br>";

$sql_dep = "CREATE TABLE IF NOT EXISTS archived_deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_id INT,
    resident_name VARCHAR(100),
    amount DECIMAL(10,2),
    date DATE,
    archived_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    archive_label VARCHAR(100)
)";
if ($conn->query($sql_dep) === TRUE)
    echo "archived_deposits created.<br>";
else
    echo "Error deposits: " . $conn->error . "<br>";

$sql_meals = "CREATE TABLE IF NOT EXISTS archived_meals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE,
    resident_name VARCHAR(100),
    lunch INT,
    dinner INT,
    archived_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    archive_label VARCHAR(100)
)";
if ($conn->query($sql_meals) === TRUE)
    echo "archived_meals created.<br>";
else
    echo "Error meals: " . $conn->error . "<br>";

echo "Done.";
?>