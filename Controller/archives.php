<?php

session_start();
include '../Model/config.php';

// Check for Supervisor access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../Main page/index.php");
    exit();
}

// Fetch all Archive Labels
// We assume 'archived_meals' is a good source of truth for when archives happened.
$sql_periods = "SELECT DISTINCT archive_label, archived_at FROM archived_meals ORDER BY archived_at DESC";
$res_periods = $conn->query($sql_periods);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Data</title>
    <link rel="stylesheet" href="../View/Assets/style.css">
    <style>
        .archive-container {
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        select {
            padding: 10px;
            font-size: 16px;
            width: 100%;
            margin-bottom: 20px;
        }

        h2,
        h3 {
            color: #333;
        }

        .data-section {
            display: none;
            margin-top: 20px;
        }

        .data-section.active {
            display: block;
        }
    </style>
</head>

<body>

    <div class="archive-container">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2>📂 Archived Data</h2>
            <a href="../Dashboards/dashboard_supervisor.php" class="btn-personal" style="text-decoration:none;">Back to Dashboard</a>
        </div>

        <!-- Dropdown to select which past month/period to view -->
        <label>Select Archived Date:</label>
        <select id="archiveSelector" onchange="loadArchiveData()">
            <option value="">-- Select an Archive --</option>
            <?php
            if ($res_periods && $res_periods->num_rows > 0) {
                while ($row = $res_periods->fetch_assoc()) {
                    echo "<option value='{$row['archive_label']}'>{$row['archive_label']}</option>";
                }
            } else {
                echo "<option disabled>No archives found.</option>";
            }
            ?>
        </select>

        <div id="loading" style="display:none;">Loading data...</div>
        <div id="archiveContent"></div>

    </div>

    <script>
       
        async function loadArchiveData() {
            const label = document.getElementById('archiveSelector').value;
            const container = document.getElementById('archiveContent');
            const loading = document.getElementById('loading');

            if (!label) {
                container.innerHTML = "";
                return;
            }

            loading.style.display = "block";
            container.innerHTML = "";

            try {
                const res = await fetch(`get_archive_data.php?label=${encodeURIComponent(label)}`);
                const html = await res.text();
                container.innerHTML = html;
            } catch (err) {
                console.error(err);
                container.innerHTML = "<p style='color:red;'>Error loading data.</p>";
            } finally {
                loading.style.display = "none";
            }
        }
    </script>

</body>

</html>