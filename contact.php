<?php
require_once 'config/db_connect.php';

$success_msg = $error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_msg = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Please enter a valid email address.';
    } else {
        // Insert into database
        $query = "INSERT INTO messages (name, email, subject, message, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = 'Thank you for your message. We will get back to you soon!';
        } else {
            $error_msg = 'Sorry, there was an error sending your message. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Ocean Breeze</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background: #4CAF50;
            padding: 15px 0;
        }

        .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            text-decoration: none;
        }

        .navbar-brand:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        .contact-section {
            padding: 40px 0;
        }

        .contact-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .contact-header h1 {
            color: #333;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .contact-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .contact-form {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
        }

        .contact-info {
            background: #4CAF50;
            color: white;
            padding: 40px;
            border-radius: 15px;
            height: 100%;
        }

        .contact-info h3 {
            margin-bottom: 30px;
            font-weight: 600;
        }

        .contact-info-item {
            margin-bottom: 30px;
            display: flex;
            align-items: flex-start;
        }

        .contact-info-item i {
            font-size: 1.5rem;
            margin-right: 15px;
            color: rgba(255,255,255,0.9);
        }

        .contact-info-item p {
            margin: 0;
            font-size: 1.1rem;
        }

        .form-control {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76,175,80,0.25);
        }

        .btn-submit {
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            color: white;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .back-link {
            display: inline-block;
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .back-link:hover {
            color: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">Ocean Breeze</a>
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </nav>

    <div class="contact-section">
        <div class="container">
            <div class="contact-header">
                <h1>Contact Us</h1>
                <p>Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-7 mb-4">
                    <div class="contact-form">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-submit">Send Message</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="contact-info">
                        <h3>Get in Touch</h3>
                        <div class="contact-info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <p>123 Restaurant Street<br>City, State 12345</p>
                        </div>
                        <div class="contact-info-item">
                            <i class="fas fa-phone"></i>
                            <p>+1 (555) 123-4567</p>
                        </div>
                        <div class="contact-info-item">
                            <i class="fas fa-envelope"></i>
                            <p>info@yourrestaurant.com</p>
                        </div>
                        <div class="contact-info-item">
                            <i class="fas fa-clock"></i>
                            <p>Mon - Fri: 9:00 AM - 10:00 PM<br>
                               Sat - Sun: 10:00 AM - 11:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 