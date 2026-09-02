<?php

$host = "localhost";        // Database host
$user = "root";             // Database username
$pass = "";                 // Database password
$db_name = "bachelor_db"; // Database name

// Initialize database connection
$conn = new mysqli($host, $user, $pass, $db_name);

// Check connection status
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>