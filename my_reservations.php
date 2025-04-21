<?php
require_once 'config/db_connect.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's reservations
$query = "SELECT * FROM reservations WHERE user_id = ? ORDER BY reservation_date DESC, reservation_time DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Handle reservation cancellation
if (isset($_POST['cancel_reservation']) && isset($_POST['reservation_id'])) {
    $reservation_id = mysqli_real_escape_string($conn, $_POST['reservation_id']);
    
    // Check if the reservation belongs to the user
    $check_query = "SELECT * FROM reservations WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $reservation_id, $user_id);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        $reservation = mysqli_fetch_assoc($check_result);
        
        // Only allow cancellation for future reservations
        $reservation_datetime = new DateTime($reservation['reservation_date'] . ' ' . $reservation['reservation_time']);
        $now = new DateTime();
        
        if ($reservation_datetime > $now) {
            $update_query = "UPDATE reservations SET status = 'cancelled' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "i", $reservation_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_msg'] = "Reservation cancelled successfully.";
            } else {
                $_SESSION['error_msg'] = "Error cancelling reservation.";
            }
        } else {
            $_SESSION['error_msg'] = "Cannot cancel past reservations.";
        }
    }
    
    // Redirect to refresh the page
    header('Location: my_reservations.php');
    exit();
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Reservations</h2>
                <a href="reservation.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Reservation
                </a>
            </div>

            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php 
                    echo $_SESSION['success_msg'];
                    unset($_SESSION['success_msg']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php 
                    echo $_SESSION['error_msg'];
                    unset($_SESSION['error_msg']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Reservation ID</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Guests</th>
                                <th>Status</th>
                                <th>Special Requests</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($reservation = mysqli_fetch_assoc($result)): 
                                $reservation_datetime = new DateTime($reservation['reservation_date'] . ' ' . $reservation['reservation_time']);
                                $now = new DateTime();
                                $can_cancel = ($reservation_datetime > $now && $reservation['status'] != 'cancelled');
                            ?>
                                <tr>
                                    <td>#<?php echo $reservation['id']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($reservation['reservation_time'])); ?></td>
                                    <td><?php echo $reservation['num_guests']; ?> people</td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($reservation['status']) {
                                                'pending' => 'warning',
                                                'confirmed' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($reservation['special_requests'] ?? 'None'); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($can_cancel): ?>
                                            <form method="POST" action="" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                                <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                <input type="hidden" name="cancel_reservation" value="1">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> You haven't made any reservations yet.
                    <a href="reservation.php" class="alert-link">Make a reservation</a> to secure your table!
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-hide alerts after 5 seconds
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

<?php include 'includes/footer.php'; ?> 