<?php
// catalog.php
session_start();
require '../php/conf.php'; // contains DB_HOST, DB_USER, DB_PASS, DB_NAME
include '../php/connect.php';

// Optional: filter by category from query string
$category = $_GET['category'] ?? '';
if ($category) {
    $stmt = $db->prepare("SELECT * FROM products WHERE category=?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $db->query("SELECT * FROM products");
}

$products = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriMeri - Product Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/catalog.css">
</head>
<body class="bg-light-bg text-dark">
    
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
                        <li class="nav-item"><a class="nav-link text-dark" href="catalog.php">Catalog</a></li>
                        <li class="nav-item"><a class="nav-link text-dark" href="stores.php">Stores</a></li>
                        <li class="nav-item"><a class="nav-link text-dark" href="../php/contact.php">Contact</a></li>
                    </ul>
                    <div class="d-flex align-items-center">
                        <a href="login.html"><button class="btn btn-link text-dark me-2 transition">Log In</button></a>
                        <a href="signup.html"><button class="btn bg-primary-custom text-white px-4 py-2 rounded-3 shadow btn-hover-primary">Get Started</button></a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="container-xl px-4">
        <main id="catalog-view" class="pt-5 pb-5">
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bolder text-dark">Diverse Product Catalog</h1>
                <p class="fs-5 text-muted mx-auto" style="max-width: 700px;">
                    Browse high-quality, customizable items available from our vetted manufacturing partners, spanning all key sectors.
                </p>
            </div>

            <div id="product-grid" class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col">
                            <div class="card shadow-lg h-100 border-0 rounded-4 overflow-hidden hover-scale-up">
                                <div class="image-container">
                                    <img src="<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                                </div>
                                <div class="card-body p-3">
                                    <h3 class="card-title fs-6 fw-semibold mb-1"><?= htmlspecialchars($product['name']) ?></h3>
                                    <p class="card-text text-muted small mb-3"><?= htmlspecialchars($product['description']) ?></p>
                                    <a href="<?= htmlspecialchars($product['link']) ?>">
                                        <button class="btn <?= ($product['category'] === 'food' || $product['category'] === 'packaging' || $product['category'] === 'tech') ? 'bg-primary-custom' : 'bg-secondary-custom' ?> text-white w-100 btn-sm fw-medium btn-hover-primary">
                                            See Options
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No products found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer class="bg-dark-bg-custom text-white mt-5">
        <div class="container-xl px-4 py-4 text-center">
            <p class="mb-0">&copy; 2025 PriMeri.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
