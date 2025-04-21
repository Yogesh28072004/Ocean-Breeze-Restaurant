<?php
require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

// Handle Delete Category
if (isset($_POST['delete_category'])) {
    $category_id = (int)$_POST['category_id'];
    
    // First check if category has menu items
    $check_query = "SELECT COUNT(*) as count FROM menu_items WHERE category_id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $count = mysqli_fetch_assoc($result)['count'];
    
    if ($count > 0) {
        $delete_error = "Cannot delete category. Please remove or reassign all menu items in this category first.";
    } else {
        $delete_query = "DELETE FROM categories WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $category_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "Category deleted successfully!";
        } else {
            $delete_error = "Error deleting category. Please try again.";
        }
    }
}

// Handle Add/Edit Category
if (isset($_POST['save_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $category_description = mysqli_real_escape_string($conn, $_POST['category_description']);
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    if (empty($category_name)) {
        $error_msg = "Category name is required.";
    } else {
        if ($category_id > 0) {
            // Update existing category
            $query = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssi", $category_name, $category_description, $category_id);
        } else {
            // Add new category
            $query = "INSERT INTO categories (name, description) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ss", $category_name, $category_description);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = ($category_id > 0) ? "Category updated successfully!" : "Category added successfully!";
        } else {
            $error_msg = ($category_id > 0) ? "Error updating category." : "Error adding category.";
        }
    }
}

// Fetch all categories
$query = "SELECT * FROM categories ORDER BY name";
$categories = mysqli_query($conn, $query);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2>Manage Categories</h2>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="fas fa-plus"></i> Add New Category
            </button>
        </div>
    </div>

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($delete_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $delete_error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary edit-category" 
                                            data-id="<?php echo $category['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($category['description']); ?>"
                                            data-bs-toggle="modal" data-bs-target="#categoryModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" name="delete_category" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="category_id" id="category_id">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_description" class="form-label">Description</label>
                        <textarea class="form-control" id="category_description" name="category_description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_category" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit category button clicks
    const editButtons = document.querySelectorAll('.edit-category');
    const categoryModal = document.getElementById('categoryModal');
    const modalTitle = document.getElementById('modalTitle');
    const categoryIdInput = document.getElementById('category_id');
    const categoryNameInput = document.getElementById('category_name');
    const categoryDescriptionInput = document.getElementById('category_description');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const description = this.dataset.description;

            modalTitle.textContent = 'Edit Category';
            categoryIdInput.value = id;
            categoryNameInput.value = name;
            categoryDescriptionInput.value = description;
        });
    });

    // Reset modal when adding new category
    categoryModal.addEventListener('hidden.bs.modal', function() {
        modalTitle.textContent = 'Add New Category';
        categoryIdInput.value = '';
        categoryNameInput.value = '';
        categoryDescriptionInput.value = '';
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }, 5000);
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?> 