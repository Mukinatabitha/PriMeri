// Store data - in a real application, this would come from a database
const storesData = {
    1: {
        name: "Urban Threads Co.",
        rating: 4.8,
        description: "Premium custom apparel manufacturer specializing in corporate uniforms and branded clothing with over 15 years of experience in the textile industry.",
        image: "../images/Urban Threads Co..jpg",
        category: "Apparel",
        minOrder: "50 units",
        leadTime: "2-4 weeks",
        shipping: "Worldwide",
        certifications: "ISO 9001, OEKO-TEX",
        products: [
            {
                id: 101,
                name: "Premium Cotton T-Shirts",
                description: "100% premium cotton t-shirts with custom printing options. Available in various colors and sizes.",
                price: 12.99,
                image: "../images/tshirt.jpg",
                minOrder: 50
            },
            {
                id: 102,
                name: "Corporate Polo Shirts",
                description: "Professional polo shirts with embroidered logos. Moisture-wicking fabric for comfort.",
                price: 18.50,
                image: "../images/polo.jpg",
                minOrder: 25
            },
            {
                id: 103,
                name: "Branded Hoodies",
                description: "Comfortable hoodies with screen printing options. Perfect for corporate events or employee apparel.",
                price: 24.99,
                image: "../images/hoodie.jpg",
                minOrder: 30
            },
            {
                id: 104,
                name: "Custom Jackets",
                description: "Weather-resistant jackets with custom embroidery. Ideal for outdoor corporate teams.",
                price: 35.75,
                image: "../images/jacket.jpg",
                minOrder: 20
            }
        ]
    },
    2: {
        name: "Gourmet Pantry",
        rating: 4.9,
        description: "Artisanal food producer creating custom-branded snacks, confections, and specialty foods with a focus on quality ingredients and sustainable sourcing.",
        image: "../images/Gourmet Pantry.jpg",
        category: "Food & Beverage",
        minOrder: "100 units",
        leadTime: "3-5 weeks",
        shipping: "Domestic only",
        certifications: "Organic, FDA Certified",
        products: [
            {
                id: 201,
                name: "Custom Chocolate Bars",
                description: "Premium chocolate bars with custom packaging and flavor options. Made with ethically sourced cocoa.",
                price: 3.50,
                image: "../images/chocolate.jpg",
                minOrder: 100
            },
            {
                id: 202,
                name: "Gourmet Snack Mix",
                description: "Custom blend of nuts, dried fruits, and seasonings. Perfect for corporate gifts or events.",
                price: 8.25,
                image: "../images/snack-mix.jpg",
                minOrder: 50
            },
            {
                id: 203,
                name: "Artisan Coffee Beans",
                description: "Premium coffee beans with custom roasting profiles and branded packaging.",
                price: 12.99,
                image: "../images/coffee.jpg",
                minOrder: 75
            },
            {
                id: 204,
                name: "Specialty Tea Collection",
                description: "Curated selection of premium teas with custom labeling and packaging options.",
                price: 9.75,
                image: "../images/tea.jpg",
                minOrder: 60
            }
        ]
    },
    3: {
        name: "Beverage Crafters",
        rating: 4.7,
        description: "Specialists in private-label beverages including water, juices, and functional drinks with state-of-the-art bottling facilities.",
        image: "../images/Beverage Crafters.jpg",
        category: "Beverages",
        minOrder: "200 units",
        leadTime: "4-6 weeks",
        shipping: "Worldwide",
        certifications: "FDA, HACCP",
        products: [
            {
                id: 301,
                name: "Custom Label Water",
                description: "Premium spring water with custom labeling. Available in various bottle sizes.",
                price: 1.25,
                image: "../images/water.jpg",
                minOrder: 200
            },
            {
                id: 302,
                name: "Vitamin-Infused Drinks",
                description: "Functional beverages with added vitamins and custom flavor profiles.",
                price: 2.50,
                image: "../images/vitamin-drink.jpg",
                minOrder: 150
            },
            {
                id: 303,
                name: "Cold-Pressed Juices",
                description: "Fresh cold-pressed juices with custom blends and labeling options.",
                price: 4.75,
                image: "../images/juice.jpg",
                minOrder: 100
            },
            {
                id: 304,
                name: "Energy Drinks",
                description: "Custom-formulated energy drinks with branded packaging.",
                price: 3.25,
                image: "../images/energy-drink.jpg",
                minOrder: 150
            }
        ]
    },
    4: {
        name: "Packaging Prodigy",
        rating: 4.8,
        description: "Custom packaging solutions for retail, e-commerce, and corporate branding needs with a focus on sustainability and innovative design.",
        image: "../images/Packaging Prodigy.jpg",
        category: "Packaging",
        minOrder: "500 units",
        leadTime: "2-3 weeks",
        shipping: "Worldwide",
        certifications: "FSC Certified, Recyclable",
        products: [
            {
                id: 401,
                name: "Custom Boxes",
                description: "Cardboard boxes with custom printing and dimensions. Eco-friendly materials available.",
                price: 1.75,
                image: "../images/boxes.jpg",
                minOrder: 500
            },
            {
                id: 402,
                name: "Branded Bags",
                description: "Custom printed paper or plastic bags for retail or promotional use.",
                price: 0.85,
                image: "../images/bags.jpg",
                minOrder: 1000
            },
            {
                id: 403,
                name: "Product Labels",
                description: "High-quality product labels with custom designs and various material options.",
                price: 0.25,
                image: "../images/labels.jpg",
                minOrder: 2000
            },
            {
                id: 404,
                name: "Display Packaging",
                description: "Custom display packaging for retail environments with attention-grabbing designs.",
                price: 3.50,
                image: "../images/display-packaging.jpg",
                minOrder: 300
            }
        ]
    }
};

