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
    </script>
    <script src="../js/cart.js"></script>
</body>
</html>