<?php
session_start();
include '../php/connect.php';

// Ensure the user is logged in and is a manufacturer
if (!isset($_SESSION['user_id']) || ($_SESSION['account_type'] ?? '') !== 'manufacturer') {
    header("Location: ../html/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$stores = [];
$products = [];
$message = "";

// Handle Add Product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $store_id = intval($_POST['store_id']);
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $minOrder = intval($_POST['minOrder'] ?? 1);
    $customization = trim($_POST['customization'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_path = '';

    // Handle product image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/products/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $file_name = 'product_' . time() . '_' . uniqid() . '.' . $ext;
        $file_path = $upload_dir . $file_name;

        $allowed_types = ['jpg','jpeg','png','gif','webp'];
        if (in_array(strtolower($ext), $allowed_types)) {
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $file_path)) {
                $image_path = 'images/products/' . $file_name;
            }
        }
    }

    // Insert product into database
    if (!empty($name) && $price > 0) {
        $stmt = $db->prepare("
            INSERT INTO products 
            (storeID, name, description, price, category, image, minOrder, customization) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "issdssis", 
            $store_id, 
            $name, 
            $description, 
            $price, 
            $category, 
            $image_path, 
            $minOrder, 
            $customization
        );
        if ($stmt->execute()) {
            $message = "Product added successfully!";
        } else {
            $message = "Error adding product: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Product name and price are required.";
    }
}

// Fetch the stores for this manufacturer
$store_query = $db->prepare("
    SELECT s.storeID, s.name, s.description, s.image_url, c.name AS category_name
    FROM stores s
    LEFT JOIN categories c ON s.categoryID = c.category_id
    WHERE s.manufacturerID = ?
");
$store_query->bind_param("i", $user_id);
$store_query->execute();
$result = $store_query->get_result();
while ($row = $result->fetch_assoc()) {
    $stores[$row['storeID']] = $row;
}
$store_query->close();

// Fetch products for these stores
if (!empty($stores)) {
    $store_ids = implode(',', array_keys($stores));
    $product_query = $db->query("
        SELECT *
        FROM products
        WHERE storeID IN ($store_ids)
        ORDER BY name ASC
    ");
    if ($product_query) {
        while ($row = $product_query->fetch_assoc()) {
            $products[$row['storeID']][] = $row;
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PriMeri - My Store</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/my_store.css">
</head>

<body class="bg-light-bg text-dark">

  <!-- Header -->
  <header class="bg-white shadow-sm sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-xl px-4 d-flex justify-content-between align-items-center">
        <a href="manage_store.html" class="navbar-brand fs-4 fw-bold text-primary-custom d-flex align-items-center">
          <img src="../images/logo.jpg" alt="PriMeri Logo" width="48" height="48" class="me-2">
          PriMeri
        </a>
        <a class="navbar-brand fs-4 fw-bold text-primary-custom d-flex align-items-center" href="create_store.php">Create Store</a>
        <a class="navbar-brand fs-4 fw-bold text-primary-custom d-flex align-items-center" href="manage_store.html">Manage Store</a>
        <a href="../php/logout.php" class="btn bg-primary-custom text-white px-3 py-2 rounded-3 btn-hover-primary">Log Out</a>
      </div>
    </nav>
  </header>

  <!-- Main -->
  <main class="container-xl py-5">
    <h1 class="fw-bolder text-dark text-center mb-4">My Store</h1>
    <p class="text-center text-muted mb-5">Manage your products: add, edit, or remove items from your store.</p>

    <?php if (!empty($message)): ?>
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (empty($stores)): ?>
      <div class="text-center py-5">
        <h3 class="text-muted">No Stores Found</h3>
        <p class="text-muted">You haven't created any stores yet.</p>
        <a href="create_store.php" class="btn bg-primary-custom text-white btn-hover-primary px-4">
          Create Your First Store
        </a>
      </div>
    <?php else: ?>
      <?php foreach ($stores as $store): ?>
        <!-- Store Header -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-2">
                <?php if (!empty($store['image_url'])): ?>
                  <img src="<?php echo htmlspecialchars($store['image_url']); ?>" 
                       alt="<?php echo htmlspecialchars($store['name']); ?>" 
                       class="img-fluid rounded-3">
                <?php else: ?>
                  <img src="../images/default-store.jpg" 
                       alt="Default Store" 
                       class="img-fluid rounded-3">
                <?php endif; ?>
              </div>
              <div class="col-md-8">
                <h3 class="fw-bold text-dark"><?php echo htmlspecialchars($store['name']); ?></h3>
                <p class="text-muted mb-2"><?php echo htmlspecialchars($store['category_name']); ?></p>
                <?php if (!empty($store['description'])): ?>
                  <p class="text-muted"><?php echo htmlspecialchars($store['description']); ?></p>
                <?php endif; ?>
              </div>
              <div class="col-md-2 text-end">
                <a href="store-detail.php?id=<?php echo $store['storeID']; ?>" 
                   class="btn bg-primary-custom text-white btn-sm">
                  Store
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Add New Item Form -->
        <section class="mb-5">
          <div class="card shadow-lg border-0 rounded-4 p-4">
            <h4 class="fw-semibold mb-3 text-primary-custom">Add New Product to <?php echo htmlspecialchars($store['name']); ?></h4>
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="store_id" value="<?php echo $store['storeID']; ?>">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Product Name *</label>
                  <input type="text" name="name" class="form-control" placeholder="Enter item name" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Price Per Unit ($) *</label>
                  <input type="number" name="price" class="form-control" step="0.01" min="0" placeholder="e.g. 10.00" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Product Category</label>
                  <input type="text" name="category" class="form-control" placeholder="e.g., tshirts, mugs, accessories">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Minimum Order Quantity *</label>
                  <input type="number" name="minOrder" class="form-control" min="1" value="1" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Product Image</label>
                  <input type="file" name="product_image" class="form-control" accept="image/*">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Customization Options</label>
                  <input type="text" name="customization" class="form-control" placeholder="e.g., Logo printing, custom colors">
                </div>
                <div class="col-12">
                  <label class="form-label">Description</label>
                  <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the product..."></textarea>
                </div>
                <div class="col-12 text-end">
                  <button type="submit" name="add_product" class="btn bg-primary-custom text-white btn-hover-primary px-4">Add Product</button>
                </div>
              </div>
            </form>
          </div>
        </section>

        <!-- Store Items Table -->
        <section class="mb-5">
          <h4 class="fw-semibold mb-3 text-secondary-custom">Products in <?php echo htmlspecialchars($store['name']); ?></h4>
          
          <?php if (empty($products[$store['storeID']])): ?>
            <div class="alert alert-info text-center">
              <p class="mb-0">No products added yet. Add your first product above!</p>
            </div>
          <?php else: ?>
            <div class="table-responsive shadow rounded-4">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-secondary-custom text-white">
                  <tr>
                    <th>Product Image</th>
                    <th>Item</th>
                    <th>Price/Unit</th>
                    <th>Min Qty</th>
                    <th>Category</th>
                    <th>Customizable</th>
                    <th>Description</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($products[$store['storeID']] as $product): ?>
                    <tr>
                      <td>
                        <?php if (!empty($product['image'])): ?>
                          <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                               alt="<?php echo htmlspecialchars($product['name']); ?>" 
                               class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                        <?php else: ?>
                          <img src="../images/default-product.jpg" 
                               alt="Default Product" 
                               class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                        <?php endif; ?>
                      </td>
                      <td class="fw-semibold"><?php echo htmlspecialchars($product['name']); ?></td>
                      <td>$<?php echo number_format($product['price'], 2); ?></td>
                      <td><?php echo htmlspecialchars($product['minOrder']); ?></td>
                      <td><?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?></td>
                      <td><?php echo !empty($product['customization']) ? 'Yes' : 'No'; ?></td>
                      <td>
                        <?php 
                          $description = $product['description'] ?? 'No description';
                          echo strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description;
                        ?>
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm bg-primary-custom text-white rounded-3 me-1">Edit</button>
                        <a href="my_store.php?delete_product=<?php echo $product['id']; ?>" 
                           class="btn btn-sm bg-secondary-custom text-white rounded-3"
                           onclick="return confirm('Are you sure you want to delete this product?')">
                          Delete
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </section>
        <hr class="my-5">
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <footer class="bg-dark-bg-custom text-white mt-5">
    <div class="container-xl px-4 py-4 text-center">
      <p class="mb-0">&copy; 2025 PriMeri.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>