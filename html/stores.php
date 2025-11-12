<?php
// Include your existing database connection
include '../php/connect.php';

// Check if connection was successful
if (!$db) {
    die("Database connection failed");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriMeri - Partner Stores</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/catalog.css">
    <link rel="stylesheet" href="../css/stores.css">
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
                    <li class="nav-item"><a class="nav-link text-dark fw-bold" href="stores.php">Stores</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="../php/contact.php">Contact</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="../php/logout.php">
                        <button class="btn bg-primary-custom text-white px-4 py-2 rounded-3 shadow btn-hover-primary">Logout</button>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

<div class="container-xl px-4">
    <main id="stores-view" class="pt-5 pb-5">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bolder text-dark">Our Partner Stores</h1>
            <p class="fs-5 text-muted mx-auto" style="max-width: 700px;">Discover trusted manufacturers and suppliers offering diverse products through our platform.</p>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="search-input" placeholder="Search stores...">
                    <button class="btn bg-primary-custom text-white" type="button" id="search-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <select class="form-select" id="category-filter">
                    <option value="all" selected>All Categories</option>
                    <?php
                    // Fetch categories from categories table
                    $catQuery = $db->query("SELECT category_id, name FROM categories ORDER BY name");
                    if ($catQuery && $catQuery->num_rows > 0) {
                        while($cat = $catQuery->fetch_assoc()) {
                            echo '<option value="'.htmlspecialchars($cat['category_id']).'">'.htmlspecialchars($cat['name']).'</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <button class="btn btn-outline-primary btn-sm category-filter-btn" data-category="all">All Stores</button>
            <?php
            if ($catQuery) {
                $catQuery->data_seek(0);
                while($cat = $catQuery->fetch_assoc()) {
                    echo '<button class="btn btn-outline-primary btn-sm category-filter-btn" data-category="'.htmlspecialchars($cat['category_id']).'">'.htmlspecialchars($cat['name']).'</button>';
                }
            }
            ?>
        </div>

        <div id="stores-grid" class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            <?php
            $storeQuery = $db->query("
                SELECT s.*, c.name AS category_name, u.name AS manufacturer_name 
                FROM stores s
                JOIN categories c ON s.categoryID = c.category_id
                JOIN users u ON s.manufacturerID = u.id
                ORDER BY s.name ASC
            ");
            if ($storeQuery && $storeQuery->num_rows > 0) {
                while($store = $storeQuery->fetch_assoc()) {
                    echo '
                    <div class="col store-card-container" data-category="'.htmlspecialchars($store['categoryID']).'">
                        <div class="card shadow-lg h-100 border-0 rounded-4 overflow-hidden store-card">
                            <div class="image-container">
                                <img src="'.htmlspecialchars($store['image_url']).'" class="card-img-top" alt="'.htmlspecialchars($store['name']).'">
                            </div>
                            <div class="card-body p-4">
                                <h3 class="card-title fs-5 fw-semibold mb-1">'.htmlspecialchars($store['name']).'</h3>
                                <p class="card-text text-muted small mb-2">'.htmlspecialchars($store['description']).'</p>
                                <p class="mb-2"><strong>Manufacturer:</strong> '.htmlspecialchars($store['manufacturer_name']).'</p>
                                <p class="mb-3"><strong>Category:</strong> '.htmlspecialchars($store['category_name']).'</p>
                                <div class="mb-3">';
                    // Display tags
                    if (!empty($store['tags'])) {
                        $tags = explode(',', $store['tags']);
                        foreach($tags as $tag){
                            echo '<span class="product-tag">'.htmlspecialchars(trim($tag)).'</span>';
                        }
                    }
                    echo '</div>
                        <a href="store-detail.php?id='.$store['storeID'].'" class="btn '.htmlspecialchars($store['btnClass']).' text-white w-100 btn-sm fw-medium btn-hover-primary">View Store</a>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<div class="col-12 text-center py-5">
                        <h3 class="text-muted">No stores found</h3>
                        <p class="text-muted">Please check back later for our partner stores.</p>
                      </div>';
            }
            ?>
        </div>
    </main>
</div>

<footer class="bg-dark-bg-custom text-white mt-5">
    <div class="container-xl px-4 py-4 text-center">
        <p class="mb-0">&copy; 2025 PriMeri.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
function filterStoresByCategory(category) {
    const storeCards = document.querySelectorAll('.store-card-container');
    const filterButtons = document.querySelectorAll('.category-filter-btn');
    
    filterButtons.forEach(btn => {
        if (btn.dataset.category === category) {
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('filter-active');
        } else {
            btn.classList.add('btn-outline-primary');
            btn.classList.remove('filter-active');
        }
    });
    
    document.getElementById('category-filter').value = category;
    
    storeCards.forEach(card => {
        card.style.display = (category === 'all' || card.dataset.category === category) ? 'block' : 'none';
    });
}

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('category-filter').addEventListener('change', e => filterStoresByCategory(e.target.value));
    
    document.querySelectorAll('.category-filter-btn').forEach(btn => {
        btn.addEventListener('click', e => filterStoresByCategory(e.target.dataset.category));
    });

    document.getElementById('search-button').addEventListener('click', () => {
        const query = document.getElementById('search-input').value.toLowerCase();
        document.querySelectorAll('.store-card-container').forEach(card => {
            const name = card.querySelector('.card-title').textContent.toLowerCase();
            const description = card.querySelector('.card-text').textContent.toLowerCase();
            card.style.display = (name.includes(query) || description.includes(query)) ? 'block' : 'none';
        });
    });

    document.getElementById('search-input').addEventListener('keyup', (e) => {
        if (e.key === 'Enter') {
            document.getElementById('search-button').click();
        }
    });
});
</script>

</body>
</html>
