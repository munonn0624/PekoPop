document.addEventListener('DOMContentLoaded', function () {
    // === Element references ===
    const variationOptions = document.querySelectorAll('.variation-option');
    const addCartBtn = document.getElementById('addcart-btn') || document.querySelector('.add-to-cart');
    const stockStatus = document.querySelector('.stock-status');
    const imgOptions = document.querySelectorAll('.product-img-option img');
    const mainImage = document.querySelector('.product-img-display img');
    const priceElement = document.querySelector('.product-price h4');
    const productName = document.querySelector('.product-name');
    const productImage = document.querySelector('.product-image img');
    const quantityWrapper = document.querySelector('.quantity-wrapper');

    if (!quantityWrapper || !priceElement) return; // safety check

    const minusBtn = quantityWrapper.querySelector('.quantity-decrease');
    const plusBtn = quantityWrapper.querySelector('.quantity-increase');
    const quantityDisplay = quantityWrapper.querySelector('.quantity-display');

    // === State ===
    let selectedStock = parseInt(quantityDisplay.getAttribute('data-stock')) || 1;
    let quantity = parseInt(quantityDisplay.value) || 1;
    let selectedVariation = null;

    // Keep original unit price constant
    const unitPrice =
        parseFloat(priceElement.getAttribute('data-unit-price')) ||
        parseFloat(priceElement.textContent.replace(/[^0-9.]/g, '')) ||
        0;

    // === Helpers ===
    function updateButtonVisuals() {
        const inactiveColor = '#f5f5f5';
        const activeColor = '#f0f0f0';

        minusBtn.disabled = !selectedVariation || quantity <= 1;
        plusBtn.disabled = !selectedVariation || quantity >= selectedStock;

        minusBtn.style.backgroundColor = minusBtn.disabled ? inactiveColor : activeColor;
        plusBtn.style.backgroundColor = plusBtn.disabled ? inactiveColor : activeColor;
        minusBtn.style.cursor = minusBtn.disabled ? 'not-allowed' : 'pointer';
        plusBtn.style.cursor = plusBtn.disabled ? 'not-allowed' : 'pointer';
    }

    function calculateAndUpdatePrice() {
        if (!selectedVariation) {
            // Show base price only
            priceElement.textContent = `RM ${unitPrice.toFixed(2)}`;
            return;
        }
        const total = unitPrice * quantity;
        priceElement.textContent = `RM ${total.toFixed(2)}`;
    }

    function refreshQuantityDisplay() {
        quantityDisplay.value = quantity;
        updateButtonVisuals();
        calculateAndUpdatePrice();
    }

    function resetVariationStyles() {
        variationOptions.forEach(opt => {
            opt.classList.remove('active', 'selected');
            opt.style.border = '1px solid #ccc';
            opt.style.boxShadow = 'none';
        });
    }

    function resetThumbnailStyles() {
        imgOptions.forEach(img => img.classList.remove('selected-thumb'));
    }

    function handleVariationSelection(option) {
        resetVariationStyles();
        option.classList.add('active', 'selected');
        option.style.border = '2px solid #333';
        option.style.boxShadow = '0 0 5px rgba(0,0,0,0.2)';

        selectedVariation = option.dataset.value;
        selectedStock = parseInt(option.dataset.stock) || 1;
        quantity = 1;
        refreshQuantityDisplay();

        if (stockStatus) {
            stockStatus.textContent = selectedStock <= 5 ? 'Low Stock' : '';
        }

        if (addCartBtn) addCartBtn.disabled = false;

        const variationImage = option.querySelector('img');
        if (variationImage && mainImage) {
            mainImage.src = variationImage.src;
        }

        resetThumbnailStyles();
    }

    function handleAddToCart() {
        if (!selectedVariation) {
            alert('Please select a product type');
            return;
        }

        const productId = new URLSearchParams(window.location.search).get('id');
        const totalPrice = unitPrice * quantity;

        const payload = {
            product_id: productId,
            quantity: quantity,
            set_type: selectedVariation,
            delivery_type: 'UnboxOnline_ClaimInstore',
            price: parseFloat(totalPrice),
            name: productName ? productName.textContent : '',
            image_url: productImage ? productImage.src : ''
        };

        fetch('/PekoPop/backend/add-to-cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    alert('Added to cart successfully!');
                } else {
                    alert(result.message || 'Failed to add to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add to cart');
            });
    }

    // === Event Listeners ===
    minusBtn.addEventListener('click', () => {
        if (!selectedVariation) return;
        if (quantity > 1) {
            quantity--;
            refreshQuantityDisplay();
        }
    });

    plusBtn.addEventListener('click', () => {
        if (!selectedVariation) return;
        if (quantity < selectedStock) {
            quantity++;
            refreshQuantityDisplay();
        } else {
            alert(`Sorry, only ${selectedStock} items available in stock!`);
        }
    });

    quantityDisplay.addEventListener('change', () => {
        if (!selectedVariation) {
            quantityDisplay.value = 1;
            return;
        }
        let newQty = parseInt(quantityDisplay.value);
        if (isNaN(newQty) || newQty < 1) newQty = 1;
        if (newQty > selectedStock) {
            alert(`Only ${selectedStock} items available in stock!`);
            newQty = selectedStock;
        }
        quantity = newQty;
        refreshQuantityDisplay();
    });

    variationOptions.forEach(option => {
        option.addEventListener('click', () => handleVariationSelection(option));
    });

    imgOptions.forEach(img => {
        img.addEventListener('click', () => {
            resetThumbnailStyles();
            img.classList.add('selected-thumb');
            if (mainImage) mainImage.src = img.src;

            resetVariationStyles();
            selectedVariation = null;
            if (addCartBtn) addCartBtn.disabled = true;
            refreshQuantityDisplay(); // re-disable buttons
        });
    });

    if (imgOptions.length > 0 && mainImage) {
        imgOptions[0].classList.add('selected-thumb');
        mainImage.src = imgOptions[0].src;
    }

    if (addCartBtn) {
        addCartBtn.disabled = true;
        addCartBtn.addEventListener('click', handleAddToCart);
    }

    // === Initial setup ===
    selectedVariation = null;
    refreshQuantityDisplay(); // disables + - until variation chosen
});

document.addEventListener('DOMContentLoaded', function () {
    const detailSections = document.querySelectorAll('.details-section');

    detailSections.forEach(section => {
        // Select both the title and the content div inside the section
        const title = section.querySelector('.product-detail-title, .shipping-detail-title');
        const content = section.querySelector('.product-detail-desc, .shipping-detail-desc');

        // Common toggle function
        function toggleSection() {
            const isActive = section.classList.contains('active');

            // Collapse all and reset carets
            detailSections.forEach(sec => {
                sec.classList.remove('active');
                toggleCaretIcon(sec, false);
            });

            // Expand clicked one if it wasn't active
            if (!isActive) {
                section.classList.add('active');
                toggleCaretIcon(section, true);
            }
        }

        title.addEventListener('click', toggleSection);
        if (content) {
            content.addEventListener('click', toggleSection);
        }
    });
});
