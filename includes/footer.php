<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>Contact Us</h5>
                <p>
                    <i class="fas fa-map-marker-alt"></i> <?php echo RESTAURANT_ADDRESS; ?><br>
                    <i class="fas fa-phone"></i> <?php echo RESTAURANT_PHONE; ?><br>
                    <i class="fas fa-envelope"></i> <?php echo RESTAURANT_EMAIL; ?>
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
                    <a href="<?php echo FACEBOOK_URL; ?>" class="text-light me-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="<?php echo TWITTER_URL; ?>" class="text-light me-2"><i class="fab fa-twitter"></i></a>
                    <a href="<?php echo INSTAGRAM_URL; ?>" class="text-light me-2"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12 text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo RESTAURANT_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer> 