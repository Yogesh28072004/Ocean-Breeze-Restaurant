<?php
require_once '../config/db_connect.php';

// Admin credentials
$admin_username = 'admin';
$admin_password = 'admin123';
$admin_email = 'admin@example.com';
$admin_fullname = 'Administrator';

// Create a new password hash
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Update admin password
$update_query = "UPDATE admin_users SET password = '$hashed_password' WHERE username = 'admin'";

if (mysqli_query($conn, $update_query)) {
    echo "<h2>Admin Password Reset Successful!</h2>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<br>You can now log in with these credentials.<br>";
    echo "<br><a href='admin_login.php'>Go to Admin Login</a>";
} else {
    echo "Error updating admin password: " . mysqli_error($conn);
}

// Verify the new password
$verify_query = "SELECT password FROM admin_users WHERE username = 'admin'";
$result = mysqli_query($conn, $verify_query);

if ($result && mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
    if (password_verify($admin_password, $admin['password'])) {
        echo "<br><br>Password verification test: SUCCESSFUL";
    } else {
        echo "<br><br>Password verification test: FAILED";
    }
}
?> 