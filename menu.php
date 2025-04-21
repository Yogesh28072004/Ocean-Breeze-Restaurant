<?php
require_once 'config/db_connect.php';

// Fetch categories
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

// Fetch menu items
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$menu_query = "SELECT * FROM menu_items";
if ($category_id > 0) {
    $menu_query .= " WHERE category_id = $category_id";
}
$menu_query .= " ORDER BY name";
$menu_result = mysqli_query($conn, $menu_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Ocean Breeze</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <h1 class="text-center mb-5">Our Menu</h1>

        <!-- Categories -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-center flex-wrap gap-2">
                    <a href="menu.php" class="btn <?php echo !$category_id ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        All
                    </a>
                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                        <a href="menu.php?category=<?php echo $category['id']; ?>" 
                           class="btn <?php echo $category_id == $category['id'] ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <div class="row">
            <?php while ($item = mysqli_fetch_assoc($menu_result)): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <?php if ($item['image']): ?>
                            <img src="uploads/menu/<?php echo htmlspecialchars($item['image']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <img src="assets/images/default-dish.jpg" 
                                 class="card-img-top" 
                                 alt="Default dish image"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($item['description']); ?></p>
                            <p class="card-text"><strong><?php echo CURRENCY_SYMBOL . number_format($item['price'], 2); ?></strong></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <?php if ($item['is_available']): ?>
                                    <button class="btn btn-primary" 
                                            onclick="addToCart(<?php echo $item['id']; ?>, 
                                                            '<?php echo addslashes($item['name']); ?>', 
                                                            <?php echo $item['price']; ?>)">
                                        Add to Cart
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>Out of Stock</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn    .jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 