<?php
require_once '../config/db_connect.php';

// Test database connection
echo "<h2>Database Connection Test:</h2>";
if ($conn->ping()) {
    echo "Database connection is working.<br><br>";
} else {
    echo "Database connection failed.<br><br>";
}

// Check if admin_users table exists
echo "<h2>Admin Table Check:</h2>";
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'admin_users'");
if (mysqli_num_rows($table_check) > 0) {
    echo "admin_users table exists.<br><br>";
} else {
    echo "admin_users table does not exist.<br><br>";
}

// Check admin users in database
echo "<h2>Admin Users in Database:</h2>";
$query = "SELECT id, username, password, email FROM admin_users";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: " . $row['id'] . "<br>";
        echo "Username: " . $row['username'] . "<br>";
        echo "Password Hash: " . $row['password'] . "<br>";
        echo "Email: " . $row['email'] . "<br><br>";
    }
} else {
    echo "No admin users found in database.<br><br>";
}

// Create new admin user if none exists
echo "<h2>Creating Default Admin:</h2>";
$admin_username = 'admin';
$admin_password = 'admin123';
$admin_email = 'admin@example.com';
$admin_fullname = 'Administrator';

// Hash the password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Check if admin already exists
$check_query = "SELECT id FROM admin_users WHERE username = 'admin'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    $insert_query = "INSERT INTO admin_users (username, password, full_name, email) 
                     VALUES ('$admin_username', '$hashed_password', '$admin_fullname', '$admin_email')";
    
    if (mysqli_query($conn, $insert_query)) {
        echo "Default admin user created successfully.<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error creating admin user: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Admin user already exists.<br>";
}

// Test password verification
echo "<h2>Password Verification Test:</h2>";
$test_password = 'admin123';
$query = "SELECT password FROM admin_users WHERE username = 'admin'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
    if (password_verify($test_password, $admin['password'])) {
        echo "Password verification successful!<br>";
    } else {
        echo "Password verification failed!<br>";
    }
} else {
    echo "Could not find admin user for password verification.<br>";
}
?> 