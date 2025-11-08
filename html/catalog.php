<?php
// catalog.php - Show categories instead of products
include '../php/connect.php';

// Get unique categories with some product info
$result = $db->query("
    SELECT category, COUNT(*) as product_count 
    FROM products 
    GROUP BY category
");

$categories = [
    'apparel' => ['name' => 'Branded Apparel & Uniforms', 'image' => '../images/Branded Apparel.png'],
    'food' => ['name' => 'Gourmet Food Products', 'image' => '../images/GourmetFoodProducts.png'],
    'beverage' => ['name' => 'Artisanal Beverages', 'image' => '../images/ArtisanalBeverages.png'],
    'packaging' => ['name' => 'Branded Retail Packaging', 'image' => '../images/BrandedRetailPackaging.png'],
    'gifts' => ['name' => 'Curated Corporate Gift Boxes', 'image' => '../images/GiftBoxes.png'],
    'engraving' => ['name' => 'Laser Engraved Goods', 'image' => '../images/EngravedGoods.png'],
    'health' => ['name' => 'Private Label Health & Beauty', 'image' => '../images/Health&Beauty.png'],
    'tech' => ['name' => 'Branded Tech Accessories', 'image' => '../images/BrandedTech.png']
];

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriMeri - Product Catalog</title>
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
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
                            <a class="nav-link text-dark fw-bold" href="catalog.php">Catalog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="stores.php">Stores</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="../php/contact.php">Contact</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center">
                        <a href="login.html">
                            <button class="btn btn-link text-dark me-2 transition">Log In</button>
                        </a>
                        <a href="signup.html">
                            <button class="btn bg-primary-custom text-white px-4 py-2 rounded-3 shadow btn-hover-primary">Get Started</button>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="container-xl px-4">
        <!-- Product Catalog View (The core content) -->
        <main id="catalog-view" class="pt-5 pb-5">
            <!-- Catalog Header -->
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bolder text-dark">Diverse Product Catalog</h1>
                <p class="fs-5 text-muted mx-auto" style="max-width: 700px;">Browse high-quality, customizable items available from our vetted manufacturing partners, spanning all key sectors.</p>
            </div>

            <!-- Category Showcase Grid (8 Cards for Variety) -->
            <div id="product-grid" class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
                <?php foreach ($categories as $categoryKey => $categoryInfo): ?>
                    <div class="col">
                        <div class="card shadow-lg h-100 border-0 rounded-4 overflow-hidden hover-scale-up">
                            <div class="image-container">
                                <img src="<?= $categoryInfo['image'] ?>" class="card-img-top" alt="<?= $categoryInfo['name'] ?>">
                            </div>
                            <div class="card-body p-3">
                                <h3 class="card-title fs-6 fw-semibold mb-1"><?= $categoryInfo['name'] ?></h3>
                                <p class="card-text text-muted small mb-3">
                                    <?php 
                                    $descriptions = [
                                        'apparel' => 'High-quality custom t-shirts, jackets, and corporate uniforms with durable logo application.',
                                        'food' => 'Custom branded snacks, candies, and packaged dry goods for retail or promotions.',
                                        'beverage' => 'Private-label water, juices, and specialized coffees/teas with custom bottling.',
                                        'packaging' => 'Custom-sized boxes, bags, and labels designed for high visual impact.',
                                        'gifts' => 'Sustainably sourced gift sets for client retention and staff appreciation.',
                                        'engraving' => 'Precision-cut keychains, plaques, and promotional items with durable materials.',
                                        'health' => 'Custom-formulated soaps, lotions, and sanitizers with branded packaging.',
                                        'tech' => 'Power banks, phone grips, and charging cables with precise logo placement.'
                                    ];
                                    echo $descriptions[$categoryKey];
                                    ?>
                                </p>
                                <a href="stores.php?category=<?= $categoryKey ?>">
                                    <button class="btn <?= in_array($categoryKey, ['food', 'packaging', 'tech']) ? 'bg-primary-custom' : 'bg-secondary-custom' ?> text-white w-100 btn-sm fw-medium btn-hover-primary">
                                        See Options
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- End Category Showcase Grid -->
        </main>
        <!-- End Product Catalog View -->
    </div>

    <!-- Footer -->
    <footer class="bg-dark-bg-custom text-white mt-5">
        <div class="container-xl px-4 py-4 text-center">
            <p class="mb-0">&copy; 2025 PriMeri.</p>
        </div>
    </footer>

    <!-- Load Bootstrap 5.3 JS Bundle (needed for Navbar Toggler, which handles mobile menu) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>