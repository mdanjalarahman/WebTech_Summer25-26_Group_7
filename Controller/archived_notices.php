<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../Main page/index.php");
    exit();
}
include '../Model/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Notices</title>
    <link rel="stylesheet" href="../View/Assets/style.css">
    <style>
        .archive-container {
            width: 79%;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .archive-item {
            border-left: 4px solid #aaa;
            background: #f9f9f9;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 0 4px 4px 0;
        }
    </style>
</head>

<body>
    <div class="archive-container">
        <a href="javascript:history.back()" class="back-link">&larr; Back to Dashboard</a>
        <h2>Archived Notices</h2> 
        <p style="color:#666; margin-bottom: 30px;">Notices older than 24 hours.</p>

        <?php
        $sql_archived = "SELECT * FROM notices WHERE created_at <= NOW() - INTERVAL 24 HOUR ORDER BY created_at DESC";
        $result = $conn->query($sql_archived);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='archive-item'>
                    <h4 style='margin:0 0 5px 0; color:#333;'>" . htmlspecialchars($row['title']) . "</h4>
                    <p style='margin:5px 0; color:#555;'>" . htmlspecialchars($row['message']) . "</p>
                    <small style='color:#888;'>Posted: " . $row['created_at'] . "</small>
                  </div>";
            }
        } else {
            echo "<p style='text-align:center; color:#888;'>No archived notices found.</p>";
        }
        ?>
    </div>

</body>

</html>