// Cart functionality
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Get store ID from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const storeId = urlParams.get('id');
    
    if (storeId && storesData[storeId]) {
        loadStoreDetails(storeId);
        loadStoreProducts(storeId);
    } else {
        // Redirect to stores page if store not found
        window.location.href = 'stores.html';
    }
    
    updateCartDisplay();
});

// Load store details
function loadStoreDetails(storeId) {
    const store = storesData[storeId];
    
    document.getElementById('store-name').textContent = store.name;
    document.getElementById('store-rating').innerHTML = `★ ${store.rating}`;
    document.getElementById('store-description').textContent = store.description;
    document.getElementById('store-image').src = store.image;
    document.getElementById('store-image').alt = store.name;
    document.getElementById('store-category').textContent = store.category;
    document.getElementById('min-order').textContent = store.minOrder;
    document.getElementById('lead-time').textContent = store.leadTime;
    document.getElementById('shipping').textContent = store.shipping;
    document.getElementById('certifications').textContent = store.certifications;
}

// Load store products
function loadStoreProducts(storeId) {
    const store = storesData[storeId];
    const productsGrid = document.getElementById('products-grid');
    
    productsGrid.innerHTML = '';
    
    store.products.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'col-md-6';
        productCard.innerHTML = `
            <div class="card product-card h-100 border-0 rounded-4 overflow-hidden">
                <div class="product-image-container">
                    <img src="${product.image}" class="card-img-top" alt="${product.name}">
                </div>
                <div class="card-body p-4">
                    <h3 class="card-title fs-5 fw-semibold mb-2">${product.name}</h3>
                    <p class="card-text text-muted small mb-3">${product.description}</p>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold text-primary-custom fs-5">$${product.price.toFixed(2)}</span>
                        <small class="text-muted">Min: ${product.minOrder} units</small>
                    </div>
                    <div class="quantity-controls">
                        <button class="quantity-btn minus-btn" data-product-id="${product.id}">-</button>
                        <input type="number" class="quantity-input" id="quantity-${product.id}" value="0" min="0">
                        <button class="quantity-btn plus-btn" data-product-id="${product.id}">+</button>
                    </div>
                    <button class="btn bg-primary-custom text-white w-100 btn-sm fw-medium btn-hover-primary add-to-cart-btn" data-product-id="${product.id}">
                        Add to Cart
                    </button>
                </div>
            </div>
        `;
        productsGrid.appendChild(productCard);
    });
    
    // Add event listeners for quantity buttons
    document.querySelectorAll('.plus-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const input = document.getElementById(`quantity-${productId}`);
            input.value = parseInt(input.value) + 1;
        });
    });
    
    document.querySelectorAll('.minus-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const input = document.getElementById(`quantity-${productId}`);
            if (parseInt(input.value) > 0) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });
    
    // Add event listeners for add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const quantity = parseInt(document.getElementById(`quantity-${productId}`).value);
            
            if (quantity > 0) {
                addToCart(productId, quantity);
                document.getElementById(`quantity-${productId}`).value = 0;
            }
        });
    });
}

