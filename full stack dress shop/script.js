// ============================================================
// script.js — DRIP ZONE 🔥 Frontend Logic
// Cart, Wishlist, Filtering, Search, Checkout
// ============================================================

'use strict';

/* ── State ── */
let cart     = loadCart();
let wishlist = loadWishlist();

const GST_RATE = 0.05; // 5% GST on apparel

/* ── DOM Refs ── */
const cartDrawer     = document.getElementById('cart-drawer');
const cartOverlay    = document.getElementById('cart-overlay');
const cartItemsList  = document.getElementById('cart-items-list');
const cartFooter     = document.getElementById('cart-footer');
const cartCountEl    = document.getElementById('cart-count');
const cartSubtotalEl = document.getElementById('cart-subtotal');
const cartGstEl      = document.getElementById('cart-gst');
const cartTotalEl    = document.getElementById('cart-total');
const searchInput    = document.getElementById('search-input');
const filterChips    = document.querySelectorAll('.filter-chip');
const productGrid    = document.getElementById('product-grid');
const resultsCountEl = document.getElementById('results-count');
const checkoutModal  = document.getElementById('checkout-modal');
const toastContainer = document.getElementById('toast-container');

/* ── Init ── */
document.addEventListener('DOMContentLoaded', () => {
  renderCart();
  updateCartCount();
  restoreWishlists();

  if (searchInput) searchInput.addEventListener('input', debounce(filterProducts, 200));

  filterChips.forEach(chip => {
    chip.addEventListener('click', () => {
      filterChips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      filterProducts();
    });
  });

  if (checkoutModal) {
    checkoutModal.addEventListener('click', e => {
      if (e.target === checkoutModal) closeCheckout();
    });
  }

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeCart(); closeCheckout(); }
  });

  // Smooth scroll for hero CTA
  document.querySelectorAll('a[href="#products"]').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      document.getElementById('products')?.scrollIntoView({ behavior: 'smooth' });
    });
  });
});

/* ═══════════════════════════════════════════════════
   CART OPERATIONS
   ═══════════════════════════════════════════════════ */

function addToCart(id, btnEl) {
  const card = document.querySelector(`.product-card[data-id="${id}"]`);
  if (!card) return;

  const item = {
    id:    parseInt(id),
    name:  card.dataset.name,
    price: parseFloat(card.dataset.price),
    image: card.dataset.image || '',
    stock: parseInt(card.dataset.stock),
    sizes: card.dataset.sizes || '',
    qty:   1,
  };

  if (cart[id]) {
    if (cart[id].qty >= cart[id].stock) {
      showToast(`Only ${cart[id].stock} units in stock`, 'error');
      return;
    }
    cart[id].qty += 1;
  } else {
    cart[id] = item;
  }

  saveCart();
  renderCart();
  updateCartCount(true);
  showToast(`"${item.name}" added to bag 🔥`, 'success');

  if (btnEl) {
    btnEl.classList.add('added');
    setTimeout(() => btnEl.classList.remove('added'), 700);
  }
}

function removeFromCart(id) {
  delete cart[id];
  saveCart();
  renderCart();
  updateCartCount();
}

function changeQty(id, delta) {
  if (!cart[id]) return;
  const newQty = cart[id].qty + delta;
  if (newQty <= 0) { removeFromCart(id); return; }
  if (newQty > cart[id].stock) {
    showToast(`Only ${cart[id].stock} in stock`, 'error');
    return;
  }
  cart[id].qty = newQty;
  saveCart();
  renderCart();
}

function clearCart() {
  cart = {};
  saveCart();
  renderCart();
  updateCartCount();
}

