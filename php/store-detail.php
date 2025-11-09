<?php
// store-detail.php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "primeri";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get store ID from URL
    $store_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
    
    // Fetch store details
    $stmt = $conn->prepare("SELECT * FROM stores WHERE id = ?");
    $stmt->execute([$store_id]);
    $store = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Fetch store products
    $stmt = $conn->prepare("SELECT * FROM products WHERE store_id = ?");
    $stmt->execute([$store_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    $store = null;
    $products = [];
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_data = [
        'store_id' => $store_id,
        'product_id' => $_POST['product_id'],
        'name' => $_POST['product_name'],
        'price' => floatval($_POST['product_price']),
        'quantity' => intval($_POST['quantity']),
        'size' => $_POST['size'] ?? '',
        'color' => $_POST['color'] ?? '',
        'style' => $_POST['style'] ?? '',
        'embroidery' => isset($_POST['embroidery']) ? 1 : 0
    ];
    
    // Check if item already exists in cart
    $existing_index = -1;
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['product_id'] == $product_data['product_id'] && 
            $item['size'] == $product_data['size'] && 
            $item['color'] == $product_data['color'] &&
            $item['style'] == $product_data['style'] &&
            $item['embroidery'] == $product_data['embroidery']) {
            $existing_index = $index;
            break;
        }
    }
    
    if ($existing_index !== -1) {
        $_SESSION['cart'][$existing_index]['quantity'] += $product_data['quantity'];
    } else {
        $_SESSION['cart'][] = $product_data;
    }
    
    header("Location: store-detail.php?id=" . $store_id . "&added=1");
    exit;
}

// Handle remove from cart
if (isset($_GET['remove_from_cart'])) {
    $index = intval($_GET['remove_from_cart']);
    if (isset($_SESSION['cart'][$index])) {
        array_splice($_SESSION['cart'], $index, 1);
    }
    header("Location: store-detail.php?id=" . $store_id);
    exit;
}

