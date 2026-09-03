<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Registration</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="login-container">
        <h2>New Resident Registration</h2>

        <form id="registerForm" style="text-align: center;">
            <input type="text" id="username" placeholder="Choose a Username" required>
            <input type="password" id="password" placeholder="Choose a Password" required>
            <!-- NEW FIELDS -->
            <input type="text" id="phone" placeholder="Phone Number" required>
            <input type="text" id="emergency_contact" placeholder="Emergency Contact (Name & Phone)" required>
            <input type="text" id="nid" placeholder="NID Number" required>
            <input type="text" id="occupation" placeholder="Occupation" required>

            <!-- The Register Button with Blue Style -->
            <button type="submit" class="btn-login">Register</button>
        </form>

        <!-- Back to Login Link -->
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="text-decoration: none; color: #007bff; font-weight: bold;">Back to Login</a>
        </div>

        <!-- Message area -->
        <div id="message" style="margin-top: 10px; text-align: center; font-weight: bold;"></div>
    </div>

    <script src="register_script.js?v=<?php echo time(); ?>"></script>
</body>

</html>