/* ── Render ── */
function renderCart() {
  const items = Object.values(cart);

  if (items.length === 0) {
    cartItemsList.innerHTML = `
      <div class="cart-empty">
        <div class="empty-icon">🛍️</div>
        <p style="font-family:var(--font-display);font-size:1.4rem;color:#333;letter-spacing:.06em;">BAG IS EMPTY</p>
        <p style="font-size:.78rem;color:#444;">Add some fire fits to get started.</p>
      </div>`;
    cartFooter.style.display = 'none';
    return;
  }

  cartFooter.style.display = 'block';

  cartItemsList.innerHTML = items.map(item => {
    const imgHTML = item.image
      ? `<img src="${escHtml(item.image)}" alt="${escHtml(item.name)}" />`
      : getCatEmoji(item.name);

    return `
      <div class="cart-item" role="listitem">
        <div class="cart-item-img">${imgHTML}</div>
        <div>
          <div class="cart-item-name">${escHtml(item.name)}</div>
          <div class="cart-item-price">₹${fmt(item.price)} each</div>
          <div class="cart-item-qty">
            <button class="qty-btn" onclick="changeQty(${item.id},-1)" aria-label="Decrease">−</button>
            <span class="qty-val">${item.qty}</span>
            <button class="qty-btn" onclick="changeQty(${item.id},+1)" aria-label="Increase">+</button>
            <span style="font-size:.72rem;color:#444;margin-left:.3rem;">= ₹${fmt(item.price * item.qty)}</span>
          </div>
        </div>
        <button class="cart-item-remove" onclick="removeFromCart(${item.id})" aria-label="Remove">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>`;
  }).join('');

  const subtotal = items.reduce((s, i) => s + i.price * i.qty, 0);
  const gst      = subtotal * GST_RATE;
  const total    = subtotal + gst;

  cartSubtotalEl.textContent = `₹${fmt(subtotal)}`;
  cartGstEl.textContent      = `₹${fmt(gst)}`;
  cartTotalEl.textContent    = `₹${fmt(total)}`;
}

function updateCartCount(bump = false) {
  const total = Object.values(cart).reduce((s, i) => s + i.qty, 0);
  cartCountEl.textContent = total;
  if (bump) {
    cartCountEl.classList.add('bump');
    setTimeout(() => cartCountEl.classList.remove('bump'), 300);
  }
}

/* ── Cart Toggle ── */
function openCart() {
  cartDrawer.classList.add('open');
  cartOverlay.classList.add('open');
  document.getElementById('cart-toggle-btn').setAttribute('aria-expanded', 'true');
  document.body.style.overflow = 'hidden';
}
function closeCart() {
  cartDrawer.classList.remove('open');
  cartOverlay.classList.remove('open');
  document.getElementById('cart-toggle-btn')?.setAttribute('aria-expanded', 'false');
  document.body.style.overflow = '';
}
document.getElementById('cart-toggle-btn')?.addEventListener('click', () => {
  cartDrawer.classList.contains('open') ? closeCart() : openCart();
});

/* ═══════════════════════════════════════════════════
   WISHLIST
   ═══════════════════════════════════════════════════ */

function toggleWishlist(btn, id) {
  id = parseInt(id);
  if (wishlist.has(id)) {
    wishlist.delete(id);
    btn.classList.remove('active');
  } else {
    wishlist.add(id);
    btn.classList.add('active');
    showToast('Added to wishlist ❤️', 'success');
  }
  saveWishlist();
}
function restoreWishlists() {
  wishlist.forEach(id => {
    const btn = document.querySelector(`.product-card[data-id="${id}"] .wishlist-btn`);
    if (btn) btn.classList.add('active');
  });
}

/* ═══════════════════════════════════════════════════
   SEARCH & FILTER
   ═══════════════════════════════════════════════════ */

function filterProducts() {
  const q        = (searchInput?.value || '').toLowerCase().trim();
  const activeChip = document.querySelector('.filter-chip.active');
  const category = activeChip?.dataset.category || 'All';
  const cards    = document.querySelectorAll('.product-card');
  let   visible  = 0;

  cards.forEach(card => {
    const name   = card.dataset.name.toLowerCase();
    const cat    = card.dataset.category;
    const matchC = category === 'All' || cat === category;
    const matchQ = !q || name.includes(q) || cat.toLowerCase().includes(q);

    if (matchC && matchQ) {
      card.style.display = '';
      card.style.animationDelay = `${visible * 0.04}s`;
      visible++;
    } else {
      card.style.display = 'none';
    }
  });

  if (resultsCountEl) resultsCountEl.textContent = visible;

  let noRes = productGrid?.querySelector('.no-results');
  if (visible === 0 && cards.length > 0) {
    if (!noRes) {
      noRes = document.createElement('div');
      noRes.className = 'no-results';
      noRes.innerHTML = `<div class="no-results-icon">🔍</div><h3>NO RESULTS</h3><p>Try a different search or category.</p>`;
      productGrid.appendChild(noRes);
    }
  } else if (noRes) {
    noRes.remove();
  }
}

/* ═══════════════════════════════════════════════════
   CHECKOUT
   ═══════════════════════════════════════════════════ */

