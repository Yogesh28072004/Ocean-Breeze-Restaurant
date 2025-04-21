<?php
require_once 'config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit();
}

// Get form data
$total_amount = floatval($_POST['total_amount']);
$delivery_address = mysqli_real_escape_string($conn, $_POST['delivery_address']);
$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
$user_id = $_SESSION['user_id'];

// Get cart items from POST data
$cart_items = isset($_POST['cart_items']) ? json_decode($_POST['cart_items'], true) : [];
if (empty($cart_items)) {
    header('Location: cart.php?error=empty_cart');
    exit();
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Create order
    $order_query = "INSERT INTO orders (user_id, total_amount, payment_method, delivery_address, status, payment_status) 
                    VALUES (?, ?, ?, ?, 'pending', 'pending')";
    $stmt = mysqli_prepare($conn, $order_query);
    mysqli_stmt_bind_param($stmt, "idss", $user_id, $total_amount, $payment_method, $delivery_address);
    mysqli_stmt_execute($stmt);
    $order_id = mysqli_insert_id($conn);

    // Insert order items
    $items_query = "INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)";
    $items_stmt = mysqli_prepare($conn, $items_query);

    foreach ($cart_items as $item) {
        // Get current price from menu_items
        $price_query = "SELECT price FROM menu_items WHERE id = ?";
        $price_stmt = mysqli_prepare($conn, $price_query);
        mysqli_stmt_bind_param($price_stmt, "i", $item['id']);
        mysqli_stmt_execute($price_stmt);
        $price_result = mysqli_stmt_get_result($price_stmt);
        $current_price = mysqli_fetch_assoc($price_result)['price'];

        $quantity = intval($item['quantity']);
        $subtotal = $current_price * $quantity;
        
        mysqli_stmt_bind_param($items_stmt, "iiidd", $order_id, $item['id'], $quantity, $current_price, $subtotal);
        mysqli_stmt_execute($items_stmt);
    }

    // Commit transaction
    mysqli_commit($conn);

    // Redirect to success page
    header('Location: order_confirmation.php?order_id=' . $order_id);
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    header('Location: cart.php?error=order_failed');
    exit();
} 