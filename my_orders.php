<?php
require_once 'config/db_connect.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user's orders
$user_id = $_SESSION['user_id'];
$query = "SELECT o.*, 
          GROUP_CONCAT(CONCAT(oi.quantity, 'x ', mi.name) SEPARATOR ', ') as items
          FROM orders o
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id
          WHERE o.user_id = ?
          GROUP BY o.id
          ORDER BY o.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">My Orders</h2>
            
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Order ID</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td>
                                        <small><?php echo htmlspecialchars($order['items']); ?></small>
                                    </td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($order['status']) {
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($order['payment_status']) {
                                                'pending' => 'warning',
                                                'paid' => 'success',
                                                'failed' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> You haven't placed any orders yet.
                    <a href="menu.php" class="alert-link">Browse our menu</a> to place your first order!
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetailsContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    const contentDiv = document.getElementById('orderDetailsContent');
    
    // Show modal with loading state
    modal.show();
    contentDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading order details...</div>';
    
    // Fetch order details
    fetch(`get_order_items.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            let html = '<div class="table-responsive"><table class="table">';
            html += '<thead><tr><th>Item</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr></thead><tbody>';
            
            let total = 0;
            data.forEach(item => {
                const subtotal = parseFloat(item.unit_price) * parseInt(item.quantity);
                total += subtotal;
                html += `<tr>
                    <td>${item.name}</td>
                    <td>${item.quantity}</td>
                    <td>$${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td>$${subtotal.toFixed(2)}</td>
                </tr>`;
            });
            
            html += `<tr class="table-dark">
                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                <td><strong>$${total.toFixed(2)}</strong></td>
            </tr>`;
            
            html += '</tbody></table></div>';
            contentDiv.innerHTML = html;
        })
        .catch(error => {
            contentDiv.innerHTML = '<div class="alert alert-danger">Error loading order details. Please try again.</div>';
            console.error('Error:', error);
        });
}
</script>

<?php include 'includes/footer.php'; ?> 