<?php
// Only start session if one hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Buffer the output
ob_start();

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $valid_statuses = ['confirmed', 'delivered', 'cancelled'];
    if (in_array($status, $valid_statuses)) {
        $update_query = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "Order #" . $order_id . " has been marked as " . $status;
        } else {
            $_SESSION['error_msg'] = "Error updating order status: " . mysqli_error($conn);
        }
        
        // Clear the output buffer and redirect
        ob_end_clean();
        header('Location: orders.php');
        exit();
    }
}

require_once 'includes/admin_header.php';

// Fetch orders with user information
$query = "SELECT o.*, u.username, u.full_name, u.email, u.phone 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $query);

// Debug information
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<div class="container-fluid px-4">
    <div class="text-center my-4">
        <h1 class="display-5 mb-2">Manage Orders</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Orders</li>
            </ol>
        </nav>
    </div>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_msg'];
            unset($_SESSION['success_msg']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error_msg'];
            unset($_SESSION['error_msg']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Orders List
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="ordersTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($order['full_name']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($order['username']); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($order['email']); ?><br>
                                    <?php if ($order['phone']): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['phone']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php 
                                        $statusClass = match($order['status']) {
                                            'pending' => 'warning',
                                            'confirmed' => 'success',
                                            'delivered' => 'info',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                        $statusIcon = match($order['status']) {
                                            'pending' => '<i class="fas fa-clock"></i>',
                                            'confirmed' => '<i class="fas fa-check-circle"></i>',
                                            'delivered' => '<i class="fas fa-truck"></i>',
                                            'cancelled' => '<i class="fas fa-times-circle"></i>',
                                            default => ''
                                        };
                                        $statusMessage = match($order['status']) {
                                            'confirmed' => 'Order Confirmed - In Progress',
                                            'cancelled' => 'Order Cancelled',
                                            default => ucfirst($order['status'])
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo $statusIcon; ?> <?php echo $statusMessage; ?>
                                        </span>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="new_status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()" <?php echo $order['status'] == 'cancelled' ? 'disabled' : ''; ?>>
                                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </div>
                                    <?php if($order['status'] == 'confirmed'): ?>
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-info-circle"></i> Order is being prepared
                                        </small>
                                    <?php elseif($order['status'] == 'cancelled'): ?>
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-info-circle"></i> Order was cancelled
                                        </small>
                                    <?php endif; ?>
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
// Initialize DataTables
$(document).ready(function() {
    $('#ordersTable').DataTable({
        order: [[6, 'desc']], // Sort by date column by default
        pageLength: 25
    });
});

function viewOrderDetails(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    const contentDiv = document.getElementById('orderDetailsContent');
    
    modal.show();
    contentDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading order details...</div>';
    
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
                    <td>${CURRENCY_SYMBOL}${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td>${CURRENCY_SYMBOL}${subtotal.toFixed(2)}</td>
                </tr>`;
            });
            
            html += `<tr class="table-dark">
                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                <td><strong>${CURRENCY_SYMBOL}${total.toFixed(2)}</strong></td>
            </tr>`;
            
            html += '</tbody></table></div>';
            contentDiv.innerHTML = html;
        })
        .catch(error => {
            contentDiv.innerHTML = '<div class="alert alert-danger">Error loading order details. Please try again.</div>';
            console.error('Error:', error);
        });
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>

<?php require_once 'includes/admin_footer.php'; ?> 