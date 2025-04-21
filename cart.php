<?php
require_once 'config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - <?php echo RESTAURANT_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <h1 class="text-center mb-5">Shopping Cart</h1>

        <div class="row">
            <div class="col-lg-8">
                <!-- Cart Items -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div id="cart-items">
                            <!-- Cart items will be loaded here via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Order Summary -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Summary</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal:</span>
                            <span id="subtotal"><?php echo CURRENCY_SYMBOL; ?>0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Delivery Fee:</span>
                            <span id="delivery-fee"><?php echo CURRENCY_SYMBOL; ?>50.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong id="total"><?php echo CURRENCY_SYMBOL; ?>0.00</strong>
                        </div>

                        <!-- Checkout Form -->
                        <form id="checkout-form" action="process_order.php" method="POST">
                            <input type="hidden" name="total_amount" id="total-amount">
                            <input type="hidden" name="cart_items" id="cart-items-input">
                            
                            <div class="mb-3">
                                <label for="delivery-address" class="form-label">Delivery Address</label>
                                <textarea class="form-control" id="delivery-address" name="delivery_address" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="payment-method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment-method" name="payment_method" required>
                                    <option value="">Select payment method</option>
                                    <option value="cash">Cash on Delivery</option>
                                    <option value="card">Credit/Debit Card</option>
                                    <option value="upi">UPI Payment</option>
                                </select>
                            </div>

                            <!-- PayPal Button Container -->
                            <div id="paypal-button-container" style="display: none;"></div>

                            <!-- Regular Checkout Button -->
                            <button type="submit" class="btn btn-primary w-100" id="checkout-button">
                                Place Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const CURRENCY_SYMBOL = '<?php echo CURRENCY_SYMBOL; ?>';
        const DELIVERY_FEE = 50.00; // Updated delivery fee to ₹50

        // Load cart items
        function loadCartItems() {
            const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            const cartContainer = document.getElementById('cart-items');
            let subtotal = 0;

            if (cartItems.length === 0) {
                cartContainer.innerHTML = '<p class="text-center">Your cart is empty</p>';
                updateOrderSummary(0);
                return;
            }

            let html = '';
            cartItems.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;

                html += `
                    <div class="cart-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">${item.name}</h6>
                                <small class="text-muted">${CURRENCY_SYMBOL}${item.price.toFixed(2)} each</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="quantity-control">
                                    <button class="btn btn-sm btn-outline-secondary" 
                                            onclick="updateQuantity(${item.id}, -1)">-</button>
                                    <span class="mx-2">${item.quantity}</span>
                                    <button class="btn btn-sm btn-outline-secondary" 
                                            onclick="updateQuantity(${item.id}, 1)">+</button>
                                </div>
                                <span class="ms-3">${CURRENCY_SYMBOL}${itemTotal.toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            cartContainer.innerHTML = html;
            updateOrderSummary(subtotal);
            
            // Update hidden cart items input
            document.getElementById('cart-items-input').value = JSON.stringify(cartItems);
        }

        // Update order summary
        function updateOrderSummary(subtotal) {
            const total = subtotal + DELIVERY_FEE;

            document.getElementById('subtotal').textContent = `${CURRENCY_SYMBOL}${subtotal.toFixed(2)}`;
            document.getElementById('delivery-fee').textContent = `${CURRENCY_SYMBOL}${DELIVERY_FEE.toFixed(2)}`;
            document.getElementById('total').textContent = `${CURRENCY_SYMBOL}${total.toFixed(2)}`;
            document.getElementById('total-amount').value = total.toFixed(2);
        }

        // Handle payment method change
        document.getElementById('payment-method').addEventListener('change', function(e) {
            const paypalContainer = document.getElementById('paypal-button-container');
            const checkoutButton = document.getElementById('checkout-button');

            if (e.target.value === 'upi') {
                paypalContainer.style.display = 'block';
                checkoutButton.style.display = 'none';
            } else {
                paypalContainer.style.display = 'none';
                checkoutButton.style.display = 'block';
            }
        });

        // Handle form submission
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            if (cartItems.length === 0) {
                e.preventDefault();
                alert('Your cart is empty. Please add items before checking out.');
                return;
            }

            // Clear cart after successful submission
            localStorage.removeItem('cart');
        });
// Update item quantity in localStorage and refresh cart display
function updateQuantity(itemId, change) {
    let cartItems = JSON.parse(localStorage.getItem('cart')) || [];
// Load cart items on page load

    cartItems = cartItems.map(item => {
        if (item.id === itemId) {
            item.quantity += change;
            if (item.quantity < 1) {
                item.quantity = 1; // Prevent quantity from going below 1
            }
        }
        return item;
    });

    localStorage.setItem('cart', JSON.stringify(cartItems));
    loadCartItems(); // Refresh the cart items display
}

        // Load cart items on page load
        document.addEventListener('DOMContentLoaded', loadCartItems);
    </script>
</body>
</html> 