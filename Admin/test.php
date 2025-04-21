<?php
require_once '../config/db_connect.php';

echo "<h2>Session Debug Information:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Database Connection Test:</h2>";
if ($conn->ping()) {
    echo "Database connection is working.<br>";
} else {
    echo "Database connection failed.<br>";
}

echo "<h2>Admin User Check:</h2>";
$query = "SELECT * FROM users WHERE role = 'admin'";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    echo "Admin user exists in database.<br>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Admin username: " . htmlspecialchars($row['username']) . "<br>";
    }
} else {
    echo "No admin user found in database.<br>";
}
?> 