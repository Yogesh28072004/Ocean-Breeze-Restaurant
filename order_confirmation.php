<?php
require_once 'config/db_connect.php';

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$order_id = intval($_GET['order_id']);

// Get order details with user information
$order_query = "SELECT o.*, u.full_name, u.email, u.username 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_result);

// Get order items
$items_query = "SELECT oi.*, m.name, m.price 
                FROM order_items oi 
                JOIN menu_items m ON oi.menu_item_id = m.id 
                WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .confirmation-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
            text-align: center;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .confirmation-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 40px 30px;
        }

        .confirmation-header i {
            font-size: 5rem;
            margin-bottom: 20px;
            animation: checkmark 0.5s ease-in-out;
        }

        @keyframes checkmark {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }

        .confirmation-body {
            padding: 30px;
        }

        .order-number {
            font-size: 1.2rem;
            color: #6c757d;
            margin: 20px 0;
        }

        .user-name {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .btn-continue {
            background: #4CAF50;
            color: white;
            padding: 15px 40px;
            border-radius: 30px;
            border: none;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-continue:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            color: white;
        }
    </style>
</head>
<body>
    <div class="confirmation-card">
        <div class="confirmation-header">
            <i class="fas fa-check-circle"></i>
            <h2>Order Confirmed!</h2>
        </div>
        <div class="confirmation-body">
            <div class="user-name">
                Thank you, <?php echo htmlspecialchars($order['full_name']); ?>!
            </div>
            <p class="lead">Your order has been successfully placed.</p>
            <div class="order-number">
                Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
            </div>
            <p>We'll start preparing your delicious meal right away!</p>
            <a href="menu.php" class="btn btn-continue">
                <i class="fas fa-utensils me-2"></i>Continue Shopping
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 