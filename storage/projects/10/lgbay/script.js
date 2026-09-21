document.addEventListener('DOMContentLoaded', () => {
  const cartKey = 'lgbay-cart-items';

  const getCart = () => {
    try {
      const storedCart = JSON.parse(localStorage.getItem(cartKey) || '[]');
      if (!Array.isArray(storedCart)) return [];

      return storedCart
        .filter((item) => item && typeof item.title === 'string')
        .map((item) => ({
          title: item.title.trim().slice(0, 120),
          price: Math.max(0, Number(item.price) || 0),
          quantity: Math.min(10, Math.max(1, Number(item.quantity) || 1)),
          image: typeof item.image === 'string' ? item.image : '',
        }));
    } catch (error) {
      return [];
    }
  };

  const saveCart = (items) => {
    localStorage.setItem(cartKey, JSON.stringify(items));
  };

  const formatCurrency = (value) => `MWK ${Number(value || 0).toLocaleString()}`;

  const escapeHtml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const updateCartCount = () => {
    const total = getCart().reduce((sum, item) => sum + Number(item.quantity || 1), 0);
    document.querySelectorAll('.cart-btn span').forEach((counter) => {
      counter.textContent = total;
    });
  };

  const updateCartSummary = () => {
    const summary = document.querySelector('.cart-summary');
    if (!summary) return;

    const cart = getCart();
    const itemCount = cart.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
    const subtotal = cart.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.quantity || 0), 0);
    const shipping = subtotal > 0 ? 2500 : 0;
    const total = subtotal + shipping;

    const itemCountNode = summary.querySelector('[data-cart-item-count]');
    const subtotalNode = summary.querySelector('[data-cart-subtotal]');
    const shippingNode = summary.querySelector('[data-cart-shipping]');
    const totalNode = summary.querySelector('[data-cart-total]');

    if (itemCountNode) itemCountNode.textContent = itemCount;
    if (subtotalNode) subtotalNode.textContent = formatCurrency(subtotal);
    if (shippingNode) shippingNode.textContent = formatCurrency(shipping);
    if (totalNode) totalNode.textContent = formatCurrency(total);
  };

  const buildSearchOverlay = () => {
    if (document.getElementById('search-overlay')) {
      return document.getElementById('search-overlay');
    }

    const overlay = document.createElement('div');
    overlay.id = 'search-overlay';
    overlay.className = 'search-overlay';
    overlay.innerHTML = `
      <div class="search-panel" role="dialog" aria-modal="true" aria-label="Search products">
        <div class="search-panel-header">
          <h3>Search products</h3>
          <button type="button" class="search-close" aria-label="Close search">✕</button>
        </div>
        <input type="text" id="product-search" placeholder="Search tees, hoodies, caps, accessories..." />
        <p class="search-results-note" data-search-status aria-live="polite">Type to search this catalogue.</p>
      </div>
    `;

    document.body.appendChild(overlay);

    const input = overlay.querySelector('#product-search');
    const closeButton = overlay.querySelector('.search-close');

    closeButton.addEventListener('click', () => {
      overlay.classList.remove('open');
      input.value = '';
      runProductSearch('');
    });

    overlay.addEventListener('click', (event) => {
      if (event.target === overlay) {
        overlay.classList.remove('open');
        input.value = '';
        runProductSearch('');
      }
    });

    input.addEventListener('input', (event) => {
      runProductSearch(event.target.value);
    });

    input.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        overlay.classList.remove('open');
        input.value = '';
        runProductSearch('');
      }
    });

    return overlay;
  };

  const runProductSearch = (query) => {
    const term = query.trim().toLowerCase();
    const productCards = document.querySelectorAll('.product-card, .product-card-page');
    let visibleCount = 0;

    productCards.forEach((card) => {
      const text = card.textContent.toLowerCase();
      const matches = !term || text.includes(term);
      card.style.display = matches ? '' : 'none';
      if (matches) visibleCount += 1;
    });

    const searchStatus = document.querySelector('[data-search-status]');
    if (searchStatus) {
      searchStatus.textContent = term
        ? `${visibleCount} product${visibleCount === 1 ? '' : 's'} found`
        : 'Type to search this catalogue.';
    }

    const pageTitle = document.querySelector('.page-top h1');
    if (pageTitle && term) {
      const resultsLabel = document.querySelector('.search-results-note');
      if (!resultsLabel) {
        const note = document.createElement('p');
        note.className = 'search-results-note';
        note.textContent = `${visibleCount} result${visibleCount === 1 ? '' : 's'} found`;
        pageTitle.parentElement.appendChild(note);
      } else {
        resultsLabel.textContent = `${visibleCount} result${visibleCount === 1 ? '' : 's'} found`;
      }
    }

    const resultsNote = document.querySelector('.search-results-note');
    if (resultsNote && !term) {
      resultsNote.remove();
    }
  };

  document.querySelectorAll('.search-toggle').forEach((button) => {
    button.addEventListener('click', () => {
      const overlay = buildSearchOverlay();
      overlay.classList.add('open');
      const input = overlay.querySelector('#product-search');
      window.setTimeout(() => input.focus(), 0);
    });
  });

  const checkoutForm = document.querySelector('[data-order-form]');
  if (checkoutForm) {
    const checkoutItems = document.querySelector('[data-checkout-items]');
    const checkoutCart = getCart();
    const subtotal = checkoutCart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const shipping = subtotal > 0 ? 2500 : 0;

    if (checkoutItems) {
      checkoutItems.innerHTML = '';
      checkoutCart.forEach((item) => {
        const itemRow = document.createElement('p');
        itemRow.className = 'checkout-item';
        itemRow.textContent = `${item.title} x${item.quantity} - ${formatCurrency(item.price * item.quantity)}`;
        checkoutItems.appendChild(itemRow);
      });
    }

    const checkoutSubtotal = document.querySelector('[data-checkout-subtotal]');
    const checkoutShipping = document.querySelector('[data-checkout-shipping]');
    const checkoutTotal = document.querySelector('[data-checkout-total]');
    if (checkoutSubtotal) checkoutSubtotal.textContent = formatCurrency(subtotal);
    if (checkoutShipping) checkoutShipping.textContent = formatCurrency(shipping);
    if (checkoutTotal) checkoutTotal.textContent = formatCurrency(subtotal + shipping);

    checkoutForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const status = checkoutForm.querySelector('[data-form-status]');
      if (!checkoutCart.length) {
        if (status) status.textContent = 'Your cart is empty. Add an item before checking out.';
        return;
      }

      const formData = new FormData(checkoutForm);
      const name = String(formData.get('name') || '').trim();
      const phone = String(formData.get('phone') || '').trim();
      const address = String(formData.get('address') || '').trim();
      const orderLines = checkoutCart.map((item) => `- ${item.title} x${item.quantity}: ${formatCurrency(item.price * item.quantity)}`);
      const message = [
        'Hello LGBAY, I would like to place an order.',
        '',
        ...orderLines,
        '',
        `Total: ${formatCurrency(subtotal + shipping)}`,
        `Name: ${name}`,
        `Phone: ${phone}`,
        `Address: ${address}`,
      ].join('\n');

      window.location.href = `https://wa.me/265988721157?text=${encodeURIComponent(message)}`;
    });
  }

  document.querySelectorAll('.add-cart').forEach((button) => {
    const savedText = button.textContent.trim();
    button.dataset.defaultText = savedText;

    button.addEventListener('click', () => {
      const card = button.closest('.product-card, .product-card-page');
      const titleElement = card ? card.querySelector('h3') : null;
      const priceElement = card ? card.querySelector('.product-meta strong') : null;
      const imageElement = card ? card.querySelector('img') : null;

      const title = titleElement ? titleElement.textContent.trim() : (button.dataset.name || 'LGBAY product');
      const priceText = priceElement ? priceElement.textContent.trim() : (button.dataset.price || '0');
      const price = Number.parseInt(priceText.replace(/[^0-9]/g, ''), 10) || 0;
      const image = imageElement ? imageElement.src : (button.dataset.image || '');

      const cart = getCart();
      const existingItem = cart.find((item) => item.title === title);

      if (existingItem) {
        existingItem.quantity += 1;
      } else {
        cart.push({
          title,
          price,
          quantity: 1,
          image,
        });
      }

      saveCart(cart);
      updateCartCount();
      updateCartSummary();

      const originalText = button.dataset.defaultText || 'Add to cart';
      button.textContent = 'Added ✓';
      button.classList.add('added');

      clearTimeout(button._cartTimer);
      button._cartTimer = setTimeout(() => {
        button.textContent = originalText;
        button.classList.remove('added');
      }, 1200);
    });
  });

  const cartList = document.querySelector('[data-cart-list]');
  if (cartList) {
    const renderCartItems = () => {
      const cart = getCart();
      cartList.innerHTML = '';

      if (!cart.length) {
        cartList.innerHTML = '<p class="empty-cart">Your cart is empty.</p>';
        updateCartSummary();
        return;
      }

      cart.forEach((item) => {
        const article = document.createElement('article');
        article.className = 'product-card-page cart-item';
        article.innerHTML = `
          <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.title)}" />
          <div class="product-info">
            <div class="rating">★★★★★ <span>4.9</span></div>
            <h3>${escapeHtml(item.title)}</h3>
            <div class="product-meta">
              <strong>${formatCurrency(item.price)}</strong>
              <span>${formatCurrency(item.price * item.quantity)}</span>
            </div>
            <div class="cart-item-controls">
              <label for="qty-${escapeHtml(item.title.replace(/\s+/g, '-').toLowerCase())}">Qty</label>
              <select id="qty-${escapeHtml(item.title.replace(/\s+/g, '-').toLowerCase())}" data-quantity-select aria-label="Set quantity for ${escapeHtml(item.title)}">
                ${[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map((qty) => `
                  <option value="${qty}" ${qty === Number(item.quantity) ? 'selected' : ''}>${qty}</option>
                `).join('')}
              </select>
            </div>
            <button class="add-cart cart-remove" type="button">Remove</button>
          </div>
        `;

        const quantitySelect = article.querySelector('[data-quantity-select]');
        quantitySelect.addEventListener('change', (event) => {
          const updatedCart = getCart().map((cartItem) => {
            if (cartItem.title === item.title) {
              return {
                ...cartItem,
                quantity: Number(event.target.value) || 1,
              };
            }
            return cartItem;
          });
          saveCart(updatedCart);
          updateCartCount();
          updateCartSummary();
          renderCartItems();
        });

        const removeButton = article.querySelector('.cart-remove');
        removeButton.addEventListener('click', () => {
          const updatedCart = getCart().filter((cartItem) => cartItem.title !== item.title);
          saveCart(updatedCart);
          updateCartCount();
          updateCartSummary();
          renderCartItems();
        });

        cartList.appendChild(article);
      });

      updateCartSummary();
    };

    renderCartItems();
  }

  document.querySelector('.checkout-btn')?.addEventListener('click', () => {
    if (getCart().length) {
      window.location.href = 'checkout.html';
      return;
    }

    const cartStatus = document.querySelector('[data-cart-status]');
    if (cartStatus) cartStatus.textContent = 'Your cart is empty. Add a product before checking out.';
  });

  updateCartCount();
  updateCartSummary();
});
