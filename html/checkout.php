<?php
// checkout.php
session_start();

// Debug: Check what's in the session
error_log("Session cart: " . print_r($_SESSION['cart'] ?? 'No cart', true));

// Check if cart exists in session
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // If no session cart, check localStorage via JavaScript fallback
    $_SESSION['checkout_error'] = 'Your cart is empty. Please add some products before checking out.';
    header("Location: stores.php");
    exit();
}

$cart = $_SESSION['cart'];
$total = 0;
$subtotal = 0;
$shipping = 500; // Fixed shipping cost
$taxRate = 0.05; // 5% tax

// Calculate totals
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * $taxRate;
$total = $subtotal + $shipping + $tax;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriMeri - Checkout</title>
    
    <!-- Load Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Link to external CSS files -->
    <link rel="stylesheet" href="../css/catalog.css">
    <link rel="stylesheet" href="../css/checkout.css">
</head>
<body class="bg-light-bg text-dark">
    <!-- Header / Navigation Bar -->
    <header class="bg-white shadow-sm sticky-top">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-xl px-4">
                <!-- Logo area -->
                <a href="#" class="navbar-brand fs-4 fw-bold text-primary-custom d-flex align-items-center">
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
                            <a class="nav-link text-dark" href="catalog.php">Catalog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="stores.php">Stores</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="../php/contact.php">Contact</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center">
                        <a href="../php/logout.php" class="btn bg-primary-custom text-white px-4 py-2 rounded-3 shadow btn-hover-primary">Logout</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="container-xl px-4 py-5">
        <!-- Checkout View -->
        <main id="checkout-view">
            <!-- Checkout Header -->
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bolder text-dark">Checkout</h1>
                <p class="fs-5 text-muted mx-auto" style="max-width: 700px;">Complete your order with secure payment</p>
            </div>

            <div class="row">
                <!-- Checkout Form -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-custom border-0 rounded-4 p-4 mb-4">
                        <h3 class="fw-semibold mb-4">Shipping Information</h3>
                        
                        <form id="shipping-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="postalCode" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postalCode" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="country" class="form-label">Country</label>
                                <select class="form-select" id="country" required>
                                    <option value="" selected disabled>Select Country</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Tanzania">Tanzania</option>
                                    <option value="Rwanda">Rwanda</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card shadow-custom border-0 rounded-4 p-4">
                        <h3 class="fw-semibold mb-4">Payment Method</h3>
                        
                        <!-- Payment Options -->
                        <div class="payment-options">
                            <div class="payment-option" id="mpesa-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="mpesa" checked>
                                    <label class="form-check-label fw-semibold" for="mpesa">
                                        <span class="mpesa-logo">M-PESA</span> Pay with M-Pesa
                                    </label>
                                </div>
                                <div id="mpesa-form" class="mt-3">
                                    <div class="mb-3">
                                        <label for="mpesa-phone" class="form-label">M-Pesa Phone Number</label>
                                        <input type="tel" class="form-control" id="mpesa-phone" placeholder="e.g., 0712 345 678" required>
                                        <div class="form-text">Enter the phone number registered with your M-Pesa account</div>
                                    </div>
                                    <button type="button" class="btn bg-primary-custom text-white w-100 btn-hover-primary" id="pay-with-mpesa">
                                        Pay KSh <?= number_format($total, 2) ?> with M-Pesa
                                    </button>
                                </div>
                            </div>
                            
                            <div class="payment-option" id="card-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="card">
                                    <label class="form-check-label fw-semibold" for="card">
                                        <span class="card-logo">CARD</span> Credit/Debit Card
                                    </label>
                                </div>
                                <div id="card-form" class="mt-3" style="display: none;">
                                    <div class="mb-3">
                                        <label for="card-number" class="form-label">Card Number</label>
                                        <input type="text" class="form-control" id="card-number" placeholder="1234 5678 9012 3456" disabled>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="expiry-date" class="form-label">Expiry Date</label>
                                            <input type="text" class="form-control" id="expiry-date" placeholder="MM/YY" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="cvv" class="form-label">CVV</label>
                                            <input type="text" class="form-control" id="cvv" placeholder="123" disabled>
                                        </div>
                                    </div>
                                    <button type="button" class="btn bg-secondary-custom text-white w-100 btn-hover-secondary" id="pay-with-card" disabled>
                                        Pay with Card
                                    </button>
                                    <div class="alert alert-info mt-3" role="alert">
                                        <small>Card payment is currently unavailable. Please use M-Pesa for payment.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- M-Pesa Payment Processing -->
                       <!-- M-Pesa Payment Processing -->
<div class="success-message" id="payment-success" style="display: none;">
    <h4 class="text-success">Order Placed Successfully!</h4>
    <p>Your order has been received and is being processed. You will receive a confirmation message shortly.</p>
    <p>Order ID: <strong>PRM-<?= date('Y') ?>-<?= rand(10000, 99999) ?></strong></p>
    <p>Status: <span class="badge bg-warning">Pending Confirmation</span></p>
    <a href="home.html" class="btn bg-primary-custom text-white mt-2">Continue Shopping</a>
    <a href="order-history.php" class="btn bg-secondary-custom text-white mt-2 ms-2">View Order History</a>
</div>
                        
                        <div class="text-center mt-3">
                            <div class="loader" id="payment-loader" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card shadow-custom border-0 rounded-4 p-4 sticky-summary">
                        <h3 class="fw-semibold mb-4">Order Summary</h3>
                        
                        <div class="order-summary-items mb-4">
                            <?php if (!empty($cart)): ?>
                                <?php foreach ($cart as $item): ?>
                                    <div class="order-summary-item mb-3 pb-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                                <small class="text-muted">Quantity: <?= $item['quantity'] ?></small>
                                            </div>
                                            <span class="fw-semibold">KSh <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Your cart is empty</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="order-summary-totals">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>KSh <?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <span>KSh <?= number_format($shipping, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (5%)</span>
                                <span>KSh <?= number_format($tax, 2) ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total</span>
                                <span>KSh <?= number_format($total, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <!-- End Checkout View -->
    </div>

    <!-- Footer -->
    <footer class="bg-dark-bg-custom text-white mt-5">
        <div class="container-xl px-4 py-4 text-center">
            <p class="mb-0">&copy; 2025 PriMeri.</p>
        </div>
    </footer>

    <!-- Load Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- Link to external JavaScript file -->
    <script src="../js/checkout.js"></script>

 
</body>
</html>