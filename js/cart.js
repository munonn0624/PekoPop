document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    const selectItems = document.querySelectorAll('.select-item');
    const checkoutButton =document.querySelector('.checkout-button');

    function disableCheckoutButton() {
        checkoutButton.disabled = true;
        checkoutButton.style.backgroundColor = '#acacacff';
        checkoutButton.style.color = 'white';
        checkoutButton.style.cursor = 'not-allowed';
        checkoutButton.classList.add('disabled');
    }

    function enableCheckoutButton() {
        checkoutButton.disabled = false;
        checkoutButton.style.color = '';
        checkoutButton.style.backgroundColor = ''; 
        checkoutButton.style.cursor = 'pointer';
        checkoutButton.classList.remove('disabled');
    }

    // Select All functionality
    selectAll.addEventListener('change', function () {
        selectItems.forEach(item => {
            item.checked = this.checked;
        });
        updateCartSummary();

    });

    // Individual select functionality
    selectItems.forEach(item => {
        item.addEventListener('change', function () {
            const allChecked = Array.from(selectItems).every(item => item.checked);
            selectAll.checked = allChecked;
            updateCartSummary();


        });
    });

    // Update quantity control functionality
    document.querySelectorAll('.quantity-wrapper').forEach(wrapper => {
        const decreaseBtn = wrapper.querySelector('.quantity-decrease');
        const increaseBtn = wrapper.querySelector('.quantity-increase');
        const quantityDisplay = wrapper.querySelector('.quantity-display');
        const maxStock = parseInt(increaseBtn.getAttribute('data-stock'));
        const itemContainer = wrapper.closest('.product-info'); // 👈 wrap each product in .cart-item
        const setType = itemContainer.querySelector('.product-options').getAttribute('data-set-type');

        decreaseBtn.addEventListener('click', async () => {
            let currentQty = parseInt(quantityDisplay.value);

            if (currentQty > 1) {
                await updateQuantity(decreaseBtn.dataset.productId, currentQty - 1,setType);
            }
        });

        increaseBtn.addEventListener('click', async () => {
            let currentQty = parseInt(quantityDisplay.value);
            if (currentQty >= maxStock) {
                alert(`Sorry, only ${maxStock} items available in stock!`);
                return;
            }
            await updateQuantity(increaseBtn.dataset.productId, currentQty + 1,setType);
        });
    });

    async function updateQuantity(productId, newQuantity, setType) {
        try {
            const response = await fetch('/PekoPop/backend/update-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: newQuantity,
                    set_type : setType
                    // Include additional identifiers if needed
                })
            });

            const result = await response.json();

            if (result.status === 'success') {
                const selector = `.quantity-wrapper button[data-product-id="${productId}"][data-set-type="${setType}"]`;
                const button = document.querySelector(selector);

                if (button) {
                    const wrapper = button.closest('.quantity-wrapper');
                    const quantityInput = wrapper.querySelector('.quantity-display');
                    quantityInput.value = newQuantity;

                    updateProductPriceDisplay(productId, newQuantity, setType);
                }

                updateCartSummary();

            } else {
                alert(result.message || 'Failed to update quantity');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update quantity');
        }
    }

    function updateProductPriceDisplay(productId, newQuantity, setType) {
        const selector = `.quantity-wrapper button[data-product-id="${productId}"][data-set-type="${setType}"]`;
        const button = document.querySelector(selector);

        if (button) {
            const wrapper = button.closest('.quantity-wrapper');
            const cartItem = wrapper.closest('.cart-item');
            const productPriceElem = cartItem.querySelector('.product-price');
            const unitPrice = parseFloat(productPriceElem.getAttribute('data-unit-price'));
            productPriceElem.textContent = `RM ${(unitPrice * newQuantity).toFixed(2)}`;
        }
    }



    // Add Remove Item functionality
    document.querySelectorAll('.remove-link').forEach(link => {
        link.addEventListener('click', async (e) => {
            e.preventDefault();

            if (!confirm('Are you sure you want to remove this item?')) return;

            const cartItem = link.closest('.cart-item');
            const cartItemId = link.getAttribute('data-cart-item-id');
            const productId = link.getAttribute('data-product-id');

            // Determine if logged in by presence of cartItemId
            const isLoggedIn = !!cartItemId;

            const payload = isLoggedIn
                ? { cart_item_id: parseInt(cartItemId) }
                : { product_id: productId };  // productId is string key, no parseInt!

            try {
                const response = await fetch('/PekoPop/backend/remove-cart-item.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    cartItem.remove();
                    updateCartSummary();
                    checkCartEmpty();

                } else {
                    alert('Failed to remove item: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to remove item: Network error');
            }
        });

    });

    function updateCartSummary() {
        const selectItems = document.querySelectorAll('.select-item');
        const summarySubtotal = document.querySelector('.summary-row:nth-child(1) .summary-value');
        const summaryShipping = document.querySelector('.summary-row:nth-child(3) .summary-value');

        const summaryTotal = document.querySelector('.summary-row.total .summary-value');
        const summaryLabel = document.querySelector('.summary-row.total .summary-label');
        const checkoutBtn = document.querySelector('.checkout-button');
        const deliverySelect = document.getElementById('delivery_type');

        let subtotal = 0;
        let totalQuantity = 0;
        let hasSelectedItems = false;

        // --- Calculate subtotal and total quantity ---
        selectItems.forEach(checkbox => {
            if (checkbox.checked) {
                const cartItem = checkbox.closest('.cart-item');
                if (!cartItem) return;

                hasSelectedItems = true;
                const quantityInput = cartItem.querySelector('.quantity-display');
                const quantity = parseInt(quantityInput?.value) || 0;

                const productPriceElem = cartItem.querySelector('.product-price');
                const unitPrice = parseFloat(productPriceElem?.getAttribute('data-unit-price')) || 0;

                subtotal += unitPrice * quantity;
                totalQuantity += quantity;
            }
        });

        // --- Shipping Calculation ---
        // --- Shipping Calculation ---
        let shipping = 0;
        const deliveryType = deliverySelect?.value;

        // Apply shipping cost even if no checkbox selected
        if (deliveryType === 'delivery') {
            shipping = 30.00;
            if (totalQuantity > 100) {
                const additionalTiers = Math.floor((totalQuantity - 1) / 100);
                shipping += additionalTiers * 30.00;
            }
        }


        const total = subtotal + shipping;

        // --- Update UI ---
        if (summarySubtotal) summarySubtotal.textContent = `RM ${subtotal.toFixed(2)}`;
        if (summaryShipping) summaryShipping.textContent = `RM ${shipping.toFixed(2)}`;
        if (summaryTotal) summaryTotal.textContent = `RM ${total.toFixed(2)}`;
        if (summaryLabel) summaryLabel.textContent = `Total (${totalQuantity})`;

        // --- Enable/Disable checkout button ---
        if (checkoutBtn) {
            if (hasSelectedItems) enableCheckoutButton();
            else disableCheckoutButton();
        }
    }

    // --- Event listeners ---
        const deliverySelect = document.getElementById('delivery_type');
        if (deliverySelect) {
            deliverySelect.addEventListener('change', updateCartSummary);
        }
    

    document.querySelectorAll('.quantity-display').forEach(input => {
        input.addEventListener('change', async function () {
            const newQuantity = Math.max(1, Math.min(parseInt(this.value) || 1, parseInt(this.getAttribute('data-stock'))));
            this.value = newQuantity;

            const productId = this.getAttribute('data-product-id');
            const itemContainer = this.closest('.product-info'); // 👈 wrap each product in .cart-item
            const setType = itemContainer.querySelector('.product-options').getAttribute('data-set-type');


            updateProductPriceDisplay(productId, newQuantity);
            await updateQuantity(productId, newQuantity, setType);
        });
    });




    // Initial total price calculation and checkoutbtn state
    updateCartSummary();

});


