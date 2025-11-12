<?php
session_start();
include '../php/conf.php';
include '../php/connect.php';

// Check if user is logged in and is a manufacturer
if (!isset($_SESSION['user_id']) || ($_SESSION['account_type'] ?? '') !== 'manufacturer') {
    header("Location: ../html/login.html");
    exit();
}

$message = "";
$categories = [];
$products = [];

// Get manufacturer ID from logged-in user
$user_id = $_SESSION['user_id'];

// Fetch available categories from database
$category_query = "SELECT category_id, name, image_url FROM categories";
$category_result = $db->query($category_query);
if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_name = trim($_POST['store_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $minOrder = $_POST['minOrder'] ?? 1;
    $leadTime = $_POST['leadTime'] ?? '';
    $customization = $_POST['customization'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $features = $_POST['features'] ?? '';
    $btnClass = $_POST['btnClass'] ?? 'btn-primary';
    
    $products_data = $_POST['products'] ?? [];
    
    // Validate required fields
    if (empty($store_name)) {
        $message = "Store name is required.";
    } elseif (empty($category_id)) {
        $message = "Please select a category.";
    } elseif (empty($products_data)) {
        $message = "Please add at least one product.";
    } else {
        // Begin transaction
        $db->begin_transaction();
        
        try {
            // Handle store image upload
            $image_path = '';
            if (isset($_FILES['store_image']) && $_FILES['store_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../images/stores/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($_FILES['store_image']['name'], PATHINFO_EXTENSION);
                $file_name = 'store_' . time() . '_' . uniqid() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                // Check if file is an image
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array(strtolower($file_extension), $allowed_types)) {
                    if (move_uploaded_file($_FILES['store_image']['tmp_name'], $file_path)) {
                        $image_path = 'images/stores/' . $file_name;
                    } else {
                        $message = "Error uploading image.";
                    }
                } else {
                    $message = "Invalid file type. Please upload an image (JPG, PNG, GIF, WEBP).";
                }
            }

            // Create store with database structure
            $store_stmt = $db->prepare("INSERT INTO stores (manufacturerID, categoryID, name, description, image_url, minOrder, leadTime, customization, tags, features, btnClass) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $store_stmt->bind_param(
                "iisssisssss", 
                $user_id,
                $category_id,
                $store_name,
                $description,
                $image_path,
                $minOrder,
                $leadTime,
                $customization,
                $tags,
                $features,
                $btnClass
            );
            $store_stmt->execute();
            $store_id = $db->insert_id;
            $store_stmt->close();
            
            // Insert products
            $product_stmt = $db->prepare("INSERT INTO products (storeID, name, description, price, category, image, minOrder, inStock, customization) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($products_data as $index => $product) {
                if (!empty($product['name']) && !empty($product['price'])) {
                    // Handle product image upload
                    $product_image_path = '';
                    if (isset($_FILES['products']) && isset($_FILES['products']['name'][$index]['image']) && $_FILES['products']['error'][$index]['image'] === UPLOAD_ERR_OK) {
                        $product_upload_dir = '../images/products/';
                        if (!is_dir($product_upload_dir)) {
                            mkdir($product_upload_dir, 0755, true);
                        }
                        
                        $product_file_extension = pathinfo($_FILES['products']['name'][$index]['image'], PATHINFO_EXTENSION);
                        $product_file_name = 'product_' . time() . '_' . uniqid() . '.' . $product_file_extension;
                        $product_file_path = $product_upload_dir . $product_file_name;
                        
                        if (in_array(strtolower($product_file_extension), $allowed_types)) {
                            if (move_uploaded_file($_FILES['products']['tmp_name'][$index]['image'], $product_file_path)) {
                                $product_image_path = 'images/products/' . $product_file_name;
                            }
                        }
                    }
                    
                    $product_category = $product['category'] ?? '';
                    $product_minOrder = $product['min_order'] ?? 1;
                    $inStock = 1; // Default to in stock
                    $product_customization = $product['customizable'] ?? '';
                    
                    $product_stmt->bind_param(
                        "issdssiis", 
                        $store_id, 
                        $product['name'],
                        $product['description'],
                        $product['price'],
                        $product_category,
                        $product_image_path,
                        $product_minOrder,
                        $inStock,
                        $product_customization
                    );
                    $product_stmt->execute();
                }
            }
            $product_stmt->close();
            
            $db->commit();
            $_SESSION['store_created'] = true;
            $_SESSION['new_store_id'] = $store_id;
            header("Location: store-detail.php?id=" . $store_id);
            exit();
            
        } catch (Exception $e) {
            $db->rollback();
            $message = "Error creating store: " . $e->getMessage();
        }
    }
    
    // Preserve submitted data for form re-display
    $submitted_data = [
        'store_name' => $store_name,
        'description' => $description,
        'category_id' => $category_id,
        'minOrder' => $minOrder,
        'leadTime' => $leadTime,
        'customization' => $customization,
        'tags' => $tags,
        'features' => $features,
        'btnClass' => $btnClass
    ];
    $products = $products_data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Store - PriMeri</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/catalog.css">
  <link rel="stylesheet" href="../css/create-store.css">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light-bg text-dark">

  <!-- Navbar -->
  <header class="bg-white shadow-sm sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-xl px-4">
        <a href="#" class="navbar-brand fs-4 fw-bold text-primary-custom d-flex align-items-center">
          <img src="../images/logo.jpg" alt="PriMeri Logo" width="48" height="48" class="me-2">
          PriMeri
        </a>
        <a class="navbar-brand fs-4 fw-bold text-primary-custom d-flex align-items-center" href="my_store.html">My Store</a>
      </div>
    </nav>
  </header>

  <!-- Main -->
  <main class="container-xl px-4 py-5">
    <div class="text-center mb-5">
      <h1 class="display-6 fw-bolder text-dark">Create Your Store</h1>
      <p class="fs-5 text-muted mx-auto" style="max-width: 700px;">
        Set up your store details and add products to start selling on PriMeri.
      </p>
    </div>

    <?php if (!empty($message)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form method="POST" class="form-section" enctype="multipart/form-data">

      <!-- Step 1: Store Basic Information -->
      <div class="mb-5">
        <h5 class="fw-semibold mb-3 text-primary-custom">
          <i class="fas fa-store me-2"></i>1. Store Information
        </h5>
        
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <div class="row g-4">
            <!-- Store Name -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Store Name *</label>
              <input type="text" name="store_name" class="form-control" 
                     value="<?php echo htmlspecialchars($submitted_data['store_name'] ?? ''); ?>" 
                     placeholder="e.g., Fashion Apparel Store" required>
            </div>

            <!-- Store Image Upload -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Store Image</label>
              <input type="file" name="store_image" class="form-control" accept="image/*">
              <small class="text-muted">Upload a store logo or banner image (JPG, PNG, GIF, WEBP)</small>
            </div>

            <!-- Description -->
            <div class="col-12">
              <label class="form-label fw-semibold">Store Description</label>
              <textarea name="description" class="form-control" rows="3" 
                        placeholder="Describe your store and what makes it unique..."><?php echo htmlspecialchars($submitted_data['description'] ?? ''); ?></textarea>
            </div>

            <!-- Store Specifications -->
            <div class="col-md-4">
              <label class="form-label fw-semibold">Minimum Order</label>
              <input type="number" name="minOrder" class="form-control" min="1" 
                     value="<?php echo htmlspecialchars($submitted_data['minOrder'] ?? ''); ?>" 
                     placeholder="e.g., 10">
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Lead Time</label>
              <input type="text" name="leadTime" class="form-control" 
                     value="<?php echo htmlspecialchars($submitted_data['leadTime'] ?? ''); ?>" 
                     placeholder="e.g., 7-14 business days">
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Button Class</label>
              <select name="btnClass" class="form-select">
                <option value="btn-primary" <?php echo ($submitted_data['btnClass'] ?? 'btn-primary') === 'btn-primary' ? 'selected' : ''; ?>>Primary Blue</option>
                <option value="btn-secondary" <?php echo ($submitted_data['btnClass'] ?? '') === 'btn-secondary' ? 'selected' : ''; ?>>Secondary Gray</option>
                <option value="btn-success" <?php echo ($submitted_data['btnClass'] ?? '') === 'btn-success' ? 'selected' : ''; ?>>Success Green</option>
              </select>
            </div>

            <!-- Tags and Features -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tags</label>
              <input type="text" name="tags" class="form-control" 
                     value="<?php echo htmlspecialchars($submitted_data['tags'] ?? ''); ?>" 
                     placeholder="e.g., premium, sustainable, handmade (comma separated)">
              <small class="text-muted">Separate tags with commas</small>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Features</label>
              <input type="text" name="features" class="form-control" 
                     value="<?php echo htmlspecialchars($submitted_data['features'] ?? ''); ?>" 
                     placeholder="e.g., fast shipping, eco-friendly (comma separated)">
              <small class="text-muted">Separate features with commas</small>
            </div>

            <!-- Customization Option -->
            <div class="col-12">
              <label class="form-label fw-semibold">Customization Options</label>
              <input type="text" name="customization" class="form-control" 
                     value="<?php echo htmlspecialchars($submitted_data['customization'] ?? ''); ?>" 
                     placeholder="e.g., Logo printing & embroidery">
            </div>
          </div>
        </div>
      </div>

      <hr class="my-4">

      <!-- Step 2: Choose Category -->
      <div class="mb-5">
        <h5 class="fw-semibold mb-3 text-primary-custom">2. Choose a Catalog Category</h5>
        <div class="row row-cols-2 row-cols-md-4 g-4 catalog-select">
          <?php foreach ($categories as $category): ?>
            <div class="col text-center">
              <input type="radio" name="category_id" id="category_<?php echo $category['category_id']; ?>" 
                     value="<?php echo $category['category_id']; ?>" class="d-none" 
                     <?php echo (isset($submitted_data['category_id']) && $submitted_data['category_id'] == $category['category_id']) ? 'checked' : ''; ?>
                     required>
              <label for="category_<?php echo $category['category_id']; ?>" class="catalog-option">
                <?php if (!empty($category['image_url'])): ?>
                  <img src="<?php echo htmlspecialchars($category['image_url']); ?>" 
                       alt="<?php echo htmlspecialchars($category['name']); ?>" class="img-fluid mb-2">
                <?php else: ?>
                  <img src="../images/default-category.jpg" 
                       alt="<?php echo htmlspecialchars($category['name']); ?>" class="img-fluid mb-2">
                <?php endif; ?>
                <div><?php echo htmlspecialchars($category['name']); ?></div>
              </label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <hr class="my-4">

      <!-- Step 3: Add Products -->
      <div class="mb-4">
        <h5 class="fw-semibold mb-3 text-primary-custom">
          <i class="fas fa-boxes me-2"></i>3. Add Store Products
        </h5>

        <div id="product-list">
          <?php if (empty($products)): ?>
            <!-- Default empty product form -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 product-card">
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Product Name *</label>
                    <input type="text" name="products[0][name]" class="form-control" placeholder="e.g., Cotton Polo Shirt" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Price per Unit (USD) *</label>
                    <input type="number" name="products[0][price]" class="form-control" step="0.01" min="0" placeholder="e.g., 12.50" required>
                  </div>

                  <div class="col-md-12">
                    <label class="form-label fw-semibold">Product Description</label>
                    <textarea class="form-control" name="products[0][description]" rows="3" placeholder="Describe your product..."></textarea>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Product Category</label>
                    <input type="text" name="products[0][category]" class="form-control" placeholder="e.g., tshirts, hoodies">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Product Image</label>
                    <input type="file" name="products[0][image]" class="form-control" accept="image/*">
                    <small class="text-muted">Upload product image</small>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Minimum Order Quantity *</label>
                    <input type="number" name="products[0][min_order]" class="form-control" min="1" value="1" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Customization Options</label>
                    <input type="text" name="products[0][customizable]" class="form-control" placeholder="e.g., Logo Printing, Custom Colors">
                  </div>

                  <div class="col-md-12 d-flex align-items-center justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                      <i class="fas fa-trash me-1"></i>Remove Item
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php else: ?>
            <!-- Display submitted products if form validation failed -->
            <?php foreach ($products as $index => $product): ?>
              <div class="card border-0 shadow-sm rounded-4 mb-4 product-card">
                <div class="card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Product Name *</label>
                      <input type="text" name="products[<?php echo $index; ?>][name]" class="form-control" 
                             value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" placeholder="e.g., Cotton Polo Shirt" required>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Price per Unit (Ksh) *</label>
                      <input type="number" name="products[<?php echo $index; ?>][price]" class="form-control" step="0.01" min="0" 
                             value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" placeholder="e.g., 12.50" required>
                    </div>

                    <div class="col-md-12">
                      <label class="form-label fw-semibold">Product Description</label>
                      <textarea class="form-control" name="products[<?php echo $index; ?>][description]" rows="3" 
                                placeholder="Describe your product..."><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Product Category</label>
                      <input type="text" name="products[<?php echo $index; ?>][category]" class="form-control" 
                             value="<?php echo htmlspecialchars($product['category'] ?? ''); ?>" placeholder="e.g., tshirts, hoodies">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Product Image</label>
                      <input type="file" name="products[<?php echo $index; ?>][image]" class="form-control" accept="image/*">
                      <small class="text-muted">Upload product image</small>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Minimum Order Quantity *</label>
                      <input type="number" name="products[<?php echo $index; ?>][min_order]" class="form-control" min="1" 
                             value="<?php echo htmlspecialchars($product['min_order'] ?? '1'); ?>" required>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Customization Options</label>
                      <input type="text" name="products[<?php echo $index; ?>][customizable]" class="form-control" 
                             value="<?php echo htmlspecialchars($product['customizable'] ?? ''); ?>" placeholder="e.g., Logo Printing, Custom Colors">
                    </div>

                    <div class="col-md-12 d-flex align-items-center justify-content-between mt-3">
                      <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                        <i class="fas fa-trash me-1"></i>Remove Item
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Add Product Button -->
        <button type="button" id="addProductBtn" class="btn bg-secondary-custom text-white btn-hover-primary mt-2">
          <i class="fas fa-plus me-2"></i>Add Another Product
        </button>
      </div>

      <hr class="my-4">

      <!-- Submit -->
      <div class="text-center">
        <button type="submit" class="btn bg-primary-custom text-white px-5 py-3 rounded-3 btn-hover-primary fw-semibold fs-5">
          <i class="fas fa-rocket me-2"></i>Create Store
        </button>
        <p class="text-muted mt-2">Your store will be live immediately after creation</p>
      </div>
    </form>
  </main>

  <!-- Footer -->
  <footer class="bg-dark-bg-custom text-white mt-5">
    <div class="container-xl px-4 py-4 text-center">
      <p class="mb-0">&copy; 2025 PriMeri.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const productList = document.getElementById('product-list');
    const addProductBtn = document.getElementById('addProductBtn');
    let productCount = <?php echo count($products) ?: 1; ?>;

    addProductBtn.addEventListener('click', () => {
      const newIndex = productCount++;
      const newCard = document.createElement('div');
      newCard.className = 'card border-0 shadow-sm rounded-4 mb-4 product-card';
      newCard.innerHTML = `
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Product Name *</label>
              <input type="text" name="products[${newIndex}][name]" class="form-control" placeholder="e.g., Cotton Polo Shirt" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Price per Unit (USD) *</label>
              <input type="number" name="products[${newIndex}][price]" class="form-control" step="0.01" min="0" placeholder="e.g., 12.50" required>
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold">Product Description</label>
              <textarea class="form-control" name="products[${newIndex}][description]" rows="3" placeholder="Describe your product..."></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Product Category</label>
              <input type="text" name="products[${newIndex}][category]" class="form-control" placeholder="e.g., tshirts, hoodies">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Product Image</label>
              <input type="file" name="products[${newIndex}][image]" class="form-control" accept="image/*">
              <small class="text-muted">Upload product image</small>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Minimum Order Quantity *</label>
              <input type="number" name="products[${newIndex}][min_order]" class="form-control" min="1" value="1" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Customization Options</label>
              <input type="text" name="products[${newIndex}][customizable]" class="form-control" placeholder="e.g., Logo Printing, Custom Colors">
            </div>

            <div class="col-md-12 d-flex align-items-center justify-content-between mt-3">
              <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                <i class="fas fa-trash me-1"></i>Remove Item
              </button>
            </div>
          </div>
        </div>
      `;
      productList.appendChild(newCard);
    });

    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
        const removeBtn = e.target.classList.contains('remove-item') ? e.target : e.target.closest('.remove-item');
        const card = removeBtn.closest('.product-card');
        if (document.querySelectorAll('.product-card').length > 1) {
          card.remove();
        } else {
          alert('You must have at least one product.');
        }
      }
    });
  </script>
</body>
</html>