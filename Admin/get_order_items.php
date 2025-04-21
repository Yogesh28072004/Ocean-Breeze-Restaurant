<?php
require_once '../config/db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('Missing order ID');
}

$order_id = intval($_GET['id']);

$query = "SELECT oi.*, m.name, m.price 
          FROM order_items oi 
          JOIN menu_items m ON oi.menu_item_id = m.id 
          WHERE oi.order_id = $order_id";
$result = mysqli_query($conn, $query);

$items = array();
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = array(
        'name' => $row['name'],
        'quantity' => $row['quantity'],
        'price' => $row['price']
    );
}

header('Content-Type: application/json');
echo json_encode($items);