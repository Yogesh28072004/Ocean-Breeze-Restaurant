<?php
require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

// Handle Reservation Status Update
if (isset($_POST['update_status'])) {
    $reservation_id = (int)$_POST['reservation_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE reservations SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $reservation_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_msg = "Reservation status updated successfully!";
    } else {
        $error_msg = "Error updating reservation status.";
    }
}

// Fetch all reservations with user details
$query = "SELECT r.*, u.full_name, u.phone, u.email 
          FROM reservations r 
          LEFT JOIN users u ON r.user_id = u.id 
          ORDER BY r.reservation_date DESC, r.reservation_time DESC";
$reservations = mysqli_query($conn, $query);

// Get status counts for statistics
$status_query = "SELECT status, COUNT(*) as count FROM reservations GROUP BY status";
$status_result = mysqli_query($conn, $status_query);
$status_counts = [];
while ($row = mysqli_fetch_assoc($status_result)) {
    $status_counts[$row['status']] = $row['count'];
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2>Manage Reservations</h2>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h2 class="card-text"><?php echo isset($status_counts['pending']) ? $status_counts['pending'] : 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Confirmed</h5>
                    <h2 class="card-text"><?php echo isset($status_counts['confirmed']) ? $status_counts['confirmed'] : 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Cancelled</h5>
                    <h2 class="card-text"><?php echo isset($status_counts['cancelled']) ? $status_counts['cancelled'] : 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Completed</h5>
                    <h2 class="card-text"><?php echo isset($status_counts['completed']) ? $status_counts['completed'] : 0; ?></h2>
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
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Guests</th>
                            <th>Special Requests</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reservation = mysqli_fetch_assoc($reservations)): ?>
                            <tr>
                                <td><?php echo $reservation['id']; ?></td>
                                <td><?php echo htmlspecialchars($reservation['full_name'] ?? ''); ?></td>
                                <td>
                                    Phone: <?php echo htmlspecialchars($reservation['phone'] ?? 'N/A'); ?><br>
                                    Email: <?php echo htmlspecialchars($reservation['email'] ?? 'N/A'); ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($reservation['reservation_time'])); ?></td>
                                <td><?php echo $reservation['num_guests']; ?></td>
                                <td><?php echo htmlspecialchars($reservation['special_requests'] ?? ''); ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch ($reservation['status']) {
                                        case 'pending':
                                            $status_class = 'bg-primary';
                                            break;
                                        case 'confirmed':
                                            $status_class = 'bg-success';
                                            break;
                                        case 'cancelled':
                                            $status_class = 'bg-danger';
                                            break;
                                        case 'completed':
                                            $status_class = 'bg-secondary';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                            Update Status
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php if ($reservation['status'] != 'confirmed'): ?>
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                        <input type="hidden" name="status" value="confirmed">
                                                        <button type="submit" name="update_status" class="dropdown-item text-success">
                                                            <i class="fas fa-check"></i> Confirm
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($reservation['status'] != 'cancelled'): ?>
                                                <li>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" name="update_status" class="dropdown-item text-danger">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($reservation['status'] != 'completed' && $reservation['status'] != 'cancelled'): ?>
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" name="update_status" class="dropdown-item text-secondary">
                                                            <i class="fas fa-check-double"></i> Mark as Completed
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
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