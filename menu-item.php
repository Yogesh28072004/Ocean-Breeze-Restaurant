<?php
require_once 'config/db_connect.php';

// Get menu item ID from URL
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch menu item details
$query = "SELECT m.*, c.name as category_name 
          FROM menu_items m 
          LEFT JOIN categories c ON m.category_id = c.id 
          WHERE m.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    header('Location: menu.php');
    exit();
}

$item = mysqli_fetch_assoc($result);

require_once 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="menu.php">Menu</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($item['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6 mb-4">
            <?php if ($item['image']): ?>
                <img src="uploads/menu/<?php echo htmlspecialchars($item['image']); ?>" 
                     class="img-fluid rounded" 
                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                     style="width: 100%; height: 400px; object-fit: cover;">
            <?php else: ?>
                <img src="assets/images/default-dish.jpg" 
                     class="img-fluid rounded" 
                     alt="Default dish image"
                     style="width: 100%; height: 400px; object-fit: cover;">
            <?php endif; ?>
        </div>
        
        <div class="col-md-6">
            <h1 class="mb-3"><?php echo htmlspecialchars($item['name']); ?></h1>
            
            <p class="text-muted mb-3">
                Category: <?php echo htmlspecialchars($item['category_name']); ?>
            </p>
            
            <div class="mb-4">
                <h4 class="text-primary"><?php echo CURRENCY_SYMBOL . number_format($item['price'], 2); ?></h4>
            </div>
            
            <div class="mb-4">
                <h5>Description</h5>
                <p><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
            </div>
            
            <?php if ($item['is_available']): ?>
                <div class="d-flex align-items-center mb-4">
                    <div class="input-group me-3" style="width: 130px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(-1)">-</button>
                        <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="10">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(1)">+</button>
                    </div>
                    <button class="btn btn-primary" onclick="addToCart()">
                        Add to Cart
                    </button>
                </div>
                <div class="alert alert-success" id="success-alert" style="display: none;">
                    Item added to cart successfully!
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    This item is currently not available.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateQuantity(change) {
    const input = document.getElementById('quantity');
    let value = parseInt(input.value) + change;
    value = Math.max(1, Math.min(10, value)); // Limit between 1 and 10
    input.value = value;
}

function addToCart() {
    const quantity = parseInt(document.getElementById('quantity').value);
    const item = {
        id: <?php echo $item['id']; ?>,
        name: <?php echo json_encode($item['name']); ?>,
        price: <?php echo $item['price']; ?>,
        quantity: quantity
    };

    // Get existing cart
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Check if item already exists in cart
    const existingItemIndex = cart.findIndex(cartItem => cartItem.id === item.id);
    
    if (existingItemIndex > -1) {
        // Update quantity if item exists
        cart[existingItemIndex].quantity += quantity;
    } else {
        // Add new item if it doesn't exist
        cart.push(item);
    }
    
    // Save updated cart
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Show success message
    const alert = document.getElementById('success-alert');
    alert.style.display = 'block';
    setTimeout(() => {
        alert.style.display = 'none';
    }, 3000);

    // Update cart count in header
    updateCartCount();
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        cartCount.textContent = totalItems;
    }
}

// Update cart count on page load
document.addEventListener('DOMContentLoaded', updateCartCount);
</script>

<?php include 'includes/footer.php'; ?> 