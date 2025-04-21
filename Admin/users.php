<?php
require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

// Handle Delete User
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    // First check if user has any orders or reservations
    $check_orders = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
    $check_reservations = "SELECT COUNT(*) as count FROM reservations WHERE user_id = ?";
    
    $stmt = mysqli_prepare($conn, $check_orders);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $orders_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
    
    $stmt = mysqli_prepare($conn, $check_reservations);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $reservations_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
    
    if ($orders_count > 0 || $reservations_count > 0) {
        $error_msg = "Cannot delete user. User has existing orders or reservations.";
    } else {
        $delete_query = "DELETE FROM users WHERE id = ? AND role != 'admin'";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_affected_rows($conn) > 0) {
                $success_msg = "User deleted successfully!";
            } else {
                $error_msg = "Cannot delete admin users.";
            }
        } else {
            $error_msg = "Error deleting user. Please try again.";
        }
    }
}

// Get user statistics
$stats_query = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
    SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as regular_users
    FROM users";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Fetch all users with their order and reservation counts
$query = "SELECT u.*, 
    COUNT(DISTINCT o.id) as order_count,
    COUNT(DISTINCT r.id) as reservation_count,
    MAX(o.created_at) as last_order,
    MAX(r.created_at) as last_reservation
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    LEFT JOIN reservations r ON u.id = r.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC";
$users = mysqli_query($conn, $query);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2>User Management</h2>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2 class="card-text"><?php echo $stats['total_users']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Regular Users</h5>
                    <h2 class="card-text"><?php echo $stats['regular_users']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Admin Users</h5>
                    <h2 class="card-text"><?php echo $stats['admin_count']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Activity</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?><br>
                                    <?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'info' : 'secondary'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    Orders: <?php echo $user['order_count']; ?><br>
                                    Reservations: <?php echo $user['reservation_count']; ?><br>
                                    <?php if ($user['last_order']): ?>
                                        Last Order: <?php echo date('M d, Y', strtotime($user['last_order'])); ?><br>
                                    <?php endif; ?>
                                    <?php if ($user['last_reservation']): ?>
                                        Last Reservation: <?php echo date('M d, Y', strtotime($user['last_reservation'])); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['role'] != 'admin'): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }, 5000);
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?> 