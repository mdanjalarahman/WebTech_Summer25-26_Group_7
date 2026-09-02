<?php

include 'config.php';

echo "Setting up Trash Tables...<br>";

//trash all
$sql_exp = "CREATE TABLE IF NOT EXISTS trash_expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_id INT,
    description VARCHAR(255),
    amount DECIMAL(10,2),
    category VARCHAR(50),
    date DATE,
    period_id INT DEFAULT NULL
)";
if ($conn->query($sql_exp) === TRUE)
    echo "trash_expenses created.<br>";
else
    echo "Error expenses: " . $conn->error . "<br>";


$sql_dep = "CREATE TABLE IF NOT EXISTS trash_deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_id INT,
    resident_name VARCHAR(100),
    amount DECIMAL(10,2),
    date DATE,
    period_id INT DEFAULT NULL
)";
if ($conn->query($sql_dep) === TRUE)
    echo "trash_deposits created.<br>";
else
    echo "Error deposits: " . $conn->error . "<br>";

$sql_meals = "CREATE TABLE IF NOT EXISTS trash_meals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE,
    resident_name VARCHAR(100),
    lunch INT,
    dinner INT,
    period_id INT DEFAULT NULL
)";
if ($conn->query($sql_meals) === TRUE)
    echo "trash_meals created.<br>";
else
    echo "Error meals: " . $conn->error . "<br>";

echo "Done.";
?>