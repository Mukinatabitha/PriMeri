

    // Cart state - Load from localStorage on initialization
    let cart = [];

    // Load cart from localStorage when page loads
    function loadCartFromLocalStorage() {
        try {
            const savedCart = localStorage.getItem('cart');
            if (savedCart) {
                cart = JSON.parse(savedCart);
                console.log('Cart loaded from localStorage:', cart);
            } else {
                cart = [];
                console.log('No cart found in localStorage');
            }
        } catch (error) {
            console.error('Error loading cart from localStorage:', error);
            cart = [];
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Load cart from localStorage first
        loadCartFromLocalStorage();
        
        if (store) {
            renderStoreDetail(store, products);
            updateCartDisplay();
            updateCartCount();
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
        
        // Update cart count and display
        updateCartCount();
        updateCartDisplay();
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
        const product = getProductById(productId);
        const currentValue = parseInt(input.value) || 0;
        const newValue = currentValue + product.minOrder;
        input.value = newValue;
        updateProductQuantity(productId, newValue);
    }

    function decreaseQuantity(productId) {
        const input = document.getElementById(`quantity-${productId}`);
        const product = getProductById(productId);
        const currentValue = parseInt(input.value) || 0;
        const newValue = Math.max(0, currentValue - product.minOrder);
        input.value = newValue;
        updateProductQuantity(productId, newValue);
    }

    function updateProductQuantity(productId, quantity) {
        const product = getProductById(productId);
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

    // Get product by ID from current store products
    function getProductById(productId) {
        return products.find(p => p.id === productId);
    }

    // Cart functionality
    function addToCart(productId) {
        const input = document.getElementById(`quantity-${productId}`);
        const quantity = parseInt(input.value) || 0;
        const product = getProductById(productId);
        
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
                storeId: store.id,
                storeName: store.name
            });
        }
        
        updateCart();
        showCartNotification(`${product.name} ${existingItemIndex > -1 ? 'updated' : 'added'} to cart`);
    }

    function removeFromCart(productId) {
        console.log('Removing product from cart:', productId);
        console.log('Cart before removal:', cart);
        
        cart = cart.filter(item => item.id !== productId);
        
        console.log('Cart after removal:', cart);
        
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

    function updateCart() {
        localStorage.setItem('cart', JSON.stringify(cart));
        syncCartWithSession();
        updateCartDisplay();
        updateCartCount();
        attachCartEventListeners();
    }

    function updateCartCount() {
        const cartCount = document.getElementById('cart-count');
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        if (cartCount) {
            cartCount.textContent = totalItems;
            
            // Add animation
            if (cartCount.parentElement) {
                cartCount.parentElement.classList.add('cart-update');
                setTimeout(() => {
                    cartCount.parentElement.classList.remove('cart-update');
                }, 300);
            }
        }
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
        console.log('Attaching cart event listeners...');
        
        // Cart quantity buttons - PLUS
        document.querySelectorAll('.cart-quantity-btn.plus').forEach(btn => {
            btn.removeEventListener('click', handlePlusClick);
            btn.addEventListener('click', handlePlusClick);
        });
        
        // Cart quantity buttons - MINUS
        document.querySelectorAll('.cart-quantity-btn.minus').forEach(btn => {
            btn.removeEventListener('click', handleMinusClick);
            btn.addEventListener('click', handleMinusClick);
        });
        
        // Cart quantity inputs
        document.querySelectorAll('.cart-quantity-input').forEach(input => {
            input.removeEventListener('change', handleInputChange);
            input.addEventListener('change', handleInputChange);
        });
        
        // Remove buttons
        document.querySelectorAll('.cart-item-remove').forEach(btn => {
            btn.removeEventListener('click', handleRemoveClick);
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
        console.log('Remove button clicked for product:', productId);
        removeFromCart(productId);
    }

    function handleCheckoutClick(e) {
        if (cart.length === 0) {
            e.preventDefault();
            alert('Please add some products to your cart before proceeding to checkout.');
        } else {
            // Sync cart with session before redirecting
            e.preventDefault();
            syncCartWithSession().then(() => {
                // Now redirect to checkout
                window.location.href = 'checkout.php';
            }).catch(error => {
                console.error('Error syncing cart:', error);
                alert('Error preparing checkout. Please try again.');
            });
        }
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
        
        const subtotalElement = document.getElementById('subtotal');
        const taxElement = document.getElementById('tax');
        const totalElement = document.getElementById('total');
        
        if (subtotalElement) subtotalElement.textContent = `KSh ${subtotal.toLocaleString()}`;
        if (taxElement) taxElement.textContent = `KSh ${tax.toLocaleString()}`;
        if (totalElement) totalElement.textContent = `KSh ${total.toLocaleString()}`;
        
        // Update order summary items
        const summaryItems = document.getElementById('order-summary-items');
        if (summaryItems) {
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
    }

    function showCartNotification(message) {
        const notification = document.querySelector('.cart-notification');
        if (notification) {
            const strongElement = notification.querySelector('strong');
            if (strongElement) {
                strongElement.textContent = message;
            }
            notification.style.display = 'block';
            
            // Hide notification after 3 seconds
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }
    }

    // Sync cart with server session
    async function syncCartWithSession() {
        try {
            const response = await fetch('../php/cart-sync.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart: cart
                })
            });
            
            const data = await response.json();
            if (data.success) {
                console.log('Cart synced with session');
                return true;
            }
        } catch (error) {
            console.error('Error syncing cart:', error);
        }
        return false;
    }
