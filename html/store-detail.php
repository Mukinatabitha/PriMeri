<?php
// Include database connection
include '../php/connect.php';

// Check if connection was successful
if (!$db) {
    die("Database connection failed");
}

// Get store ID from URL
$storeId = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Fetch store data
$storeQuery = $db->prepare("SELECT * FROM stores WHERE id = ?");
$storeQuery->bind_param("i", $storeId);
$storeQuery->execute();
$storeResult = $storeQuery->get_result();
$store = $storeResult->fetch_assoc();

// Fetch products for this store
$productsQuery = $db->prepare("SELECT * FROM products WHERE store_id = ? ORDER BY id ASC");
$productsQuery->bind_param("i", $storeId);
$productsQuery->execute();
$productsResult = $productsQuery->get_result();
$products = [];
while ($product = $productsResult->fetch_assoc()) {
    $products[] = $product;
}

// If store not found, show error
if (!$store) {
    die("Store not found");
}

// Process store data for display
$tags = !empty($store['tags']) ? explode(',', $store['tags']) : [];
$features = !empty($store['features']) ? explode(',', $store['features']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriMeri - <?php echo htmlspecialchars($store['name']); ?></title>
    
    <!-- Load Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <style>
        /* Define Custom Colors */
        :root {
            --primary: #3A4D39; /* Dark Olive Green */
            --secondary: #8E7C68; /* Muted Brown/Taupe */
            --light-bg: #FAF8F3; /* Soft Cream Background */
            --dark-bg: #3A4D39;
            --success: #4A7C59; /* Muted Green for success */
            --bs-body-bg: var(--light-bg);
            --bs-body-color: #343a40; /* Darker text */
        }

        /* Custom Utility Classes to map to the brand colors */
        .bg-primary-custom {
            background-color: var(--primary) !important;
        }
        .text-primary-custom {
            color: var(--primary) !important;
        }
        .bg-secondary-custom {
            background-color: var(--secondary) !important;
        }
        .text-secondary-custom {
            color: var(--secondary) !important;
        }
        .bg-dark-bg-custom {
            background-color: var(--dark-bg) !important;
        }

        /* Setting a professional, clean font - Using a common system fallback */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: var(--light-bg);
            color: var(--bs-body-color);
        }

        /* Custom Hover Effects (Tailwind-like) */
        .btn-hover-primary:hover {
            background-color: #2b392b !important;
            border-color: #2b392b !important;
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        .btn-hover-secondary:hover {
            background-color: #7a6b5a !important;
            border-color: #7a6b5a !important;
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        .hover-scale-up:hover {
            transform: scale(1.02);
            transition: transform 0.3s ease-in-out;
        }

        /* Image Container Styling */
        .image-container {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: #f8f9fa;
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Store Detail Page Specific Styles */
        .store-banner {
            background: linear-gradient(135deg, var(--primary) 0%, #2b392b 100%);
        }

        .store-detail-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(58, 77, 57, 0.1);
            transition: all 0.3s ease;
            background-color: white;
        }

        .store-detail-card:hover {
            box-shadow: 0 6px 18px rgba(58, 77, 57, 0.15);
        }

        .product-card {
            border: none;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .product-image {
            height: 180px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 2;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 5px;
        }

        .cart-sidebar {
            position: sticky;
            top: 100px;
        }

        .cart-item {
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-remove {
            color: #dc3545;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .cart-item-remove:hover {
            color: #bd2130;
        }

        .empty-cart {
            color: #6c757d;
        }

        .empty-cart i {
            opacity: 0.5;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(58, 77, 57, 0.25);
        }

        .store-feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .store-feature-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(58, 77, 57, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary);
        }

        .store-tag {
            background-color: rgba(142, 124, 104, 0.1);
            color: var(--secondary);
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 0.85rem;
            margin-right: 8px;
            margin-bottom: 8px;
            display: inline-block;
        }

        .cart-notification {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1050;
            display: none;
        }

        @keyframes cartUpdate {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .cart-update {
            animation: cartUpdate 0.3s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .cart-sidebar {
                position: static;
                margin-top: 2rem;
            }
        }

        @media (max-width: 767.98px) {
            .store-banner {
                padding: 2rem 1rem !important;
            }
            
            .store-banner h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body class="bg-light-bg text-dark">
    <!-- Header / Navigation Bar -->
    <header class="bg-white shadow-sm sticky-top">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-xl px-4">
                <!-- Logo area -->
                <a href="home.html" class="navbar-brand fs-4 fw-bold text-primary-custom d-flex align-items-center">
                    <img src="../images/logo.jpg" alt="PriMeri Logo" width="48" height="48" class="me-2">
                    PriMeri
                </a>

                <!-- Navbar Toggler for mobile support -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="home.html">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="catalog.html">Catalog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="stores.php">Stores</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="../php/contact.php">Contact</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center">
                      <a href="../php/logout.php"  class="btn bg-primary-custom text-white px-4 py-2 rounded-3 shadow btn-hover-primary">Logout</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Cart Notification -->
    <div class="cart-notification alert alert-success alert-dismissible fade show" role="alert">
        <strong>Item added to cart!</strong> Your item has been successfully added to your shopping cart.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="container-xl px-4 py-5">
        <!-- Store Detail View -->
        <main id="store-detail-view">
            <!-- Store will be dynamically loaded here -->
        </main>
        <!-- End Store Detail View -->
    </div>

    <!-- Footer -->
    <footer class="bg-dark-bg-custom text-white mt-5">
        <div class="container-xl px-4 py-4 text-center">
            <p class="mb-0">&copy; 2025 PriMeri.</p>
        </div>
    </footer>

    <!-- Load Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
        // Store data from PHP
        const store = <?php echo json_encode($store); ?>;
        const products = <?php echo json_encode($products); ?>;

        // Cart state
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        document.addEventListener('DOMContentLoaded', function() {
            if (store) {
                renderStoreDetail(store, products);
                updateCartDisplay();
            } else {
                document.getElementById('store-detail-view').innerHTML = `
                    <div class="text-center py-5">
                        <h2 class="text-muted">Store not found</h2>
                        <p class="text-muted">The store you're looking for doesn't exist.</p>
                        <a href="stores.php" class="btn bg-primary-custom text-white mt-3">Back to Stores</a>
                    </div>
                `;
            }
        });

        // Render store details
        function renderStoreDetail(store, products) {
            const storeDetailView = document.getElementById('store-detail-view');
            
            // Process tags and features from string to array
            const tags = store.tags ? store.tags.split(',') : [];
            const features = store.features ? store.features.split(',') : [];
            
            storeDetailView.innerHTML = `
                <!-- Store Header -->
                <div class="store-header text-center mb-5">
                    <div class="store-banner text-white rounded-4 p-5 mb-4">
                        <h1 class="display-5 fw-bolder mb-3">${store.name}</h1>
                        <p class="fs-5 mb-0 mx-auto" style="max-width: 700px;">${store.description}</p>
                    </div>
                    
                    <!-- Store Info -->
                    <div class="store-info row g-4 mb-5">
                        <div class="col-md-4">
                            <div class="info-card p-3 rounded-3 bg-white shadow-sm">
                                <h6 class="fw-semibold text-primary-custom mb-2">Minimum Order</h6>
                                <p class="mb-0">${store.minOrder} units per product</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card p-3 rounded-3 bg-white shadow-sm">
                                <h6 class="fw-semibold text-primary-custom mb-2">Lead Time</h6>
                                <p class="mb-0">${store.leadTime}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card p-3 rounded-3 bg-white shadow-sm">
                                <h6 class="fw-semibold text-primary-custom mb-2">Customization</h6>
                                <p class="mb-0">${store.customization}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Products Section -->
                    <div class="col-lg-8 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="fw-bolder text-dark">Available Products</h2>
                            <div class="cart-indicator bg-primary-custom text-white rounded-pill px-3 py-1">
                                <span id="cart-count">0</span> items in cart
                            </div>
                        </div>

                        <!-- Store Features -->
                        <div class="card store-detail-card p-4 mb-4">
                            <h3 class="fw-bolder text-dark mb-4">Why Choose ${store.name}?</h3>
                            <div class="row">
                                ${features.map(feature => `
                                    <div class="col-md-6 mb-3">
                                        <div class="store-feature-item">
                                            <div class="store-feature-icon">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                                </svg>
                                            </div>
                                            <span>${feature.trim()}</span>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>

                        <!-- Product Tags -->
                        <div class="mb-4">
                            <h4 class="fw-semibold text-dark mb-3">Product Categories</h4>
                            <div>
                                ${tags.map(tag => `<span class="store-tag">${tag.trim()}</span>`).join('')}
                            </div>
                        </div>

                        <!-- Products Grid -->
                        <div class="products-grid row g-4" id="products-container">
                            ${products.map(product => createProductCard(product)).join('')}
                        </div>
                    </div>

                    <!-- Cart Section -->
                    <div class="col-lg-4">
                        <div class="cart-sidebar card store-detail-card p-4">
                            <h3 class="fw-semibold mb-4">Your Order</h3>
                            
                            <!-- Cart Items -->
                            <div class="cart-items mb-4" id="cart-items">
                                <div class="empty-cart text-center text-muted py-4">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="mb-3">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                    <p class="mb-0">Your cart is empty</p>
                                    <small>Add products from the store to get started</small>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="order-summary" id="order-summary" style="display: none;">
                                <div class="order-summary-items mb-3" id="order-summary-items">
                                    <!-- Items will be populated here -->
                                </div>
                                
                                <div class="order-summary-totals">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal</span>
                                        <span id="subtotal">KSh 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Shipping</span>
                                        <span id="shipping">KSh 500</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax (5%)</span>
                                        <span id="tax">KSh 0</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold fs-5 mb-4">
                                        <span>Total</span>
                                        <span id="total">KSh 0</span>
                                    </div>
                                </div>

                                <!-- Checkout Button -->
                                <a href="checkout.php" class="btn bg-primary-custom text-white w-100 btn-hover-primary" id="checkout-btn">
                                    Proceed to Checkout
                                </a>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">Minimum order: ${store.minOrder} units per product</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Attach event listeners to the newly created elements
            attachProductEventListeners();
            attachCartEventListeners();
            
            // Update cart count
            updateCartCount();
        }

        // Create product card HTML
        function createProductCard(product) {
            const cartItem = cart.find(item => item.id === product.id);
            const currentQuantity = cartItem ? cartItem.quantity : 0;
            const customization = product.customization ? product.customization.split(',') : [];
            
            return `
                <div class="col-md-6">
                    <div class="card product-card shadow-lg h-100 border-0 rounded-4 overflow-hidden" data-category="${product.category}">
                        <div class="position-relative">
                            <img src="${product.image}" class="card-img-top product-image" alt="${product.name}">
                            ${product.inStock ? 
                                '<span class="product-badge badge bg-success">In Stock</span>' : 
                                '<span class="product-badge badge bg-danger">Out of Stock</span>'
                            }
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title fw-semibold mb-2">${product.name}</h5>
                            <p class="card-text text-muted small mb-3">${product.description}</p>
                            
                            <div class="mb-3">
                                ${customization.map(item => 
                                    `<span class="badge bg-light text-dark me-1 mb-1">${item.trim()}</span>`
                                ).join('')}
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="fw-bold text-primary-custom fs-5">KSh ${parseFloat(product.price).toLocaleString()}</span>
                                    <small class="text-muted d-block">per unit</small>
                                </div>
                                <small class="text-muted">Min: ${product.minOrder} units</small>
                            </div>
                            
                            <div class="quantity-controls mb-3">
                                <button class="quantity-btn minus" data-product-id="${product.id}">-</button>
                                <input type="number" 
                                       class="quantity-input" 
                                       id="quantity-${product.id}" 
                                       value="${currentQuantity}" 
                                       min="0" 
                                       step="${product.minOrder}"
                                       data-product-id="${product.id}">
                                <button class="quantity-btn plus" data-product-id="${product.id}">+</button>
                            </div>
                            
                            <button class="btn ${currentQuantity > 0 ? 'btn-success' : 'bg-primary-custom'} text-white w-100 btn-hover-primary add-to-cart" 
                                    data-product-id="${product.id}"
                                    ${!product.inStock ? 'disabled' : ''}>
                                ${currentQuantity > 0 ? 'Update Cart' : 'Add to Cart'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // [Rest of the JavaScript functions remain the same as in your original file]
        // ... (attachProductEventListeners, quantity control functions, cart functionality, etc.)

        // Attach event listeners to product elements
    function attachProductEventListeners() {
    // Remove existing listeners first to avoid duplicates
    document.querySelectorAll('.quantity-btn.plus').forEach(btn => {
        btn.replaceWith(btn.cloneNode(true));
    });
    document.querySelectorAll('.quantity-btn.minus').forEach(btn => {
        btn.replaceWith(btn.cloneNode(true));
    });
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.replaceWith(input.cloneNode(true));
    });
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.replaceWith(btn.cloneNode(true));
    });
    
    // Quantity buttons
    document.querySelectorAll('.quantity-btn.plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = parseInt(this.dataset.productId);
            increaseQuantity(productId);
        });
    });
    
    document.querySelectorAll('.quantity-btn.minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = parseInt(this.dataset.productId);
            decreaseQuantity(productId);
        });
    });
    
    // Quantity inputs
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = parseInt(this.dataset.productId);
            const quantity = parseInt(this.value) || 0;
            updateProductQuantity(productId, quantity);
        });
    });
    
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = parseInt(this.dataset.productId);
            addToCart(productId);
        });
    });
}
        // Quantity control functions
        function increaseQuantity(productId) {
            const input = document.getElementById(`quantity-${productId}`);
            const product = products.find(p => p.id === productId);
            const currentValue = parseInt(input.value) || 0;
            const newValue = currentValue + product.minOrder;
            input.value = newValue;
            updateProductQuantity(productId, newValue);
        }

        function decreaseQuantity(productId) {
            const input = document.getElementById(`quantity-${productId}`);
            const product = products.find(p => p.id === productId);
            const currentValue = parseInt(input.value) || 0;
            const newValue = Math.max(0, currentValue - product.minOrder);
            input.value = newValue;
            updateProductQuantity(productId, newValue);
        }

        function updateProductQuantity(productId, quantity) {
            const product = products.find(p => p.id === productId);
            const btn = document.querySelector(`.add-to-cart[data-product-id="${productId}"]`);
            
            if (quantity > 0) {
                btn.textContent = 'Update Cart';
                btn.classList.remove('bg-primary-custom');
                btn.classList.add('btn-success');
            } else {
                btn.textContent = 'Add to Cart';
                btn.classList.remove('btn-success');
                btn.classList.add('bg-primary-custom');
            }
            
            // Validate minimum order
            if (quantity > 0 && quantity < product.minOrder) {
                input.value = product.minOrder;
                alert(`Minimum order for ${product.name} is ${product.minOrder} units.`);
                return product.minOrder;
            }
            
            return quantity;
        }

        // Cart functionality
        function addToCart(productId) {
            const input = document.getElementById(`quantity-${productId}`);
            const quantity = parseInt(input.value) || 0;
            const product = products.find(p => p.id === productId);
            
            if (quantity < product.minOrder) {
                alert(`Minimum order for ${product.name} is ${product.minOrder} units.`);
                input.value = product.minOrder;
                return;
            }
            
            if (quantity === 0) {
                removeFromCart(productId);
                return;
            }
            
            const existingItemIndex = cart.findIndex(item => item.id === productId);
            
            if (existingItemIndex > -1) {
                cart[existingItemIndex].quantity = quantity;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    quantity: quantity,
                    image: product.image,
                    minOrder: product.minOrder,
                    storeId: store.id
                });
            }
            
            updateCart();
            showCartNotification(`${product.name} ${existingItemIndex > -1 ? 'updated' : 'added'} to cart`);
        }

        function removeFromCart(productId) {
            cart = cart.filter(item => item.id !== productId);
            updateCart();
            
            // Reset product card
            const input = document.getElementById(`quantity-${productId}`);
            const btn = document.querySelector(`.add-to-cart[data-product-id="${productId}"]`);
            
            if (input && btn) {
                input.value = 0;
                btn.textContent = 'Add to Cart';
                btn.classList.remove('btn-success');
                btn.classList.add('bg-primary-custom');
            }

        }

        function updateCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartDisplay();
    updateCartCount();
    attachCartEventListeners(); // This should re-attach listeners
}

function updateCartDisplay() {
    const cartItems = document.getElementById('cart-items');
    const orderSummary = document.getElementById('order-summary');
    
    if (!cartItems) return;
    
    const emptyCart = cartItems.querySelector('.empty-cart');
    
    if (cart.length === 0) {
        if (emptyCart) emptyCart.style.display = 'block';
        if (orderSummary) orderSummary.style.display = 'none';
        return;
    }
    
    if (emptyCart) emptyCart.style.display = 'none';
    if (orderSummary) orderSummary.style.display = 'block';
    
    // Update cart items
    cartItems.innerHTML = '';
    cart.forEach(item => {
        const cartItemElement = createCartItem(item);
        cartItems.appendChild(cartItemElement);
    });
    
    // Update order summary
    updateOrderSummary();
    
    // Re-attach event listeners after updating the DOM
    attachCartEventListeners();
}

function createCartItem(item) {
    const div = document.createElement('div');
    div.className = 'cart-item';
    div.innerHTML = `
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h6 class="fw-semibold mb-1">${item.name}</h6>
                <p class="text-muted small mb-2">KSh ${item.price.toLocaleString()} per unit</p>
                <div class="d-flex align-items-center">
                    <button class="cart-quantity-btn minus me-2" data-product-id="${item.id}">-</button>
                    <input type="number" 
                           class="cart-quantity-input" 
                           value="${item.quantity}" 
                           min="${item.minOrder}" 
                           step="${item.minOrder}"
                           data-product-id="${item.id}">
                    <button class="cart-quantity-btn plus ms-2" data-product-id="${item.id}">+</button>
                    <small class="text-muted ms-2">units</small>
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold text-primary-custom mb-2">
                    KSh ${(item.price * item.quantity).toLocaleString()}
                </div>
                <button class="cart-item-remove" data-product-id="${item.id}" type="button">
                    &times;
                </button>
            </div>
        </div>
    `;
    
    return div;
}

function attachCartEventListeners() {
    console.log('Attaching cart event listeners...'); // Debug log
    
    // Cart quantity buttons - PLUS
    document.querySelectorAll('.cart-quantity-btn.plus').forEach(btn => {
        btn.removeEventListener('click', handlePlusClick); // Remove existing listeners
        btn.addEventListener('click', handlePlusClick);
    });
    
    // Cart quantity buttons - MINUS
    document.querySelectorAll('.cart-quantity-btn.minus').forEach(btn => {
        btn.removeEventListener('click', handleMinusClick); // Remove existing listeners
        btn.addEventListener('click', handleMinusClick);
    });
    
    // Cart quantity inputs
    document.querySelectorAll('.cart-quantity-input').forEach(input => {
        input.removeEventListener('change', handleInputChange); // Remove existing listeners
        input.addEventListener('change', handleInputChange);
    });
    
    // Remove buttons
    document.querySelectorAll('.cart-item-remove').forEach(btn => {
        btn.removeEventListener('click', handleRemoveClick); // Remove existing listeners
        btn.addEventListener('click', handleRemoveClick);
    });
    
    // Checkout button
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.removeEventListener('click', handleCheckoutClick);
        checkoutBtn.addEventListener('click', handleCheckoutClick);
    }
}

// Separate handler functions to avoid duplicate listeners
function handlePlusClick() {
    const productId = parseInt(this.dataset.productId);
    const item = cart.find(item => item.id === productId);
    if (item) {
        const newQuantity = item.quantity + item.minOrder;
        updateCartItemQuantity(productId, newQuantity);
    }
}

function handleMinusClick() {
    const productId = parseInt(this.dataset.productId);
    const item = cart.find(item => item.id === productId);
    if (item) {
        const newQuantity = Math.max(item.minOrder, item.quantity - item.minOrder);
        updateCartItemQuantity(productId, newQuantity);
    }
}

function handleInputChange() {
    const productId = parseInt(this.dataset.productId);
    const quantity = parseInt(this.value) || 0;
    updateCartItemQuantity(productId, quantity);
}

function handleRemoveClick() {
    const productId = parseInt(this.dataset.productId);
    console.log('Remove button clicked for product:', productId); // Debug log
    removeFromCart(productId);
}

function handleCheckoutClick(e) {
    if (cart.length === 0) {
        e.preventDefault();
        alert('Please add some products to your cart before proceeding to checkout.');
    }
}

function removeFromCart(productId) {
    console.log('Removing product from cart:', productId); // Debug log
    console.log('Cart before removal:', cart); // Debug log
    
    cart = cart.filter(item => item.id !== productId);
    
    console.log('Cart after removal:', cart); // Debug log
    
    updateCart();
    
    // Reset product card
    const input = document.getElementById(`quantity-${productId}`);
    const btn = document.querySelector(`.add-to-cart[data-product-id="${productId}"]`);
    
    if (input && btn) {
        input.value = 0;
        btn.textContent = 'Add to Cart';
        btn.classList.remove('btn-success');
        btn.classList.add('bg-primary-custom');
    }
    
    showCartNotification('Item removed from cart');
}

function updateCartItemQuantity(productId, quantity) {
    const item = cart.find(item => item.id === productId);
    
    if (!item) return;
    
    if (quantity < item.minOrder) {
        alert(`Minimum order for ${item.name} is ${item.minOrder} units.`);
        quantity = item.minOrder;
    }
    
    if (quantity === 0) {
        removeFromCart(productId);
        return;
    }
    
    item.quantity = quantity;
    updateCart();
    
    // Update product card
    const productInput = document.getElementById(`quantity-${productId}`);
    const productBtn = document.querySelector(`.add-to-cart[data-product-id="${productId}"]`);
    
    if (productInput && productBtn) {
        productInput.value = quantity;
        productBtn.textContent = 'Update Cart';
        productBtn.classList.remove('bg-primary-custom');
        productBtn.classList.add('btn-success');
    }
}

function updateOrderSummary() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const shipping = 500;
    const tax = subtotal * 0.05; // 5% tax
    const total = subtotal + shipping + tax;
            
            document.getElementById('subtotal').textContent = `KSh ${subtotal.toLocaleString()}`;
            document.getElementById('tax').textContent = `KSh ${tax.toLocaleString()}`;
            document.getElementById('total').textContent = `KSh ${total.toLocaleString()}`;
            
            // Update order summary items
            const summaryItems = document.getElementById('order-summary-items');
            summaryItems.innerHTML = '';
            
            cart.forEach(item => {
                const div = document.createElement('div');
                div.className = 'order-summary-item';
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span>${item.name} (${item.quantity} units)</span>
                        <span class="fw-semibold">KSh ${(item.price * item.quantity).toLocaleString()}</span>
                    </div>
                `;
                summaryItems.appendChild(div);
            });
        }

        function showCartNotification(message) {
            const notification = document.querySelector('.cart-notification');
            notification.querySelector('strong').textContent = message;
            notification.style.display = 'block';
            
            // Hide notification after 3 seconds
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>