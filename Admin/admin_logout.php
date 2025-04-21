<?php
require_once '../config/db_connect.php';

// Unset admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_name']);

// Redirect to admin login page
header('Location: admin_login.php');
exit(); 