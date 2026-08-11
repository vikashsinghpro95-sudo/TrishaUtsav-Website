<?php
require_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<style>
  :root {
    --ink: #12090c;
    --crimson: #990024;
    --gold: #f59e0b;
    --cream: #fffdf7;
    --line: rgba(245, 158, 11, 0.20);
  }

  /* Base Typography & Background */
  .cart-page { background: #ffffff; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 80vh; }
  .cart-page .font-display,
  .cart-page h1, .cart-page h2, .cart-page h3 { font-family: 'Fraunces', serif; }
  
  .eyebrow {
    font-size: 11px; letter-spacing: 0.15em; text-transform: uppercase;
    font-weight: 800; color: var(--crimson);
  }

  /* ---- 12-Column Layout ---- */
  .cart-split {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    align-items: start;
  }
  .cart-items-wrapper { grid-column: span 12 / span 12; }
  .cart-summary-wrapper { grid-column: span 12 / span 12; }

  @media (min-width: 1024px) {
    .cart-split { gap: 4rem; }
    .cart-items-wrapper { grid-column: span 7 / span 7; }
    .cart-summary-wrapper { grid-column: span 5 / span 5; position: sticky; top: 120px; }
  }
  @media (min-width: 1280px) {
    .cart-items-wrapper { grid-column: span 8 / span 8; }
    .cart-summary-wrapper { grid-column: span 4 / span 4; }
  }

  /* ---- Cart Items ---- */
  .cart-items-list { display: flex; flex-direction: column; gap: 1.5rem; }
  .cart-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 1rem;
    padding: 1.25rem; display: flex; flex-direction: column; gap: 1.5rem;
    transition: all 0.2s ease;
  }
  .cart-card:hover { border-color: var(--ink); box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
  @media (min-width: 640px) {
    .cart-card { flex-direction: row; align-items: stretch; gap: 2rem; padding: 1.5rem; }
  }
  
  .cart-item-img {
    width: 100%; aspect-ratio: 1/1; border-radius: 0.5rem; overflow: hidden; background: #f9fafb;
  }
  @media (min-width: 640px) {
    .cart-item-img { width: 140px; height: 140px; flex-shrink: 0; }
  }
  .cart-item-img img { width: 100%; height: 100%; object-fit: cover; }

  /* Qty controls */
  .qty-controls {
    display: inline-flex; align-items: center; justify-content: space-between;
    border: 1px solid #d1d5db; border-radius: 0.25rem;
    background: #fff; height: 44px; width: 120px;
  }
  .qty-btn {
    width: 40px; height: 100%; background: transparent; color: #4b5563;
    font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: all 0.2s ease; border: none; outline: none;
  }
  .qty-btn:hover { background: #f3f4f6; color: var(--ink); }
  .qty-value {
    flex: 1; text-align: center; font-size: 14px; font-weight: 700; color: var(--ink); border: none; background: transparent; outline: none; pointer-events: none;
  }

  /* Remove Btn */
  .remove-btn {
    color: #9ca3af; font-size: 13px; font-weight: 600; background: none; border: none; padding: 4px 8px; cursor: pointer; transition: color 0.2s;
    text-decoration: underline; margin-left: -8px;
  }
  .remove-btn:hover { color: #dc2626; }

  /* ---- Order Summary ---- */
  .summary-card {
    background: #f9fafb; border-radius: 1rem; padding: 1.25rem; border: 1px solid #e5e7eb;
  }
  @media (min-width: 1024px) {
    .summary-card { padding: 2rem; }
  }
  .summary-row {
    display: flex; justify-content: space-between; font-size: 14px; font-weight: 600; color: #4b5563; margin-bottom: 1rem;
  }
  .summary-row.total {
    border-top: 1px solid #e5e7eb; padding-top: 1.5rem; margin-top: 0.5rem;
    font-size: 18px; font-weight: 800; color: var(--ink);
  }

  /* Promo Input */
  .promo-form {
    display: flex; gap: 8px; margin-bottom: 2rem;
  }
  .promo-input {
    flex: 1; border: 1px solid #d1d5db; border-radius: 0.25rem; padding: 0 12px;
    height: 48px; font-size: 13px; font-weight: 600; text-transform: uppercase; background: #fff;
  }
  .promo-input:focus { border-color: var(--ink); outline: none; }
  .btn-apply {
    height: 48px; background: var(--ink); color: #fff; font-size: 13px; font-weight: 700;
    border-radius: 0.25rem; border: none; cursor: pointer; padding: 0 1.5rem; text-transform: uppercase; letter-spacing: 0.05em;
  }

  /* Primary CTA */
  .btn-primary-massive {
    width: 100%; background: var(--crimson); color: #fff; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;
    border-radius: 0.25rem; padding: 1.25rem; font-size: 14px; min-height: 52px;
    display: flex; align-items: center; justify-content: center; gap: 0.75rem;
    cursor: pointer; transition: all 0.2s ease; border: none;
  }
  .btn-primary-massive:hover { background: #7a001c; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(153, 0, 36, 0.3); }

  /* Trust Badges */
  .trust-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 24px; margin-top: 1.5rem; }
  .trust-badge { display: flex; align-items: center; gap: 8px; color: #6b7280; font-size: 12px; font-weight: 600; }
  .trust-badge i { font-size: 16px; color: var(--ink); }

  /* Empty State */
  .empty-cart-container {
    text-align: center; padding: 4rem 1rem; border: 1px dashed #d1d5db; border-radius: 1rem; background: #f9fafb;
  }
  .empty-cart-icon {
    font-size: 4rem; color: #d1d5db; margin-bottom: 1.5rem; display: inline-block;
  }

  /* Sticky Mobile Bar */
  .sticky-cta-bar {
    position: fixed; left: 0; right: 0; bottom: 0; z-index: 60;
    background: #fff; border-top: 1px solid #e5e7eb;
    padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
    transition: transform 0.3s ease; box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
  }
  .sticky-cta-bar.hidden-bar { transform: translateY(120%); }
  @media (min-width: 1024px) { .mobile-only { display: none; } }
</style>

<div class="cart-page">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10 py-6 sm:py-10">
        <!-- Breadcrumb -->
        <nav class="mb-6 sm:mb-10 flex items-center justify-between" aria-label="Breadcrumb">
            <a href="<?php echo BASE_URL; ?>shop.php" class="eyebrow hover:text-gray-900 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> CONTINUE SHOPPING
            </a>
            <span class="text-sm font-bold text-gray-500 hidden sm:inline-block">Secure Checkout <i class="fas fa-lock ml-1 text-green-600"></i></span>
        </nav>

        <!-- Page Title -->
        <div class="mb-8 sm:mb-12">
            <h1 class="font-display text-4xl sm:text-5xl font-black text-[#12090c] tracking-tight mb-2">Shopping Bag</h1>
            <p class="text-gray-500 text-sm font-medium">Review your items before proceeding to checkout.</p>
        </div>

        <!-- Cart Container (rendered by JS) -->
        <div id="cart-container" aria-label="Shopping Cart">
            <div class="flex flex-col justify-center items-center py-32 space-y-3">
                <div class="loader-spinner-dark"></div>
                <span class="text-gray-500 text-sm font-medium">Loading your bag...</span>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Sticky Checkout Bar -->
<div id="cart-sticky-bar" class="sticky-cta-bar mobile-only hidden" role="complementary" aria-label="Cart Summary">
    <div class="flex items-center justify-between gap-4 max-w-[1400px] mx-auto">
        <div class="min-w-0 flex flex-col">
            <span class="text-[10px] text-gray-500 font-bold uppercase tracking-wider block">Estimated Total</span>
            <span id="cart-sticky-total" class="font-display text-2xl font-black text-[#12090c]">₹0</span>
        </div>
        <a id="cart-sticky-checkout-btn" href="<?php echo BASE_URL; ?>checkout.php" class="btn-primary-massive !w-auto !px-6 !py-2 !min-h-[44px]">
            CHECKOUT
        </a>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/cart.js?v=<?php echo time(); ?>"></script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
