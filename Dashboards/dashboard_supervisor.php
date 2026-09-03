<?php

session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../Main page/index.php");
    exit();
}
include '../../Model/config.php';
$current_user = $_SESSION['username'];



$today = date('Y-m-d');
$sql_today_meals = "SELECT * FROM meals WHERE date = '$today'";
$res_today = $conn->query($sql_today_meals);

$total_lunch = 0;
$total_dinner = 0;

if ($res_today->num_rows > 0) {
    while ($row = $res_today->fetch_assoc()) {
        $total_lunch += $row['lunch'];
        $total_dinner += $row['dinner'];
    }
}
$total_meals = $total_lunch + $total_dinner;

$sql_users = "SELECT username FROM users WHERE role IN ('resident', 'supervisor') ORDER BY username ASC";
$res_users = $conn->query($sql_users);
$user_options = "";
if ($res_users->num_rows > 0) {
    while ($u_row = $res_users->fetch_assoc()) {
        $user_options .= "<option value='" . htmlspecialchars($u_row['username']) . "'>" . htmlspecialchars($u_row['username']) . "</option>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard</title>
    <link rel="stylesheet" href="../Assets/style.css?v=<?php echo time(); ?>">
</head>

<body>

    <div class="dashboard-container">
        <div class="header-bar">
            <div>
                <h2>Supervisor Dashboard</h2>
                <p style="margin:0; color:#666;">Welcome, <?php echo $current_user; ?>! (Supervisor)</p>
            </div>
            <div class="header-right" style="display:flex; align-items:center; gap:10px;">
                <?php
                $sql_notices = "SELECT * FROM notices WHERE created_at > NOW() - INTERVAL 24 HOUR ORDER BY created_at DESC";
                $result_notices = $conn->query($sql_notices);
                $notice_count = $result_notices->num_rows;
                ?>
                <div class="notification-container">
                    <button class="notification-btn" onclick="toggleNotifications()">
                        Notice
                        
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


                <button class="btn-sm"
                    style="background:#dc3545; color:white; border:none; padding:8px 12px; border-radius:4px; font-weight:bold; cursor:pointer;"
                    onclick="closePeriod()"> Close Period</button>
                <button class="btn-sm"
                    style="background:#ff9800; color:white; border:none; padding:8px 12px; border-radius:4px; font-weight:bold; cursor:pointer;"
                    onclick="resetPeriodData()">Reset Data</button>
                <button class="btn-sm"
                    style="background:#28a745; color:white; border:none; padding:8px 12px; border-radius:4px; font-weight:bold; cursor:pointer;"
                    onclick="undoReset()"> Undo Reset</button>
                <a href="../../Controller/archives.php" style="background-color: #6c757d;">View Archives</a>
                <a href="../../Controller/generate_bills.php" style="background-color: #17a2b8;">Generate Bills</a>
                <a href="../Main page/logout.php" style="background-color: #dc3545;">Logout</a>
            </div>
        </div>

        <div class="daily-overview">
            <h4 style="margin-top:0; color:#0d47a1; display:flex; justify-content:space-between;">
                <span>Today's Meals (<?php echo $today; ?>)</span>
            </h4>
            <div style="display:flex; gap: 20px;">
                <p><strong>Lunch:</strong> <?php echo $total_lunch; ?></p>
                <p><strong>Dinner:</strong> <?php echo $total_dinner; ?></p>
                <p><strong>Total Meals:</strong> <?php echo $total_meals; ?></p>
            </div>
        </div>

        <h3>Official Duties</h3>
        <div class="grid-container">
            <div class="form-box">
                <h4>Record Expense</h4>
                <input type="text" id="exp_desc" placeholder="Description">
                <input type="number" id="exp_amount" placeholder="Amount (BDT)">
                <select id="exp_category">
                    <option value="Grocery">Grocery</option>
                    <option value="Utility">Utility Bill</option>
                    <option value="Others">Others</option>
                </select>
                <input type="date" id="exp_date">
                <button class="btn-add" onclick="addExpense()">Add Expense</button>
                <p id="exp_msg"></p>
            </div>

            <div class="form-box">
                <h4>Record Deposit</h4>
                <select id="dep_name">
                    <option value="">-- Select Resident --</option>
                    <?php echo $user_options; ?>
                </select>
                <input type="number" id="dep_amount" placeholder="Amount (BDT)">
                <input type="date" id="dep_date">
                <button class="btn-add" onclick="addDeposit()">Add Deposit</button>
                <p id="dep_msg"></p>
            </div>
        </div>

        <h3>Manage Resident Meals</h3>
        <div class="section"
            style="background:#fff; padding:20px; border-radius:6px; border:1px solid #ddd; margin-bottom:30px;">
            <div style="display:flex; gap:10px; align-items:center;">
                <label>Select Resident:</label>
                <select id="meal_target_user" style="padding:8px;">
                    <option value="">-- Select --</option>
                    <?php
                    $res_users->data_seek(0);
                    while ($u_row = $res_users->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($u_row['username']) . "'>" . htmlspecialchars($u_row['username']) . "</option>";
                    }
                    ?>
                </select>
                <button class="btn-add" style="width:auto; margin:0;" onclick="loadResidentMeals()">Load
                    History</button>
            </div>

            <div id="residentMealTable" style="margin-top:20px;">
                
            </div>
        </div>

        <div class="personal-section">
            <h3>Personal Resident Duties</h3>
            <div class="grid-container">
                <div class="form-box">
                    <h4>Manage Your Meals</h4>
                    <input type="date" id="mealDate">
                    <div style="margin: 15px 0; display: flex; align-items: center; gap: 20px;">
                        <div style="display:flex; align-items:center;">
                            <label style="margin:0 10px 0 0;">Lunches:</label>
                            <input type="number" id="lunchCount" value="0" min="0" style="width: 70px; padding: 8px;">
                        </div>
                        <div style="display:flex; align-items:center;">
                            <label style="margin:0 10px 0 0;">Dinners:</label>
                            <input type="number" id="dinnerCount" value="0" min="0" style="width: 70px; padding: 8px;">
                        </div>
                    </div>
                    <button class="btn-personal" onclick="updateMeal()">Update My Meal</button>
                    <p id="mealMsg"></p>
                </div>

                <!-- Personal Complaint Form -->
                <div class="form-box">
                    <h4>Submit Complaint</h4>
                    <textarea id="complaintText" placeholder="Describe your issue..."></textarea>
                    <button class="btn-personal" onclick="submitComplaint()">Send Complaint</button>
                    <p id="compMsg"></p>
                </div>
            </div>

            <!-- Personal Meal History -->
            <h4>Your Meal History</h4>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Lunch</th>
                    <th>Dinner</th>
                </tr>
                <?php
                $sql = "SELECT * FROM meals WHERE resident_name = '$current_user' ORDER BY date DESC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Just show the number. 0 means No, 1+ means Yes/Extra
                        $l_status = $row['lunch'];
                        $d_status = $row['dinner'];

                        echo "<tr><td>{$row['date']}</td><td>$l_status</td><td>$d_status</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No meal plans found.</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- RECENT LOGS (With Delete Buttons) -->
        <h3>Recent Official Logs</h3>

        <!-- Recent Expenses -->
        <h4>Expenses</h4>
        <table style="margin-bottom: 20px;">
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
            <?php
            $sql = "SELECT * FROM expenses ORDER BY date DESC LIMIT 5";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['date']}</td>
                        <td>{$row['description']}</td>
                        <td>{$row['amount']}</td>
                        <td>
                            <button class='btn-sm' style='background-color:#ffc107; color:black;' onclick='openEditExpenseModal({$row['id']}, \"{$row['date']}\", \"{$row['description']}\", \"{$row['amount']}\", \"{$row['category']}\")'>Edit</button>
                            <button class='btn-sm' onclick='deleteEntry({$row['id']}, \"expense\")'>Del</button>
                        </td>
                      </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No data.</td></tr>";
            }
            ?>
        </table>

        <!-- Recent Deposits -->
        <h4>Deposits</h4>
        <table>
            <tr>
                <th>Date</th>
                <th>Resident</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
            <?php
            $sql = "SELECT * FROM deposits ORDER BY date DESC LIMIT 5";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['date']}</td>
                        <td>{$row['resident_name']}</td>
                        <td>{$row['amount']}</td>
                        <td>
                             <button class='btn-sm' style='background-color:#ffc107; color:black;' onclick='openEditDepositModal({$row['id']}, \"{$row['date']}\", \"{$row['resident_name']}\", \"{$row['amount']}\")'>Edit</button>
                             <button class='btn-sm' onclick='deleteEntry({$row['id']}, \"deposit\")'>Del</button>
                        </td>
                      </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No data.</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- EDIT EXPENSE MODAL -->
    <div id="editExpenseModal" class="modal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div style="background:white; padding:20px; border-radius:5px; width:300px;">
            <h4>Edit Expense</h4>
            <input type="hidden" id="edit_exp_id">
            <label>Description:</label>
            <input type="text" id="edit_exp_desc" style="width:100%; margin-bottom:10px;">
            <label>Amount:</label>
            <input type="number" id="edit_exp_amount" style="width:100%; margin-bottom:10px;">
            <label>Category:</label>
            <select id="edit_exp_category" style="width:100%; margin-bottom:10px;">
                <option value="Grocery">Grocery</option>
                <option value="Utility">Utility Bill</option>
                <option value="Others">Others</option>
            </select>
            <label>Date:</label>
            <input type="date" id="edit_exp_date" style="width:100%; margin-bottom:10px;">
            <div style="display:flex; justify-content:space-between;">
                <button class="btn-personal" style="width:48%;" onclick="updateExpense()">Update</button>
                <button class="logout-btn" style="width:48%;" onclick="closeEditExpenseModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- EDIT DEPOSIT MODAL -->
    <div id="editDepositModal" class="modal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div style="background:white; padding:20px; border-radius:5px; width:300px;">
            <h4>Edit Deposit</h4>
            <input type="hidden" id="edit_dep_id">
            <label>Resident:</label>
            <select id="edit_dep_name" style="width:100%; margin-bottom:10px;">
                <option value="">-- Select Resident --</option>
                <?php
                // Reset pointer for second loop
                $res_users->data_seek(0);
                while ($u_row = $res_users->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($u_row['username']) . "'>" . htmlspecialchars($u_row['username']) . "</option>";
                }
                ?>
            </select>
            <label>Amount:</label>
            <input type="number" id="edit_dep_amount" style="width:100%; margin-bottom:10px;">
            <label>Date:</label>
            <input type="date" id="edit_dep_date" style="width:100%; margin-bottom:10px;">
            <div style="display:flex; justify-content:space-between;">
                <button class="btn-personal" style="width:48%;" onclick="updateDeposit()">Update</button>
                <button class="logout-btn" style="width:48%;" onclick="closeEditDepositModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- EDIT MEAL MODAL (SUPERVISOR) -->
    <div id="editMealModal" class="modal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div style="background:white; padding:20px; border-radius:5px; width:300px;">
            <h4>Edit Resident Meal</h4>
            <input type="hidden" id="edit_meal_user">
            <input type="hidden" id="edit_meal_date">
            <!-- Date is key, so typically we don't edit it, but we need it for WHERE clause -->

            <p><strong>Resident:</strong> <span id="disp_meal_user"></span></p>

            <div style="margin-bottom:15px;">
                <label>Date:</label>
                <input type="date" id="edit_meal_new_date" style="width:100%; padding:8px; margin-top:5px;">
            </div>

            <div style="margin: 15px 0; display: flex; align-items: center; gap: 20px;">
                <div style="display:flex; align-items:center;">
                    <label style="margin:0 10px 0 0;">Lunch:</label>
                    <input type="number" id="edit_lunch_count" min="0" style="width: 70px; padding: 8px;">
                </div>
                <div style="display:flex; align-items:center;">
                    <label style="margin:0 10px 0 0;">Dinner:</label>
                    <input type="number" id="edit_dinner_count" min="0" style="width: 70px; padding: 8px;">
                </div>
            </div>

            <div style="display:flex; justify-content:space-between;">
                <button class="btn-personal" style="width:48%;" onclick="updateTargetMeal()">Update</button>
                <button class="logout-btn" style="width:48%;" onclick="closeEditMealModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Main JavaScript for Supervisor -->
    <script src="../Assets/supervisor.js"></script>

</body>

</html>