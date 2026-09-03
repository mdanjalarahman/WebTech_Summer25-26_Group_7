<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
//check user if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Main page/index.php");
    exit();
}
include '../../Model/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../Assets/style.css">
</head>

<body>

    <div class="dashboard-container">
        <header>
            <div>
                <h2 style="margin:0;">Admin Dashboard</h2>
                <p style="margin:5px 0 0 0; color:#666;">Welcome, <?php echo $_SESSION['username']; ?>!</p>
            </div>
            <a href="../Main page/logout.php" class="logout-btn">Logout</a>
        </header>

        <div class="section">
            <h3>Manage Users & Roles</h3>
            <table>
                <tr>
                    <th>Username</th>
                    <th>Phone</th>
                    <th>Emg. Contact</th>
                    <th>NID</th>
                    <th>Occupation</th>
                    <th>Current Role</th>
                    <th>Change Role</th>
                    <th>Action</th>
                </tr>
                <?php
                $sql_users = "SELECT * FROM users WHERE role != 'admin'";
                $result_users = $conn->query($sql_users);
                while ($row = $result_users->fetch_assoc()) {
                    $u_id = $row['id'] ?? '';
                    $u_name = $row['username'] ?? '';
                    $u_role = $row['role'] ?? 'resident';

                    $phone = $row['phone'] ?? '';
                    $emergency_contact = $row['emergency_contact'] ?? '';
                    $nid = $row['nid'] ?? '';
                    $occupation = $row['occupation'] ?? '';

                    $dropdown = "<select onchange='updateRole($u_id, this.value)'>
                                <option value=''>-- Change Role --</option>
                                <option value='supervisor' " . ($u_role == 'supervisor' ? 'selected' : '') . ">Supervisor</option>
                                <option value='resident' " . ($u_role == 'resident' ? 'selected' : '') . ">Resident</option>
                              </select>";

                    $editBtn = "<button class='btn-sm' style='background-color:#ffc107; color:black; margin-right:5px;' onclick='openEditModal($u_id, \"" . htmlspecialchars($u_name) . "\", \"" . htmlspecialchars($phone) . "\", \"" . htmlspecialchars($emergency_contact) . "\", \"" . htmlspecialchars($nid) . "\", \"" . htmlspecialchars($occupation) . "\")'>Edit</button>";

                    if ($u_role == 'admin') {
                        $dropdown = "<span style='color: gray; font-style: italic;'>(Cannot change)</span>";
                        $actionButton = "<span style='color:#999; font-size:0.9em; font-weight:bold;'>(Protected Admin)</span>";
                    } else {
                        $actionButton = $editBtn . "<button class='btn-sm' onclick='deleteUser($u_id)'>Delete User</button>";
                    }

                    echo "<tr>
                        <td>$u_name</td>
                        <td>$phone</td>
                        <td>$emergency_contact</td>
                        <td>$nid</td>
                        <td>$occupation</td>
                        <td><strong>$u_role</strong></td>
                        <td>$dropdown</td>
                        <td>$actionButton</td>
                      </tr>";
                }
                ?>
            </table>
        </div>

        <div class="section">
            <h3>Resident Complaints</h3>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Resident</th>
                    <th>Message</th>
                    <th>Action</th>
                </tr>
                <?php
                $sql_comp = "SELECT * FROM complaints ORDER BY created_at DESC";
                $res_comp = $conn->query($sql_comp);
                if ($res_comp->num_rows > 0) {
                    while ($row = $res_comp->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['created_at']}</td>
                            <td>{$row['resident_name']}</td>
                            <td>{$row['message']}</td>
                            <td><button class='btn-sm' onclick='deleteComplaint({$row['id']})'>Resolved</button></td>
                          </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No complaints found.</td></tr>";
                }
                ?>
            </table>
        </div>

        <div class="section">
            <h3>Publish Notice</h3>
            <div class="notice-form">
                <input type="text" id="noticeTitle" placeholder="Notice Title" required>
                <textarea id="noticeMessage" placeholder="Type your announcement here..." required></textarea>
                <button class="logout-btn" style="background-color: #28a745; width: auto; padding: 10px 30px;"
                    onclick="postNotice()">Publish Notice</button>
                <div id="noticeMsg"></div>
            </div>

            <div class="notice-list">
                <?php
                $sql_not = "SELECT * FROM notices WHERE created_at > NOW() - INTERVAL 24 HOUR ORDER BY created_at DESC";
                $res_not = $conn->query($sql_not);

                if ($res_not->num_rows > 0) {
                    while ($row = $res_not->fetch_assoc()) {
                        echo "<div class='notice-item' style='display:flex; justify-content:space-between; align-items:flex-start;'>
                            <div>
                                <h4 style='margin:0 0 5px 0;'>" . htmlspecialchars($row['title']) . "</h4>
                                <p style='margin:5px 0;'>" . htmlspecialchars($row['message']) . "</p>
                                <div class='notice-date'>Posted: " . $row['created_at'] . "</div>
                            </div>
                            <button class='btn-sm' style='background-color:#dc3545; padding:5px 8px; font-size:0.8em;' onclick='deleteNotice({$row['id']})'>Delete</button>
                          </div>";
                    }
                } else {
                    echo "<p style='color:#666; font-style:italic;'>No active notices (last 24h).</p>";
                }
                ?>
            </div>

            <div style="margin-top: 20px; border-top: 2px dashed #eee; padding-top: 10px;">
                <button class="logout-btn"
                    style="background-color: #6c757d; width: auto; padding: 8px 15px; font-size: 0.9em;"
                    onclick="toggleArchive()">View Archived Notices</button>
                <div id="archiveContainer" style="display: none; margin-top: 15px;">
                    <h4 style="color: #666; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Archived Notices (Older
                        than 24h)</h4>
                    <div id="archiveList" class="notice-list"></div>
                </div>
            </div>
        </div>

    </div>

    <div id="editUserModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div style="background:white; padding:20px; border-radius:8px; width:400px; position:relative;">
            <h3 style="margin-top:0;">Edit User Details</h3>
            <span onclick="closeEditModal()"
                style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px;">&times;</span>

            <input type="hidden" id="edit_user_id">
            <label>Phone:</label>
            <input type="text" id="edit_phone">
            <label>Emergency Contact:</label>
            <input type="text" id="edit_emg">
            <label>NID:</label>
            <input type="text" id="edit_nid">
            <label>Occupation:</label>
            <input type="text" id="edit_occ">

            <button class="btn-sm" style="background-color:#28a745; width:100%; font-size:14px;"
                onclick="saveUserDetails()">Save Changes</button>
        </div>
    </div>

    <script src="../Assets/admin.js"></script>

</body>

</html>