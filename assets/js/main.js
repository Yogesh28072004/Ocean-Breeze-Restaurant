// Cart functionality
let cart = JSON.parse(localStorage.getItem('cart')) || [];
updateCartCount();

function addToCart(itemId, name, price) {
    const existingItem = cart.find(item => item.id === itemId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: itemId,
            name: name,
            price: price,
            quantity: 1
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    showToast('Item added to cart!');
}

function updateCartCount() {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        cartCount.textContent = cart.reduce((total, item) => total + item.quantity, 0);
    }
}

function updateQuantity(itemId, change) {
    const item = cart.find(item => item.id === itemId);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            cart = cart.filter(i => i.id !== itemId);
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        if (window.location.pathname.includes('cart.php')) {
            location.reload();
        }
    }
}

// Toast notification
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Reservation form validation
const reservationForm = document.getElementById('reservation-form');
if (reservationForm) {
    reservationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const date = document.getElementById('reservation-date').value;
        const time = document.getElementById('reservation-time').value;
        const guests = document.getElementById('num-guests').value;
        
        if (new Date(date) < new Date()) {
            showToast('Please select a future date');
            return;
        }
        
        if (guests < 1 || guests > 10) {
            showToast('Number of guests must be between 1 and 10');
            return;
        }
        
        this.submit();
    });
}

// Payment integration (example with PayPal)
function initPayPalButton() {
    if (document.getElementById('paypal-button-container')) {
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: document.getElementById('total-amount').value
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    document.getElementById('payment-form').submit();
                });
            }
        }).render('#paypal-button-container');
    }
}

// Admin dashboard charts (if needed)
function initAdminCharts() {
    if (document.getElementById('sales-chart')) {
        // Implementation for admin dashboard charts
        // You can use Chart.js or any other charting library
    }
}

// Admin Login Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const adminLoginForm = document.getElementById('adminLoginForm');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorDiv = document.getElementById('adminLoginError');
            
            // Clear previous error messages
            errorDiv.classList.add('d-none');
            
            fetch('admin_login.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.classList.remove('d-none');
                }
            })
            .catch(error => {
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.classList.remove('d-none');
            });
        });
    }
});

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    initPayPalButton();
    initAdminCharts();
}); 