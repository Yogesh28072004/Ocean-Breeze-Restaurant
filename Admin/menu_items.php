<?php
require_once '../config/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                $id = isset($_POST['id']) ? $_POST['id'] : null;
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                $price = mysqli_real_escape_string($conn, $_POST['price']);
                $is_available = isset($_POST['is_available']) ? 1 : 0;
                
                // Handle image upload
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed = ['image/png', 'image/jpeg', 'image/jpg'];
                    if (in_array($_FILES['image']['type'], $allowed)) {
                        $filename = time() . '_' . $_FILES['image']['name'];
                        $upload_path = '../uploads/menu/';
                        
                        // Create directory if it doesn't exist
                        if (!file_exists($upload_path)) {
                            mkdir($upload_path, 0777, true);
                        }
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path . $filename)) {
                            $image = $filename;
                        }
                    }
                }

                if ($id) {
                    // Update existing item
                    $image_sql = $image ? ", image='$image'" : "";
                    $sql = "UPDATE menu_items SET 
                            name='$name', 
                            category_id='$category_id', 
                            description='$description', 
                            price='$price',
                            is_available='$is_available'
                            $image_sql 
                            WHERE id=$id";
                } else {
                    // Add new item
                    $sql = "INSERT INTO menu_items (name, category_id, description, price, image, is_available) 
                            VALUES ('$name', '$category_id', '$description', '$price', '$image', '$is_available')";
                }
                
                if (mysqli_query($conn, $sql)) {
                    $_SESSION['success'] = "Menu item " . ($id ? "updated" : "added") . " successfully!";
                } else {
                    $_SESSION['error'] = "Error: " . mysqli_error($conn);
                }
                break;

            case 'delete':
                $id = mysqli_real_escape_string($conn, $_POST['id']);
                // Get image filename before deleting
                $result = mysqli_query($conn, "SELECT image FROM menu_items WHERE id=$id");
                if ($row = mysqli_fetch_assoc($result)) {
                    if ($row['image']) {
                        @unlink('../uploads/menu/' . $row['image']);
                    }
                }
                if (mysqli_query($conn, "DELETE FROM menu_items WHERE id=$id")) {
                    $_SESSION['success'] = "Menu item deleted successfully!";
                } else {
                    $_SESSION['error'] = "Error deleting menu item: " . mysqli_error($conn);
                }
                break;
        }
        header('Location: menu_items.php');
        exit();
    }
}

// Fetch categories
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

// Fetch menu items with category names
$menu_items = mysqli_query($conn, "SELECT m.*, c.name as category_name 
                                  FROM menu_items m 
                                  LEFT JOIN categories c ON m.category_id = c.id 
                                  ORDER BY c.name, m.name");

require_once 'includes/admin_header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Menu Items Management</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Menu Items</li>
    </ol>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Add/Edit Menu Item Form -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-1"></i>
            Add/Edit Menu Item
        </div>
        <div class="card-body">
            <form id="menuItemForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" value="">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="price" class="form-label">Price (<?php echo CURRENCY_SYMBOL; ?>)</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label for="image" class="form-label">Image (PNG/JPG)</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/png,image/jpeg,image/jpg">
                        <div id="currentImage" class="mt-2" style="display: none;">
                            <img src="" alt="Current image" style="height: 100px;">
                            <br>
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_available" name="is_available" checked>
                        <label class="form-check-label" for="is_available">
                            Item is available
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Menu Item</button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">Clear Form</button>
            </form>
        </div>
    </div>

    <!-- Menu Items List -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Menu Items List
        </div>
        <div class="card-body">
            <table id="menuItemsTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($menu_items)): ?>
                        <tr>
                            <td>
                                <?php if ($item['image']): ?>
                                    <img src="../uploads/menu/<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         style="height: 50px; width: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="../assets/images/default-dish.jpg" 
                                         alt="Default image"
                                         style="height: 50px; width: 50px; object-fit: cover;">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($item['description'], 0, 100)) . '...'; ?></td>
                            <td><?php echo CURRENCY_SYMBOL . number_format($item['price'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $item['is_available'] ? 'success' : 'danger'; ?>">
                                    <?php echo $item['is_available'] ? 'Available' : 'Not Available'; ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" 
                                        onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                    Edit
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="deleteItem(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this menu item?
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteItemId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#menuItemsTable').DataTable({
        order: [[2, 'asc'], [1, 'asc']] // Sort by category then name
    });
});

// Edit menu item
function editItem(item) {
    const form = document.getElementById('menuItemForm');
    form.elements['action'].value = 'edit';
    form.elements['id'].value = item.id;
    form.elements['name'].value = item.name;
    form.elements['category_id'].value = item.category_id;
    form.elements['description'].value = item.description;
    form.elements['price'].value = item.price;
    form.elements['is_available'].checked = item.is_available == 1;

    // Show current image if exists
    const currentImage = document.getElementById('currentImage');
    if (item.image) {
        currentImage.style.display = 'block';
        currentImage.querySelector('img').src = '../uploads/menu/' + item.image;
    } else {
        currentImage.style.display = 'none';
    }

    // Scroll to form
    form.scrollIntoView({ behavior: 'smooth' });
}

// Delete menu item
function deleteItem(id, name) {
    document.getElementById('deleteItemId').value = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    document.querySelector('#deleteModal .modal-body').innerHTML = 
        `Are you sure you want to delete <strong>${name}</strong>?`;
    modal.show();
}

// Reset form
function resetForm() {
    const form = document.getElementById('menuItemForm');
    form.reset();
    form.elements['action'].value = 'add';
    form.elements['id'].value = '';
    document.getElementById('currentImage').style.display = 'none';
}
</script>

<?php require_once 'includes/admin_footer.php'; ?> 