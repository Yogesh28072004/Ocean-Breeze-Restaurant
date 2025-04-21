<?php
require_once 'config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get order ID from request
if (!isset($_GET['order_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Order ID is required']);
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Fetch order items (only if the order belongs to the current user)
$query = "SELECT oi.*, mi.name, mi.price as unit_price
          FROM order_items oi
          JOIN menu_items mi ON oi.menu_item_id = mi.id
          JOIN orders o ON oi.order_id = o.id
          WHERE oi.order_id = ? AND o.user_id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = [
        'name' => $row['name'],
        'quantity' => $row['quantity'],
        'unit_price' => $row['unit_price'],
        'subtotal' => $row['quantity'] * $row['unit_price']
    ];
}

// Set JSON header
header('Content-Type: application/json');
echo json_encode($items); 