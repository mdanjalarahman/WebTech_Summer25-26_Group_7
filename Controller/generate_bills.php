<?php
/**
 * Generate Bills Script
 * 
 * This page calculates the monthly bill for each resident.
 * Calculation Logic:
 * 1. Calculate Total Expenses / Total Meals = Cost Per Meal (Meal Rate)
 * 2. Resident's Cost = Resident's Total Meals * Meal Rate
 * 3. Resident's Balance = Resident's Cost - Resident's Total Deposits
 * 
 * Displays whether each resident has a Due amount or a Refund due.
 *
 * @package BachelorSystem
 * @subpackage Finance
 */

session_start();
include '../Model/config.php';

// Security Check: Only Supervisor can generate bills
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../Main page/index.php");
    exit();
}

// --- Get Total Expenses ---
$sql_exp = "SELECT SUM(amount) as total_expense FROM expenses";
$result_exp = $conn->query($sql_exp);
$total_expense = $result_exp->fetch_assoc()['total_expense'];
if ($total_expense == null)
    $total_expense = 0;

// --- Get Total Meals ---
// Sum the integer columns (lunch + dinner) directly
$sql_meal = "SELECT SUM(lunch + dinner) as total_meals FROM meals";
$result_meal = $conn->query($sql_meal);
$total_meals = $result_meal->fetch_assoc()['total_meals'];
if ($total_meals == null)
    $total_meals = 0;

// --- Calculate Cost Per Meal ---
$cost_per_meal = 0;
if ($total_meals > 0) {
    $cost_per_meal = $total_expense / $total_meals;
}

// --- Get All Residents (Includes Supervisors too as they eat) ---
$sql_users = "SELECT * FROM users WHERE role='resident' OR role='supervisor'";
$residents = $conn->query($sql_users);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Generate Bills</title>
    <link rel="stylesheet" href="../View/Assets/style.css">
    <style>
        /* Bills Page Specific Styles */
        .bill-container {
            width: 100%;
            height: 100vh;
            margin: 0;
            padding: 20px;
            background: white;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .summary-box {
            background-color: #e9ecef;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 5px solid #17a2b8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: right;
            /* Align numbers to right */
        }

        th {
            text-align: center;
            background-color: #f2f2f2;
            position: sticky;
            top: 0;
        }

        td:first-child {
            text-align: left;
            font-weight: bold;
        }

        .due {
            color: red;
            font-weight: bold;
        }

        .refund {
            color: green;
            font-weight: bold;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            margin-bottom: 50px;
        }
    </style>
</head>

<body>

    <div class="bill-container">
        <h2>Monthly Bill Calculation</h2>

        <div class="summary-box">
            <p><strong>Total Expense:</strong> <?php echo number_format($total_expense, 2); ?> BDT</p>
            <p><strong>Total Meals (All Residents):</strong> <?php echo $total_meals; ?></p>
            <p><strong>Cost Per Meal:</strong> <?php echo number_format($cost_per_meal, 2); ?> BDT</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Resident Name</th>
                    <th>Total Meals</th>
                    <th>Total Cost (Meals × Rate)</th>
                    <th>Total Deposited</th>
                    <th>Status (Due / Refund)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($residents->num_rows > 0) {
                    while ($user = $residents->fetch_assoc()) {
                        $name = $user['username'];

                        // A. Get User's Total Meals
                        $sql_user_meals = "SELECT SUM(lunch + dinner) as count FROM meals WHERE resident_name='$name'";
                        $res_user_meals = $conn->query($sql_user_meals);
                        $user_meal_count = $res_user_meals->fetch_assoc()['count'];
                        if ($user_meal_count == null)
                            $user_meal_count = 0;

                        // B. Get User's Total Deposit
                        $sql_user_dep = "SELECT SUM(amount) as total FROM deposits WHERE resident_name='$name'";
                        $res_user_dep = $conn->query($sql_user_dep);
                        $user_deposit = $res_user_dep->fetch_assoc()['total'];
                        if ($user_deposit == null)
                            $user_deposit = 0;

                        // C. Calculations
                        $user_total_cost = $user_meal_count * $cost_per_meal;
                        $balance = $user_total_cost - $user_deposit;

                        // D. Determine Status Text
                        if ($balance > 0) {
                            $status_text = "Due: " . number_format($balance, 2);
                            $class = "due";
                        } elseif ($balance < 0) {
                            $status_text = "Refund: " . number_format(abs($balance), 2);
                            $class = "refund";
                        } else {
                            $status_text = "Settled";
                            $class = "";
                        }

                        echo "<tr>
                            <td>$name</td>
                            <td>$user_meal_count</td>
                            <td>" . number_format($user_total_cost, 2) . "</td>
                            <td>" . number_format($user_deposit, 2) . "</td>
                            <td class='$class'>$status_text</td>
                          </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No residents found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <a href="../Dashboards/dashboard_supervisor.php" class="back-link">&larr; Back to Dashboard</a>
    </div>

</body>

</html>