function openCheckout() {
  const items = Object.values(cart);
  if (!items.length) { showToast('Your bag is empty!', 'error'); return; }

  const subtotal = items.reduce((s, i) => s + i.price * i.qty, 0);
  const gst      = subtotal * GST_RATE;
  const total    = subtotal + gst;

  document.getElementById('checkout-summary').innerHTML =
    items.map(i =>
      `<div style="display:flex;justify-content:space-between;padding:.25rem 0;border-bottom:1px solid #1a1a1a;">
         <span style="color:var(--off-white);">${escHtml(i.name)} × ${i.qty}</span>
         <span style="color:var(--neon-yellow);">₹${fmt(i.price * i.qty)}</span>
       </div>`
    ).join('') +
    `<div style="display:flex;justify-content:space-between;padding:.2rem 0;color:#444;font-size:.8rem;">
       <span>GST (5%)</span><span>₹${fmt(gst)}</span>
     </div>`;

  document.getElementById('checkout-total').innerHTML =
    `<div style="display:flex;justify-content:space-between;padding-top:.6rem;border-top:1px solid #1a1a1a;">
       <span>TOTAL</span><span>₹${fmt(total)}</span>
     </div>`;

  closeCart();
  checkoutModal.classList.add('open');
  document.body.style.overflow = 'hidden';
  document.getElementById('cust-name').focus();
}

function closeCheckout() {
  checkoutModal?.classList.remove('open');
  document.body.style.overflow = '';
}

async function placeOrder() {
  const nameEl   = document.getElementById('cust-name');
  const phoneEl  = document.getElementById('cust-phone');
  const emailEl  = document.getElementById('cust-email');
  const methodEl = document.getElementById('payment-method');
  const notesEl  = document.getElementById('cust-notes');
  const btn      = document.getElementById('place-order-btn');

  if (!nameEl.value.trim()) {
    nameEl.focus();
    showToast('Enter your name to continue!', 'error');
    return;
  }

  const items    = Object.values(cart);
  const subtotal = items.reduce((s, i) => s + i.price * i.qty, 0);
  const total    = subtotal + subtotal * GST_RATE;

  const payload = {
    customer_name:  nameEl.value.trim(),
    customer_phone: phoneEl.value.trim(),
    customer_email: emailEl.value.trim(),
    payment_method: methodEl.value,
    notes:          notesEl.value.trim(),
    total_amount:   total.toFixed(2),
    items: items.map(i => ({
      stock_id:   i.id,
      item_name:  i.name,
      unit_price: i.price.toFixed(2),
      quantity:   i.qty,
      line_total: (i.price * i.qty).toFixed(2),
    })),
  };

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> LOCKING IN…';

  try {
    const res  = await fetch('checkout.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload),
    });
    const data = await res.json();

    if (data.success && data.bill_id) {
      clearCart();
      closeCheckout();
      window.location.href = `checkout.php?bill_id=${data.bill_id}&view=1`;
    } else {
      showToast(data.error || 'Something went wrong. Try again!', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('Network error. Check your connection!', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = 'LOCK IN ORDER ★';
  }
}

/* ═══════════════════════════════════════════════════
   PERSISTENCE
   ═══════════════════════════════════════════════════ */
function saveCart()     { localStorage.setItem('dripzone_cart', JSON.stringify(cart)); }
function loadCart()     { try { return JSON.parse(localStorage.getItem('dripzone_cart') || '{}'); } catch { return {}; } }
function saveWishlist() { localStorage.setItem('dripzone_wishlist', JSON.stringify([...wishlist])); }
function loadWishlist() { try { return new Set(JSON.parse(localStorage.getItem('dripzone_wishlist') || '[]')); } catch { return new Set(); } }

/* ═══════════════════════════════════════════════════
   TOAST
   ═══════════════════════════════════════════════════ */
function showToast(msg, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<span class="toast-dot"></span>${escHtml(msg)}`;
  toastContainer?.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity .3s';
    setTimeout(() => toast.remove(), 320);
  }, 3000);
}

/* ═══════════════════════════════════════════════════
   UTILS
   ═══════════════════════════════════════════════════ */
function fmt(n) {
  return parseFloat(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}
function debounce(fn, d) {
  let t;
  return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), d); };
}
function getCatEmoji(name) {
  const n = name.toLowerCase();
  if (n.includes('tee') || n.includes('shirt') && !n.includes('shirt ')) return '👕';
  if (n.includes('hoodie')) return '🧥';
  if (n.includes('jean') || n.includes('denim')) return '👖';
  if (n.includes('cargo') || n.includes('pant')) return '🪖';
  if (n.includes('jacket') || n.includes('puffer') || n.includes('wind')) return '🧣';
  if (n.includes('jogger') || n.includes('track')) return '🩳';
  if (n.includes('cap') || n.includes('hat') || n.includes('bucket')) return '🧢';
  return '🔥';
}
