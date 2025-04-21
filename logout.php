<?php
require_once 'config/db_connect.php';

// Unset user session variables
unset($_SESSION['user_id']);
unset($_SESSION['username']);
unset($_SESSION['role']);

// Destroy the session
session_destroy();

// Redirect to home page
header('Location: index.php');
exit();
?> 