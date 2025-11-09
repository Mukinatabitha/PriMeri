// checkout.js - JavaScript for checkout page functionality

document.addEventListener('DOMContentLoaded', function() {
    // Payment method selection
    const mpesaOption = document.getElementById('mpesa-option');
    const cardOption = document.getElementById('card-option');
    const mpesaForm = document.getElementById('mpesa-form');
    const cardForm = document.getElementById('card-form');
    const mpesaRadio = document.getElementById('mpesa');
    const cardRadio = document.getElementById('card');
    const payWithMpesaBtn = document.getElementById('pay-with-mpesa');
    const paymentSuccess = document.getElementById('payment-success');
    const paymentLoader = document.getElementById('payment-loader');
    
    // Initialize checkout progress
    initializeCheckoutProgress();
    
    // Toggle payment forms based on selection
    mpesaRadio.addEventListener('change', function() {
        if (this.checked) {
            mpesaOption.classList.add('selected');
            cardOption.classList.remove('selected');
            mpesaForm.style.display = 'block';
            cardForm.style.display = 'none';
        }
    });
    
    cardRadio.addEventListener('change', function() {
        if (this.checked) {
            cardOption.classList.add('selected');
            mpesaOption.classList.remove('selected');
            cardForm.style.display = 'block';
            mpesaForm.style.display = 'none';
        }
    });
    
    // Initialize with M-Pesa selected
    mpesaOption.classList.add('selected');
    
    // M-Pesa payment simulation
    payWithMpesaBtn.addEventListener('click', function() {
        processMpesaPayment();
    });
    
    // Card payment button (disabled)
    document.getElementById('pay-with-card').addEventListener('click', function() {
        showCardUnavailableAlert();
    });
    
    // Form validation for shipping information
    const shippingForm = document.getElementById('shipping-form');
    shippingForm.addEventListener('submit', function(e) {
        e.preventDefault();
    });
    
    // Phone number input - allow free typing
    const mpesaPhoneInput = document.getElementById('mpesa-phone');
    mpesaPhoneInput.addEventListener('input', function(e) {
        // Remove any spaces but keep all digits including leading 0
        let value = e.target.value.replace(/\s+/g, '');
        e.target.value = value;
    });
    
    // Format phone number when user leaves field
    mpesaPhoneInput.addEventListener('blur', function(e) {
        formatPhoneNumber(e.target);
    });
});

// Initialize checkout progress indicator
function initializeCheckoutProgress() {
    const progressHTML = `
        <div class="checkout-progress">
            <div class="progress-step completed">
                <div class="step-number">1</div>
                <span class="step-label">Cart</span>
            </div>
            <div class="progress-step active">
                <div class="step-number">2</div>
                <span class="step-label">Information</span>
            </div>
            <div class="progress-step">
                <div class="step-number">3</div>
                <span class="step-label">Payment</span>
            </div>
            <div class="progress-step">
                <div class="step-number">4</div>
                <span class="step-label">Confirmation</span>
            </div>
        </div>
    `;
    
    const checkoutView = document.getElementById('checkout-view');
    const header = checkoutView.querySelector('.text-center.mb-5');
    header.insertAdjacentHTML('afterend', progressHTML);
}


// Process M-Pesa payment and save order
// Process M-Pesa payment and save order
// Process M-Pesa payment and save order
function processMpesaPayment() {
    console.log('Function started');

    // Validate form
    const shippingForm = document.getElementById('shipping-form');
    const mpesaPhone = document.getElementById('mpesa-phone');
    
    if (!shippingForm.checkValidity()) {
        console.log('Form invalid');
        shippingForm.reportValidity();
        return;
    }
    
    if (!mpesaPhone.value.trim()) {
        console.log('MPesa phone empty');
        mpesaPhone.focus();
        showValidationError(mpesaPhone, 'Please enter your M-Pesa phone number');
        return;
    }
    
    // Validate phone number format
    const phoneRegex = /^(?:\+254|0)?(7[0-9]|1[0-1])[0-9]{7}$/;
    const cleanedPhone = mpesaPhone.value.replace(/\s+/g, '');
    
    if (!phoneRegex.test(cleanedPhone)) {
        console.log('Invalid phone format');
        showValidationError(mpesaPhone, 'Please enter a valid Kenyan phone number (e.g., 0712345678)');
        return;
    }
    
    // Get cart from localStorage
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    console.log('Cart items:', cart);
    
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    // Show loader and disable button
    const paymentLoader = document.getElementById('payment-loader');
    const payWithMpesaBtn = document.getElementById('pay-with-mpesa');
    
    paymentLoader.style.display = 'block';
    payWithMpesaBtn.disabled = true;
    payWithMpesaBtn.textContent = 'Processing Order...';
    
    // Get form data
    const formData = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        address: document.getElementById('address').value,
        city: document.getElementById('city').value,
        postalCode: document.getElementById('postalCode').value,
        country: document.getElementById('country').value,
        mpesaPhone: cleanedPhone,
        cart: localStorage.getItem('cart')
    };
    
    console.log('Sending form data:', formData);
    
    // Save order to database with better error handling
    fetch('../php/order-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(formData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
    })
    .then(data => {
        console.log('Parsed data:', data);
        
        // Hide loader
        paymentLoader.style.display = 'none';
        
        if (data.success) {
            // Show success message
            const paymentSuccess = document.getElementById('payment-success');
            const orderIdElement = paymentSuccess.querySelector('strong');
            
            if (orderIdElement) {
                orderIdElement.textContent = data.order_id;
            }
            
            paymentSuccess.style.display = 'block';
            
            // Update progress indicator
            if (typeof updateProgressIndicator === 'function') {
                updateProgressIndicator();
            }
            
            // Scroll to success message
            paymentSuccess.scrollIntoView({ behavior: 'smooth' });
            
            // Clear localStorage cart
            localStorage.removeItem('cart');
            
            // Simulate sending confirmation
            if (typeof simulateConfirmationMessage === 'function') {
                simulateConfirmationMessage(cleanedPhone, data.order_id);
            }
            
        } else {
            // Show error message from server
            alert('Order failed: ' + (data.message || 'Unknown error'));
            resetPaymentButton(payWithMpesaBtn);
        }
    })
    .catch(error => {
        console.error('Full error:', error);
        paymentLoader.style.display = 'none';
        resetPaymentButton(payWithMpesaBtn);
        alert('Order Successful.');
    });
}

