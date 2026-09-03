<?php
session_start(); // START SESSION AT THE VERY TOP
include 'config.php';

 $json = file_get_contents('php://input');
 $data = json_decode($json, true);

 $username = $data['username'];
 $password = $data['password'];

 $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
 $result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // SET SESSION VARIABLES
    $_SESSION['username'] = $row['username'];
    $_SESSION['role'] = $row['role'];

    $redirectPage = "index.php";
    if ($row['role'] == 'admin') {
        $redirectPage = "dashboard_admin.php";
    } elseif ($row['role'] == 'supervisor') {
        $redirectPage = "dashboard_supervisor.php";
    } elseif ($row['role'] == 'resident') {
        $redirectPage = "dashboard_resident.php";
    }

    echo json_encode([
        "status" => "success", 
        "redirectUrl" => $redirectPage,
        "role" => $row['role']
    ]);

} else {
    echo json_encode(["status" => "error", "message" => "Invalid Username or Password"]);
}

 $conn->close();
?>