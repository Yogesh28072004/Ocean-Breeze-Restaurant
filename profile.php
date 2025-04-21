<?php
require_once 'config/db_connect.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Fetch user information
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if email already exists for other users
        $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = mysqli_prepare($conn, $check_email);
        mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
        mysqli_stmt_execute($stmt);
        $email_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($email_result) > 0) {
            $error = "Email already exists";
        } else {
            // Update profile
            $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ssssi", $full_name, $email, $phone, $address, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Profile updated successfully";
                // Refresh user data
                $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
                $user = mysqli_fetch_assoc($result);
            } else {
                $error = "Error updating profile";
            }
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match";
    } else if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Password changed successfully";
            } else {
                $error = "Error changing password";
            }
        } else {
            $error = "Current password is incorrect";
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                    </div>
                    <h4 class="mb-3"><?php echo htmlspecialchars($user['username']); ?></h4>
                    <p class="text-muted mb-4">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    <div class="d-grid">
                        <button class="btn btn-primary mb-2" type="button" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="card-title mb-4">Profile Information</h3>
                    
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

                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                            <div class="invalid-feedback">Please enter your full name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Delivery Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                        <div class="invalid-feedback">Please enter your current password.</div>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                        <div class="invalid-feedback">Password must be at least 6 characters long.</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback">Passwords do not match.</div>
                    </div>

                    <input type="hidden" name="change_password" value="1">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
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
            
            // Check if passwords match in the change password form
            if (form.querySelector('#new_password')) {
                const newPassword = form.querySelector('#new_password').value;
                const confirmPassword = form.querySelector('#confirm_password').value;
                
                if (newPassword !== confirmPassword) {
                    event.preventDefault();
                    form.querySelector('#confirm_password').setCustomValidity('Passwords do not match');
                } else {
                    form.querySelector('#confirm_password').setCustomValidity('');
                }
            }
            
            form.classList.add('was-validated');
        }, false);
    });
})();

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