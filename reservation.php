<?php
require_once 'config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include header after session check
require_once 'includes/header.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $time = mysqli_real_escape_string($conn, $_POST['time']);
    $guests = (int)$_POST['guests'];
    $special_requests = mysqli_real_escape_string($conn, $_POST['special_request']);
    
    // Validate date and time
    $datetime = new DateTime("$date $time");
    $now = new DateTime();
    
    if ($datetime <= $now) {
        $error = 'Please select a future date and time';
    } else {
        // Check if the selected time slot is available
        $check_query = "SELECT * FROM reservations 
                       WHERE reservation_date = ? 
                       AND reservation_time = ? 
                       AND status != 'cancelled'";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ss", $date, $time);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) >= 5) { // Assuming max 5 reservations per time slot
            $error = 'Selected time slot is fully booked. Please choose another time.';
        } else {
            // Insert reservation
            $user_id = $_SESSION['user_id'];
            $insert_query = "INSERT INTO reservations (user_id, reservation_date, reservation_time, num_guests, special_requests, status) 
                           VALUES (?, ?, ?, ?, ?, 'pending')";
            
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "issis", $user_id, $date, $time, $guests, $special_requests);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Reservation submitted successfully! We will confirm your reservation shortly.';
            } else {
                $error = 'Failed to submit reservation. Please try again.';
            }
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body">
                   
                    <h2 class="text-center mb-4">Table Reservation</h2>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="reservation-form" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required
                                   min="<?php echo date('Y-m-d'); ?>" value="<?php echo $_POST['date'] ?? ''; ?>">
                            <div class="invalid-feedback">Please select a valid date.</div>
                        </div>

                            <div class="mb-3">
                                <label for="time" class="form-label">Time</label>
                                <select class="form-select" id="time" name="time" required>
                                    <option value="">Select time</option>
                                    <?php
                                    // Generate time slots from 11 AM to 10 PM
                                    $start = strtotime('11:00');
                                    $end = strtotime('22:00');
                                    $interval = 30 * 60; // 30 minutes

                                for ($time = $start; $time <= $end; $time += $interval) {
                                    $time_value = date('H:i:s', $time);
                                    $time_display = date('h:i A', $time);
                                    $selected = ($selected_time === $time_value) ? 'selected' : '';
                                    echo "<option value=\"$time_value\" $selected>$time_display</option>";
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">Please select a time slot.</div>
                        </div>

                        <div class="mb-3">
                            <label for="guests" class="form-label">Number of Guests</label>
                            <select class="form-select" id="guests" name="guests" required>
                                <option value="">Select number of guests</option>
                                <?php 
                                $selected_guests = $_POST['guests'] ?? '';
                                for ($i = 1; $i <= 10; $i++): 
                                    $selected = ($selected_guests == $i) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <div class="invalid-feedback">Please select the number of guests.</div>
                        </div>

                        <div class="mb-3">
                            <label for="special_request" class="form-label">Special Requests (Optional)</label>
                            <textarea class="form-control" id="special_request" name="special_request" rows="3"><?php echo htmlspecialchars($_POST['special_request'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Submit Reservation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
})();

// Disable past dates
document.getElementById('date').min = new Date().toISOString().split('T')[0];

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