// Handle clear cart
if (isset($_GET['clear_cart'])) {
    $_SESSION['cart'] = [];
    header("Location: store-detail.php?id=" . $store_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($store['name'] ?? 'Store'); ?> - PriMeri</title>
    <!-- Load Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-light-bg { background-color: #f8f9fa; }
        .bg-primary-custom { background-color: #3a86ff; }
        .bg-secondary-custom { background-color: #6c757d; }
        .text-primary-custom { color: #3a86ff; }
        .product-tag {
            display: inline-block;
            background-color: #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
        }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .cart-item { border-bottom: 1px solid #dee2e6; padding: 1rem 0; }
        .cart-summary { background-color: #f8f9fa; border-radius: 0.5rem; }
        .option-group { margin-bottom: 1rem; }
        .option-label { font-weight: 500; margin-bottom: 0.5rem; }
        .currency { font-weight: bold; }
        .toast { position: fixed; top: 20px; right: 20px; z-index: 9999; }
    </style>
</head>
<body class="bg-light-bg text-dark">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky-top">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-xl px-4">
                <a href="#" class="navbar-brand fs-4 fw-bold text-primary-custom d-flex align-items-center">
                    <img src="../images/logo.jpg" alt="PriMeri Logo" width="48" height="48" class="me-2">
                    PriMeri
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link text-dark" href="home.html">Home</a></li>
                        <li class="nav-item"><a class="nav-link text-dark" href="catalog.html">Catalog</a></li>
                        <li class="nav-item"><a class="nav-link text-dark fw-bold" href="stores.html">Stores</a></li>
                        <li class="nav-item"><a class="nav-link text-dark" href="../php/contact.php">Contact</a></li>
                    </ul>
                    <div class="d-flex align-items-center">
                        <a href="login.html"><button class="btn btn-link text-dark me-2">Log In</button></a>
                        <a href="signup.html"><button class="btn bg-primary-custom text-white px-4 py-2 rounded-3 shadow">Get Started</button></a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="container-xl px-4 py-5">
        <?php if ($store): ?>
        <!-- Store Information -->
        <div class="row mb-5">
            <div class="col-md-4">
                <img src="../images/<?php echo htmlspecialchars($store['image']); ?>" class="img-fluid rounded shadow" alt="<?php echo htmlspecialchars($store['name']); ?>">
            </div>
            <div class="col-md-8">
                <h1 class="display-5 fw-bolder text-dark"><?php echo htmlspecialchars($store['name']); ?></h1>
                <p class="fs-5 text-muted"><?php echo htmlspecialchars($store['description']); ?></p>
                <div class="mb-3">
                    <?php
                    $tags = explode(',', $store['tags']);
                    foreach ($tags as $tag): 
                    ?>
                        <span class="product-tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2">Open</span>
                    <small class="text-muted">Mon-Fri: 9AM-6PM</small>
                </div>
            </div>
        </div>

        <!-- Products and Cart Section -->
        <div class="row">
            <!-- Products Column -->
            <div class="col-lg-8">
                <h2 class="mb-4">Products</h2>
                
                <?php foreach ($products as $product): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img src="../images/<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                <p class="card-text"><strong>KSH <?php echo number_format($product['price'], 2); ?></strong></p>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                                    <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                                    
                                    <?php if ($product['has_sizes']): ?>
                                    <div class="option-group">
                                        <div class="option-label">Size:</div>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?php
                                            $sizes = explode(',', $product['sizes']);
                                            foreach ($sizes as $size):
                                            ?>
                                            <input type="radio" class="btn-check" name="size" id="size-<?php echo $product['id']; ?>-<?php echo $size; ?>" value="<?php echo $size; ?>" <?php echo $size === 'M' ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-primary" for="size-<?php echo $product['id']; ?>-<?php echo $size; ?>"><?php echo $size; ?></label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['has_colors']): ?>
                                    <div class="option-group">
                                        <div class="option-label">Color:</div>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?php
                                            $colors = explode(',', $product['colors']);
                                            foreach ($colors as $color):
                                            ?>
                                            <input type="radio" class="btn-check" name="color" id="color-<?php echo $product['id']; ?>-<?php echo $color; ?>" value="<?php echo $color; ?>" <?php echo $color === $colors[0] ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-dark" for="color-<?php echo $product['id']; ?>-<?php echo $color; ?>"><?php echo $color; ?></label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['has_styles']): ?>
                                    <div class="option-group">
                                        <div class="option-label">Style:</div>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?php
                                            $styles = explode(',', $product['styles']);
                                            foreach ($styles as $style):
                                            ?>
                                            <input type="radio" class="btn-check" name="style" id="style-<?php echo $product['id']; ?>-<?php echo $style; ?>" value="<?php echo $style; ?>" <?php echo $style === $styles[0] ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-primary" for="style-<?php echo $product['id']; ?>-<?php echo $style; ?>"><?php echo $style; ?></label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['has_embroidery']): ?>
                                    <div class="option-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="embroidery" id="embroidery-<?php echo $product['id']; ?>" value="1">
                                            <label class="form-check-label" for="embroidery-<?php echo $product['id']; ?>">
                                                Add logo embroidery (+KSH 500.00)
                                            </label>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="input-group" style="width: 130px;">
                                            <button class="btn btn-outline-secondary" type="button" onclick="decrementQuantity(<?php echo $product['id']; ?>)">-</button>
                                            <input type="number" class="form-control text-center" name="quantity" id="quantity-<?php echo $product['id']; ?>" value="1" min="1" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="incrementQuantity(<?php echo $product['id']; ?>)">+</button>
                                        </div>
                                        <button type="submit" name="add_to_cart" class="btn bg-primary-custom text-white">Add to Cart</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Cart Column -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 100px;">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary-custom text-white">
                            <h5 class="mb-0">Your Cart</h5>
                        </div>
                        <div class="card-body">
                            <div id="cart-items">
                                <?php if (empty($_SESSION['cart'])): ?>
                                    <p class="text-muted text-center">Your cart is empty</p>
                                <?php else: ?>
                                    <?php 
                                    $subtotal = 0;
                                    foreach ($_SESSION['cart'] as $index => $item): 
                                        $item_total = $item['price'] * $item['quantity'];
                                        $subtotal += $item_total;
                                    ?>
                                    <div class="cart-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo $item['size'] ? "Size: {$item['size']}" : ''; ?>
                                                    <?php echo $item['color'] ? " | Color: {$item['color']}" : ''; ?>
                                                    <?php echo $item['style'] ? " | Style: {$item['style']}" : ''; ?>
                                                    <?php echo $item['embroidery'] ? ' | With Embroidery' : ''; ?>
                                                </small>
                                                <div class="d-flex align-items-center mt-1">
                                                    <span>Qty: <?php echo $item['quantity']; ?></span>
                                                    <a href="?id=<?php echo $store_id; ?>&remove_from_cart=<?php echo $index; ?>" class="btn btn-sm btn-link text-danger ms-2">Remove</a>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div>KSH <?php echo number_format($item_total, 2); ?></div>
                                                <small class="text-muted">KSH <?php echo number_format($item['price'], 2); ?> each</small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($_SESSION['cart'])): ?>
                            <?php
                            $tax = $subtotal * 0.16; // 16% VAT in Kenya
                            $total = $subtotal + $tax;
                            ?>
                            <div class="cart-summary p-3 mt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>KSH <?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>VAT (16%):</span>
                                    <span>KSH <?php echo number_format($tax, 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 fw-bold">
                                    <span>Total:</span>
                                    <span>KSH <?php echo number_format($total, 2); ?></span>
                                </div>
                                <a href="checkout.php?store_id=<?php echo $store_id; ?>" class="btn bg-primary-custom text-white w-100">Proceed to Checkout</a>
                                <a href="?id=<?php echo $store_id; ?>&clear_cart=1" class="btn btn-outline-secondary w-100 mt-2">Clear Cart</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <div class="alert alert-danger text-center">
            <h4>Store not found</h4>
            <p>The store you're looking for doesn't exist or has been removed.</p>
            <a href="stores.html" class="btn bg-primary-custom text-white">Back to Stores</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5">
        <div class="container-xl px-4 py-4 text-center">
            <p class="mb-0">&copy; 2025 PriMeri.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Quantity control functions
        function incrementQuantity(productId) {
            const quantityInput = document.getElementById('quantity-' + productId);
            quantityInput.value = parseInt(quantityInput.value) + 1;
        }
        
        function decrementQuantity(productId) {
            const quantityInput = document.getElementById('quantity-' + productId);
            if (parseInt(quantityInput.value) > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
            }
        }
        
        // Show toast if item was added
        <?php if (isset($_GET['added'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-bg-success border-0';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        Item added to cart successfully!
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', function() {
                document.body.removeChild(toast);
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>
<?php $conn = null; ?>