// Function to dynamically check and style .left-cart when cart is empty
function checkCartEmpty() {
    const leftCart = document.querySelector('.left-cart');
    const hasCartItems = leftCart.querySelector('.cart-item') !== null;

    if (!hasCartItems) {
        leftCart.style.display = 'flex';
        leftCart.style.justifyContent = 'center';
        leftCart.style.alignItems = 'center';
        leftCart.style.flexDirection = 'column';
        leftCart.style.lineHeight = '10px';
        leftCart.innerHTML = '<p style="font-size: 18px">Your cart is empty.</p>\
        <a href="/PekoPop/Pages/products.php" style="text-decoration: none; font-size: 14px;">Continue Shopping</a>';

    }
}

document.addEventListener('DOMContentLoaded', function () {
    checkCartEmpty(); //check on intial load
});

document.addEventListener('DOMContentLoaded', () => {
    const infoIcon = document.querySelector('.info-icon');
    const infoBox = infoIcon.nextElementSibling; // correctly targets the delivery-info

    infoIcon.addEventListener('click', (e) => {
        e.stopPropagation(); // prevent the document click from immediately hiding it
        infoBox.classList.toggle('show');
    });

    // hide when clicking outside
    document.addEventListener('click', (e) => {
        if (!infoIcon.contains(e.target) && !infoBox.contains(e.target)) {
            infoBox.classList.remove('show');
        }
    });
});
