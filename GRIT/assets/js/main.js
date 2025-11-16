// Main JavaScript for GRIT E-commerce

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav ul');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            
            // Animate hamburger icon
            const spans = mobileMenuToggle.querySelectorAll('span');
            if (mainNav.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
    }
    
    // Close mobile menu when clicking on a link
    const navLinks = document.querySelectorAll('.main-nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            mainNav.classList.remove('active');
            const spans = mobileMenuToggle.querySelectorAll('span');
            spans[0].style.transform = 'none';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'none';
        });
    });
    
    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll('.btn-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            if (productId) {
                addToCart(productId);
            }
        });
    });
    
    // Update cart count
    updateCartCount();
});

// Add to cart function
function addToCart(productId) {
    // Check if we're in admin mode (URL contains /admin/)
    if (window.location.pathname.includes('/admin/')) {
        showNotification('Shopping is disabled in admin mode. Please visit the main site to shop.');
        return;
    }
    
    // Create form data
    const formData = new FormData();
    formData.append('add_to_cart', '1');
    formData.append('product_id', productId);
    formData.append('quantity', '1');
    
    // Send request to cart.php
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Update cart count in header
        updateCartCount();
        // Show notification
        showNotification('Product added to cart!');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding product to cart.');
    });
}

// Update cart count
function updateCartCount() {
    fetch('header.php')
    .then(response => response.text())
    .then(data => {
        // Parse the HTML to extract cart count
        const parser = new DOMParser();
        const doc = parser.parseFromString(data, 'text/html');
        const cartCountElement = doc.getElementById('cart-count');
        if (cartCountElement) {
            const cartCount = cartCountElement.textContent;
            const headerCartCount = document.getElementById('cart-count');
            if (headerCartCount) {
                headerCartCount.textContent = cartCount;
            }
        }
    })
    .catch(error => {
        console.error('Error updating cart count:', error);
    });
}

// Show notification
function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.backgroundColor = '#d4af37';
    notification.style.color = '#000';
    notification.style.padding = '15px 20px';
    notification.style.borderRadius = '3px';
    notification.style.zIndex = '9999';
    notification.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
    notification.style.fontWeight = 'bold';
    notification.id = 'notification';
    
    // Add to body
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    
    // Get all required fields
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = 'red';
            
            // Create error message
            const error = document.createElement('div');
            error.className = 'error-message';
            error.textContent = 'This field is required';
            error.style.color = 'red';
            error.style.fontSize = '0.8rem';
            error.style.marginTop = '5px';
            
            // Insert after field
            field.parentNode.insertBefore(error, field.nextSibling);
        } else {
            field.style.borderColor = '';
            
            // Remove existing error message
            const existingError = field.parentNode.querySelector('.error-message');
            if (existingError) {
                existingError.parentNode.removeChild(existingError);
            }
        }
    });
    
    return isValid;
}

// Quantity validation for cart
document.addEventListener('change', function(e) {
    if (e.target.matches('input[name^="quantities["]')) {
        const quantity = parseInt(e.target.value);
        const max = parseInt(e.target.max);
        const min = parseInt(e.target.min);
        
        if (quantity < min) {
            e.target.value = min;
            showNotification('Quantity cannot be less than ' + min);
        } else if (quantity > max) {
            e.target.value = max;
            showNotification('Quantity cannot exceed available stock of ' + max);
        }
    }
});