// Add item to cart
function addToCart(productId, quantity) {
    // Find the product in all stores
    let product = null;
    for (const storeId in storesData) {
        product = storesData[storeId].products.find(p => p.id == productId);
        if (product) break;
    }
    
    if (!product) return;
    
    // Check if product already in cart
    const existingItem = cart.find(item => item.id == productId);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            quantity: quantity,
            image: product.image
        });
    }
    
    // Save to localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Update cart display
    updateCartDisplay();
    
    // Show success message
    showToast(`${quantity} ${product.name} added to cart`);
}

// Remove item from cart
function removeFromCart(productId) {
    cart = cart.filter(item => item.id != productId);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartDisplay();
}

// Update cart display
function updateCartDisplay() {
    const cartItems = document.getElementById('cart-items');
    const cartCount = document.getElementById('cart-count');
    const subtotalElement = document.getElementById('subtotal');
    const shippingElement = document.getElementById('shipping-cost');
    const taxElement = document.getElementById('tax');
    const totalElement = document.getElementById('total');
    const checkoutButton = document.getElementById('checkout-button');
    
    // Update cart count
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="text-muted text-center">Your cart is empty</p>';
        subtotalElement.textContent = '$0.00';
        shippingElement.textContent = '$0.00';
        taxElement.textContent = '$0.00';
        totalElement.textContent = '$0.00';
        checkoutButton.disabled = true;
        return;
    }
    
    // Calculate totals
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const shipping = subtotal > 100 ? 0 : 15;
    const tax = subtotal * 0.08; // 8% tax
    
    subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
    shippingElement.textContent = `$${shipping.toFixed(2)}`;
    taxElement.textContent = `$${tax.toFixed(2)}`;
    totalElement.textContent = `$${(subtotal + shipping + tax).toFixed(2)}`;
    
    // Enable checkout button
    checkoutButton.disabled = false;
    
    // Display cart items
    cartItems.innerHTML = '';
    cart.forEach(item => {
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.innerHTML = `
            <div class="cart-item-details">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-price">$${item.price.toFixed(2)} each</div>
            </div>
            <div class="cart-item-quantity">
                <input type="number" class="form-control form-control-sm" value="${item.quantity}" min="1" data-product-id="${item.id}">
                <button class="remove-item" data-product-id="${item.id}">&times;</button>
            </div>
        `;
        cartItems.appendChild(cartItem);
    });
    
    // Add event listeners for quantity changes
    document.querySelectorAll('.cart-item-quantity input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.getAttribute('data-product-id');
            const newQuantity = parseInt(this.value);
            
            if (newQuantity > 0) {
                const item = cart.find(item => item.id == productId);
                if (item) {
                    item.quantity = newQuantity;
                    localStorage.setItem('cart', JSON.stringify(cart));
                    updateCartDisplay();
                }
            }
        });
    });
    
    // Add event listeners for remove buttons
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            removeFromCart(productId);
        });
    });
}

// Show toast notification
function showToast(message) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-primary-custom border-0 position-fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '1050';
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast from DOM after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        document.body.removeChild(toast);
    });
}

// Checkout button functionality
document.getElementById('checkout-button').addEventListener('click', function() {
    if (cart.length > 0) {
        // In a real application, this would redirect to a checkout page
        alert('Proceeding to checkout! In a real application, this would take you to a secure checkout page.');
        // window.location.href = 'checkout.html';
    }
});