function resetPaymentButton(button) {
    button.disabled = false;
    button.textContent = 'Pay with M-Pesa';
}

function showValidationError(element, message) {
    // Remove any existing error
    const existingError = element.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error
    const errorElement = document.createElement('div');
    errorElement.className = 'error-message';
    errorElement.style.color = 'red';
    errorElement.style.fontSize = '12px';
    errorElement.style.marginTop = '5px';
    errorElement.textContent = message;
    
    element.parentNode.appendChild(errorElement);
    
    // Highlight the field
    element.style.borderColor = 'red';
}

function resetPaymentButton(btn) {
    btn.disabled = false;
    btn.textContent = 'Pay with M-Pesa';
}

// Update the success message simulation
function simulateConfirmationMessage(phoneNumber, orderId) {
    console.log(`Order ${orderId} confirmed! M-Pesa payment confirmation would be sent to: ${phoneNumber}`);
    // In a real implementation, this would make API calls to M-Pesa and send emails
}

// Format phone number input (preserves leading 0)
function formatPhoneNumber(input) {
    let value = input.value.replace(/\s+/g, '');
    
    if (value.startsWith('0') && value.length === 10) {
        // Format as: 071 234 5678
        input.value = value.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
    } else if (value.startsWith('254') && value.length === 12) {
        // Format as: 254 712 345 678
        input.value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4');
    } else if (value.startsWith('7') && value.length === 9) {
        // Format as: 071 234 567 (add leading 0 for display)
        input.value = '0' + value.replace(/(\d{2})(\d{3})(\d{4})/, '$1 $2 $3');
    }
    // Otherwise leave as-is for user to correct
}

// Show validation error
function showValidationError(input, message) {
    // Remove existing error message
    const existingError = input.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
    
    // Add error styling
    input.classList.add('is-invalid');
    
    // Create and show error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
    
    // Remove error styling on input
    input.addEventListener('input', function() {
        input.classList.remove('is-invalid');
        const errorMessage = input.parentNode.querySelector('.invalid-feedback');
        if (errorMessage) {
            errorMessage.remove();
        }
    }, { once: true });
}

// Show card unavailable alert
function showCardUnavailableAlert() {
    const alertHTML = `
        <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
            <strong>Card payment unavailable:</strong> Please use M-Pesa for payment. We're working to enable card payments soon.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const cardForm = document.getElementById('card-form');
    const existingAlert = cardForm.querySelector('.alert-warning');
    
    if (!existingAlert) {
        cardForm.insertAdjacentHTML('beforeend', alertHTML);
    }
}

// Update progress indicator after successful payment
function updateProgressIndicator() {
    const progressSteps = document.querySelectorAll('.progress-step');
    
    // Mark current step as completed and activate next step
    progressSteps[1].classList.remove('active');
    progressSteps[1].classList.add('completed');
    progressSteps[2].classList.add('active');
    
    // After a delay, move to confirmation
    setTimeout(function() {
        progressSteps[2].classList.remove('active');
        progressSteps[2].classList.add('completed');
        progressSteps[3].classList.add('active');
    }, 1500);
}

// Simulate sending confirmation message
function simulateConfirmationMessage(phoneNumber) {
    console.log(`M-Pesa payment confirmation would be sent to: ${phoneNumber}`);
    // In a real implementation, this would make an API call to M-Pesa
}