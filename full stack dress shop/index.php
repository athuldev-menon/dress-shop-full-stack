<?php
// index.php — DRIP ZONE 🔥 Men's Wear Storefront
session_start();
require_once 'db.php';

$catStmt    = $pdo->query("SELECT DISTINCT category FROM Stock WHERE display_status=1 ORDER BY category ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

$stmt  = $pdo->query("SELECT id,item_name,description,category,image_path,price,stock_qty,sizes FROM Stock WHERE display_status=1 ORDER BY created_at DESC");
$items = $stmt->fetchAll();

// Emoji map per category
$catEmoji = [
  'Tees'        => '👕',
  'Shirts'      => '🔲',
  'Jeans'       => '👖',
  'Cargo'       => '🪖',
  'Hoodies'     => '🧥',
  'Jackets'     => '🧣',
  'Joggers'     => '🩳',
  'Accessories' => '🧢',
];
$catBg = [
  'Tees'        => 'bg-tees',
  'Shirts'      => 'bg-shirts',
  'Jeans'       => 'bg-jeans',
  'Cargo'       => 'bg-cargo',
  'Hoodies'     => 'bg-hoodies',
  'Jackets'     => 'bg-jackets',
  'Joggers'     => 'bg-joggers',
  'Accessories' => 'bg-accessories',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>DRIP ZONE 🔥 — Men's Streetwear & Fashion</title>
  <meta name="description" content="Next-level men's streetwear. Tees, baggy jeans, cargos, hoodies, jackets and more. No cap, all drip."/>
  <link rel="stylesheet" href="style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
</head>
<body>

<!-- ══ MARQUEE TOP ══ -->
<div class="marquee-strip" aria-hidden="true">
  <div class="marquee-track">
    <span>🔥 DRIP ZONE</span><span>★ FREE SHIPPING OVER ₹999</span>
    <span>🔥 NEW DROPS WEEKLY</span><span>★ CASH ON DELIVERY</span>
    <span>🔥 DRIP ZONE</span><span>★ NO CAP ALL DRIP</span>
    <span>🔥 STREETWEAR FOR THE BOLD</span><span>★ SIZES S TO XXL</span>
    <span>🔥 DRIP ZONE</span><span>★ FREE SHIPPING OVER ₹999</span>
    <span>🔥 NEW DROPS WEEKLY</span><span>★ CASH ON DELIVERY</span>
    <span>🔥 DRIP ZONE</span><span>★ NO CAP ALL DRIP</span>
    <span>🔥 STREETWEAR FOR THE BOLD</span><span>★ SIZES S TO XXL</span>
  </div>
</div>

<!-- ══ NAV ══ -->
<header>
  <nav class="nav" role="navigation" aria-label="Main navigation">
    <div class="nav-inner">
      <a href="index.php" class="nav-logo" id="site-logo">
        <span class="logo-fire" aria-hidden="true">🔥</span>DRIP ZONE
      </a>

      <div class="nav-search" role="search">
        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
        </svg>
        <input type="search" id="search-input" placeholder="search tees, jeans, cargo…" aria-label="Search products"/>
      </div>

      <div class="nav-actions">
        <a href="admin.php" class="btn btn-outline-w btn-sm" id="admin-link">⚙ ADMIN</a>
        <button class="cart-btn" id="cart-toggle-btn" aria-label="Open cart" aria-expanded="false">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          BAG
          <span class="cart-count" id="cart-count" aria-live="polite">0</span>
        </button>
      </div>
    </div>
  </nav>

  <!-- Category Filter -->
  <div class="filter-bar" role="navigation" aria-label="Categories">
    <div class="filter-inner">
      <button class="filter-chip active" data-category="All" id="filter-all">ALL</button>
      <?php foreach($categories as $cat): ?>
        <button class="filter-chip" data-category="<?= htmlspecialchars($cat) ?>" id="filter-<?= strtolower(htmlspecialchars($cat)) ?>">
          <?= ($catEmoji[$cat] ?? '🔥') . ' ' . strtoupper(htmlspecialchars($cat)) ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>
</header>

<!-- ══ HERO ══ -->
<section class="hero" aria-label="Hero banner">
  <!-- Blobs -->
  <div class="hero-blob hero-blob-1" aria-hidden="true"></div>
  <div class="hero-blob hero-blob-2" aria-hidden="true"></div>
  <div class="hero-blob hero-blob-3" aria-hidden="true"></div>

  <!-- Sticker decorations -->
  <div class="sticker sticker-1" aria-hidden="true">EST. 2024 ✦</div>
  <div class="sticker sticker-2" aria-hidden="true">NO CAP 🧢</div>
  <div class="sticker sticker-3" aria-hidden="true">STAY DRIPPED ✦</div>
  <div class="sticker sticker-4" aria-hidden="true">GEN-Z ONLY 🔥</div>

  <p class="hero-tag">— next-gen men's streetwear —</p>

  <h1 data-text="DRIP ZONE" aria-label="DRIP ZONE">
    <span class="line-color-1">DRIP</span>
    <span class="line-color-2">ZONE</span>
  </h1>

  <p class="hero-sub">Bold cuts. Wild prints. Zero rules.<br>Fresh drops every week — limited stock, unlimited drip.</p>

  <div class="hero-cta">
    <a href="#products" class="btn btn-pink btn-xl" id="shop-now-btn">SHOP NOW 🔥</a>
    <a href="#products" class="btn btn-outline-w btn-xl" id="new-drops-btn">NEW DROPS ★</a>
  </div>
</section>

<!-- ══ SECOND MARQUEE ══ -->
<div class="marquee-strip yellow" aria-hidden="true">
  <div class="marquee-track" style="animation-direction:reverse;animation-duration:22s;">
    <span>👕 OVERSIZED TEES</span><span>★</span>
    <span>👖 BAGGY JEANS</span><span>★</span>
    <span>🧥 HEAVY HOODIES</span><span>★</span>
    <span>🪖 CARGO PANTS</span><span>★</span>
    <span>🧣 STREETWEAR JACKETS</span><span>★</span>
    <span>🧢 FRESH ACCESSORIES</span><span>★</span>
    <span>👕 OVERSIZED TEES</span><span>★</span>
    <span>👖 BAGGY JEANS</span><span>★</span>
    <span>🧥 HEAVY HOODIES</span><span>★</span>
    <span>🪖 CARGO PANTS</span><span>★</span>
    <span>🧣 STREETWEAR JACKETS</span><span>★</span>
    <span>🧢 FRESH ACCESSORIES</span><span>★</span>
  </div>
</div>

<!-- ══ PRODUCT GRID ══ -->
<main class="store-main" id="products">
  <div class="section-label">
    THE FITS
    <span class="label-accent">LIVE NOW</span>
  </div>

  <p class="results-meta" id="results-meta" aria-live="polite">
    Showing <strong id="results-count"><?= count($items) ?></strong> pieces
  </p>

  <div class="product-grid" id="product-grid" role="list">
    <?php if(empty($items)): ?>
      <div class="no-results">
        <div class="no-results-icon">👕</div>
        <h3>NO DROPS YET</h3>
        <p>Add some fire fits from the admin panel.</p>
      </div>
    <?php else: ?>
      <?php foreach($items as $item):
        $emoji = $catEmoji[$item['category']] ?? '🔥';
        $bg    = $catBg[$item['category']]    ?? 'bg-default';
        $sizes = $item['sizes'] ? explode(',', $item['sizes']) : [];
      ?>
      <article class="product-card"
               role="listitem"
               data-id="<?= $item['id'] ?>"
               data-name="<?= htmlspecialchars($item['item_name']) ?>"
               data-price="<?= $item['price'] ?>"
               data-category="<?= htmlspecialchars($item['category']) ?>"
               data-image="<?= $item['image_path'] ? htmlspecialchars($item['image_path']) : '' ?>"
               data-stock="<?= $item['stock_qty'] ?>"
               data-sizes="<?= htmlspecialchars($item['sizes'] ?? '') ?>"
               aria-label="<?= htmlspecialchars($item['item_name']) ?>, ₹<?= number_format($item['price'],2) ?>">

        <!-- Image -->
        <div class="product-img-wrap">
          <?php if($item['image_path'] && file_exists($item['image_path'])): ?>
            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>" loading="lazy"/>
          <?php else: ?>
            <div class="product-img-placeholder <?= $bg ?>">
              <span class="placeholder-emoji"><?= $emoji ?></span>
              <span class="placeholder-label"><?= strtoupper(htmlspecialchars($item['category'])) ?></span>
            </div>
          <?php endif; ?>

          <!-- Category sticker -->
          <div class="cat-sticker"><?= strtoupper(htmlspecialchars($item['category'])) ?></div>

          <!-- Stock ribbon -->
          <div class="stock-ribbon">
            <?php if($item['stock_qty']===0): ?>
              <span class="badge badge-red">SOLD OUT</span>
            <?php elseif($item['stock_qty']<=5): ?>
              <span class="badge badge-pink">ALMOST GONE</span>
            <?php endif; ?>
          </div>

          <!-- Wishlist -->
          <button class="wishlist-btn" aria-label="Wishlist" onclick="toggleWishlist(this,<?= $item['id'] ?>)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/>
            </svg>
          </button>
        </div>

        <!-- Info -->
        <div class="product-info">
          <span class="product-category"><?= htmlspecialchars($item['category']) ?></span>
          <h2 class="product-name"><?= strtoupper(htmlspecialchars($item['item_name'])) ?></h2>
          <?php if($item['description']): ?>
            <p class="product-desc"><?= htmlspecialchars($item['description']) ?></p>
          <?php endif; ?>

          <?php if(!empty($sizes)): ?>
            <p class="product-sizes">
              <?php foreach($sizes as $s): ?>
                <span style="border:1px solid #333;padding:.1rem .3rem;font-size:.65rem;margin-right:.2rem;"><?= trim($s) ?></span>
              <?php endforeach; ?>
            </p>
          <?php endif; ?>

          <div class="product-footer">
            <div class="product-price">
              <span class="currency">₹</span><?= number_format($item['price'],2) ?>
            </div>
            <?php if($item['stock_qty']>0): ?>
              <button class="add-cart-btn"
                      id="add-cart-<?= $item['id'] ?>"
                      aria-label="Add to bag"
                      onclick="addToCart(<?= $item['id'] ?>,this)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
              </button>
            <?php else: ?>
              <button class="add-cart-btn" disabled style="opacity:.3;cursor:not-allowed;" aria-disabled="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            <?php endif; ?>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<!-- ══ THIRD MARQUEE ══ -->
<div class="marquee-strip blue" aria-hidden="true" style="background:var(--cyber-blue);">
  <div class="marquee-track" style="animation-duration:25s;">
    <span>💳 UPI ACCEPTED</span><span>★</span>
    <span>📦 FAST DELIVERY</span><span>★</span>
    <span>🔄 7-DAY RETURNS</span><span>★</span>
    <span>🔒 SECURE CHECKOUT</span><span>★</span>
    <span>💳 UPI ACCEPTED</span><span>★</span>
    <span>📦 FAST DELIVERY</span><span>★</span>
    <span>🔄 7-DAY RETURNS</span><span>★</span>
    <span>🔒 SECURE CHECKOUT</span><span>★</span>
  </div>
</div>

<!-- ══ CART DRAWER ══ -->
<div class="cart-overlay" id="cart-overlay" onclick="closeCart()"></div>
<aside class="cart-drawer" id="cart-drawer" role="complementary" aria-label="Shopping bag">
  <div class="cart-header">
    <h2>🔥 YOUR BAG</h2>
    <button class="cart-close-btn" onclick="closeCart()" aria-label="Close bag">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
  <div class="cart-items-list" id="cart-items-list" role="list" aria-live="polite"></div>
  <div class="cart-footer" id="cart-footer" style="display:none;">
    <div class="cart-totals">
      <div class="cart-total-row"><span>Subtotal</span><span id="cart-subtotal">₹0.00</span></div>
      <div class="cart-total-row"><span>GST (5%)</span><span id="cart-gst">₹0.00</span></div>
      <div class="cart-total-row grand"><span>TOTAL</span><span class="amount" id="cart-total">₹0.00</span></div>
    </div>
    <button class="btn btn-pink btn-block btn-lg" id="checkout-btn" onclick="openCheckout()">
      CHECKOUT NOW 🔥
    </button>
  </div>
</aside>

<!-- ══ CHECKOUT MODAL ══ -->
<div class="modal-overlay" id="checkout-modal" role="dialog" aria-modal="true" aria-label="Checkout">
  <div class="modal">
    <div class="modal-head">
      <h2>CHECK OUT 🔥</h2>
      <p>Fill your deets to lock in the order</p>
    </div>
    <div class="modal-body">
      <form id="checkout-form" novalidate>
        <div class="form-group">
          <label class="form-label" for="cust-name">Full Name *</label>
          <input type="text" id="cust-name" name="customer_name" class="form-control" placeholder="Your name" required/>
        </div>
        <div class="form-group">
          <label class="form-label" for="cust-phone">Phone Number</label>
          <input type="tel" id="cust-phone" name="customer_phone" class="form-control" placeholder="+91 98765 43210"/>
        </div>
        <div class="form-group">
          <label class="form-label" for="cust-email">Email</label>
          <input type="email" id="cust-email" name="customer_email" class="form-control" placeholder="you@email.com"/>
        </div>
        <div class="form-group">
          <label class="form-label" for="payment-method">Payment Method *</label>
          <select id="payment-method" name="payment_method" class="form-control">
            <option value="UPI">UPI (PhonePe / GPay / Paytm)</option>
            <option value="Cash">Cash on Delivery</option>
            <option value="Card">Debit / Credit Card</option>
            <option value="Net Banking">Net Banking</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="cust-notes">Size Notes / Special Requests</label>
          <textarea id="cust-notes" name="notes" class="form-control" placeholder="e.g. Size L for tee, XL for hoodie…"></textarea>
        </div>
        <div class="divider"></div>
        <p class="form-label">ORDER SUMMARY</p>
        <div id="checkout-summary" style="font-size:.82rem;color:#666;margin-bottom:.5rem;"></div>
        <div id="checkout-total" style="font-family:var(--font-display);font-size:1.5rem;letter-spacing:.06em;color:var(--neon-yellow);"></div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline-w" onclick="closeCheckout()">CANCEL</button>
      <button class="btn btn-pink btn-lg" id="place-order-btn" onclick="placeOrder()">LOCK IN ORDER ★</button>
    </div>
  </div>
</div>

<!-- ══ FOOTER ══ -->
<footer class="store-footer" role="contentinfo">
  <div class="footer-logo">🔥 DRIP ZONE</div>
  <p>No cap. All drip. Every time.</p>
  <p style="margin-top:.4rem;font-size:.72rem;">© <?= date('Y') ?> Drip Zone. All rights reserved.</p>
</footer>

<div class="toast-container" id="toast-container" aria-live="assertive"></div>

<script>
  const STOCK_DATA = <?= json_encode($items, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP) ?>;
  const GST_LABEL  = 'GST (5%)';
</script>
<script src="script.js"></script>
</body>
</html>
