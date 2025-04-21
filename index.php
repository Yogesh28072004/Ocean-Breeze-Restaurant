<?php
require_once 'config/db_connect.php';

// Fetch featured dishes from the database
$query = "SELECT id, name, description, price, image FROM menu_items WHERE is_available = 1 LIMIT 6";
$result = mysqli_query($conn, $query);
$featured_dishes = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $featured_dishes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocean Breeze - Welcome</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <div class="navbar-brand d-flex align-items-center">
                <!-- Logo space -->
                <img src="assets/images/images.jpg" alt="Restaurant Logo" class="me-2" style="height: 50px; width: auto;">
                <a href="index.php" class="text-decoration-none text-white">Ocean Breeze</a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu.php">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservation.php">Reservations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <span class="badge bg-danger" id="cart-count">0</span>
                        </a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                                <li><a class="dropdown-item" href="my_reservations.php">My Reservations</a></li>
                                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                    <li><a class="dropdown-item" href="admin/">Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section" style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/bkimg.jpg') no-repeat center center; background-size: cover; min-height: 100vh; position: relative;">
        <div class="container">
            <div class="row min-vh-100 align-items-center">
                <div class="col-md-6">
                    <div class="text-white">
                        <h1 class="display-4 fw-bold mb-4" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">Welcome to OceanBreeze Restaurant</h1>
                        <p class="lead mb-4" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">Experience the finest dining with our carefully crafted menu and exceptional service.</p>
                        <div class="hero-buttons">
                            <a href="menu.php" class="btn btn-primary btn-lg me-3">View Menu</a>
                            <a href="reservation.php" class="btn btn-outline-light btn-lg">Book a Table</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Section -->
    <section class="featured-section py-5">
        <div class="container">
            <h2 class="text-center mb-4">Featured Dishes</h2>
            <div class="row">
                <?php foreach ($featured_dishes as $dish): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if ($dish['image']): ?>
                                <img src="uploads/menu/<?php echo htmlspecialchars($dish['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($dish['name']); ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <img src="assets/images/default-dish.jpg" class="card-img-top" alt="Default dish image" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($dish['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($dish['description'], 0, 100)) . '...'; ?></p>
                                <p class="card-text"><strong><?php echo CURRENCY_SYMBOL . number_format($dish['price'], 2); ?></strong></p>
                                <a href="menu-item.php?id=<?php echo $dish['id']; ?>" class="btn btn-primary mt-auto">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="menu.php" class="btn btn-outline-primary btn-lg">View Full Menu</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p>
                        <i class="fas fa-map-marker-alt"></i> 123 Restaurant Street<br>
                        <i class="fas fa-phone"></i> (123) 456-7890<br>
                        <i class="fas fa-envelope"></i> info@restaurant.com
                    </p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        
                        <li><a href="menu.php" class="text-light">Menu</a></li>
                        <li><a href="reservation.php" class="text-light">Reservations</a></li>
                        <li><a href="contact.php" class="text-light">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Follow Us</h5>
                    <div class="social-links">
                        <a href="https://www.facebook.com/login/" class="text-light me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://x.com/i/flow/login?lang=en" class="text-light me-2"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.instagram.com/accounts/login/?hl=en" class="text-light me-2"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> OceanBreeze. All rights reserved.<br> Designed and developed By Yogesh</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 