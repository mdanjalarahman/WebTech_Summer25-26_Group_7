<?php

session_start();

//caching logout works
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//check if  resident
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../Main page/index.php");
    exit();
}
include '../../Model/config.php';
$current_user = $_SESSION['username'];


$sql_tot_exp = "SELECT SUM(amount) as total FROM expenses";
$res_tot_exp = $conn->query($sql_tot_exp);
$global_expense = $res_tot_exp->fetch_assoc()['total'];
if ($global_expense == null)
    $global_expense = 0;


$sql_tot_meals = "SELECT SUM(lunch) + SUM(dinner) as count FROM meals";
$res_tot_meals = $conn->query($sql_tot_meals);
$global_meals = $res_tot_meals->fetch_assoc()['count'];
if ($global_meals == null)
    $global_meals = 1;

$meal_rate = $global_expense / $global_meals;


$sql_my_meals = "SELECT SUM(lunch) + SUM(dinner) as count FROM meals WHERE resident_name='$current_user'";
$res_my_meals = $conn->query($sql_my_meals);
$my_meal_count = $res_my_meals->fetch_assoc()['count'];
if ($my_meal_count == null)
    $my_meal_count = 0;


$sql_my_dep = "SELECT SUM(amount) as total FROM deposits WHERE resident_name='$current_user'";
$res_my_dep = $conn->query($sql_my_dep);
$my_deposits = $res_my_dep->fetch_assoc()['total'];
if ($my_deposits == null)
    $my_deposits = 0;

$my_total_cost = $my_meal_count * $meal_rate;
$my_due = $my_total_cost - $my_deposits;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard</title>
    <link rel="stylesheet" href="../Assets/style.css?v=<?php echo time(); ?>">
</head>

<body>

    <div class="dashboard-container">
        
        <div class="header-bar">
            <div>
                <h2 style="margin:0;">Resident Dashboard</h2>
                <p style="margin:5px 0 0 0; color:#666;">Welcome, <?php echo $current_user; ?>!</p>
            </div>
            <div class="header-right" style="display:flex; align-items:center;">
                
                <?php
                
                $sql_notices = "SELECT * FROM notices WHERE created_at > NOW() - INTERVAL 24 HOUR ORDER BY created_at DESC";
                $result_notices = $conn->query($sql_notices);
                $notice_count = $result_notices->num_rows;
                ?>
                <div class="notification-container">
                    <button class="notification-btn" onclick="toggleNotifications()">
                        Notifications
                        <?php if ($notice_count > 0) {
                            echo "<span class='notification-badge'>$notice_count</span>";
                        } ?>
                    </button>
                    
                    <div id="notificationDropdown" class="notification-dropdown" style="display: none;">
                        <div class="notification-header">Notifications</div>
                        <?php
                        if ($notice_count > 0) {
                            while ($row = $result_notices->fetch_assoc()) {
                                echo "<div class='notification-item' data-time='" . $row['created_at'] . "'>
                                        <strong>" . htmlspecialchars($row['title']) . "</strong>
                                        <p style='margin: 5px 0; font-size:0.9em;'>" . htmlspecialchars($row['message']) . "</p>
                                        <small style='color:#888;'>" . $row['created_at'] . "</small>
                                      </div>";
                            }
                        } else {
                            echo "<div class='notification-empty'>No new notifications</div>";
                        }
                        ?>
                        <div style="padding:10px; text-align:center; border-top:1px solid #eee;">
                            <a href="../../Controller/archived_notices.php" style="font-size:0.9em; color:#007bff;">View Archived
                                Notices</a>
                        </div>
                    </div>
                </div>
                <a href="../Main page/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>


        
        <div class="wallet-card">
            <div>
                <h4 style="margin:0;">My Wallet Status</h4>
                <p style="margin:5px 0; font-size: 0.9em; color:#666;">Rate per meal:
                    <?php echo number_format($meal_rate, 2); ?> BDT
                </p>
            </div>
            <div style="text-align: right;">
                <h3 style="margin:0; color: <?php echo $my_due > 0 ? 'red' : 'green'; ?>">
                    <?php echo $my_due > 0 ? 'Due: ' . number_format($my_due, 2) : 'Refund: ' . number_format(abs($my_due), 2); ?>
                    BDT
                </h3>
                <p style="margin:5px 0 0; font-size: 0.8em;">
                    Meals: <?php echo $my_meal_count; ?> | Cost: <?php echo number_format($my_total_cost, 2); ?> | Paid:
                    <?php echo number_format($my_deposits, 2); ?>
                </p>
            </div>
        </div>

        
        <div class="form-box">
            <h3 style="margin-top:0;">Manage Meals</h3>
            <p style="margin-top:0; color:#555;">Enter number of meals (0, 1, 2...)</p>
            <input type="date" id="mealDate" style="width: 200px;">

            <div style="display:flex; align-items:center; gap: 30px; margin: 15px 0;">
                <div style="display:flex; align-items:center;">
                    <label style="margin:0 10px 0 0; font-weight:bold;">Lunches:</label>
                    <input type="number" id="lunchCount" value="0" min="0" style="width: 80px; padding: 8px;">
                </div>
                <div style="display:flex; align-items:center;">
                    <label style="margin:0 10px 0 0; font-weight:bold;">Dinners:</label>
                    <input type="number" id="dinnerCount" value="0" min="0" style="width: 80px; padding: 8px;">
                </div>
            </div>

            <button class="btn-add" onclick="updateMeal()" style="width: auto; padding: 10px 30px;">Update Meal
                Plan</button>
            <div id="mealMsg" style="margin-top: 10px; font-weight: bold; text-align: center; min-height: 20px;"></div>
        </div>

        
        <div class="form-box">
            <h3 style="margin-top:0;">Submit Complaint</h3>
            <textarea id="complaintText" placeholder="Describe your issue..."
                style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; resize: none; margin-bottom: 15px;"></textarea>
            <button class="btn-add" onclick="submitComplaint()" style="width: auto; padding: 10px 30px;">Send
                Complaint</button>
            <div id="compMsg" style="margin-top: 10px; font-weight: bold; text-align: center; min-height: 20px;"></div>
        </div>

        
        <h3 style="margin-bottom: 10px;">Your Meal History</h3>
        <table>
            <tr>
                <th>Date</th>
                <th>Lunch Count</th>
                <th>Dinner Count</th>
            </tr>
            <?php
            $sql = "SELECT * FROM meals WHERE resident_name = '$current_user' ORDER BY date DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    
                    $l_status = $row['lunch'];
                    $d_status = $row['dinner'];

                    echo "<tr>
                        <td>{$row['date']}</td>
                        <td>$l_status</td>
                        <td>$d_status</td>
                      </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No meal plans found.</td></tr>";
            }
            ?>
        </table>
    </div>

    
    <script src="../Assets/resident.js"></script>

</body>

</html>