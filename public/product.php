<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../models/Product.php';

$ogProduct = null;
$productSlugOrId = $_GET['slug'] ?? $_GET['id'] ?? null;
if ($productSlugOrId) {
    try {
        $pModel = new Product();
        if (is_numeric($productSlugOrId)) {
            $ogProduct = $pModel->find((int)$productSlugOrId);
        } else {
            $ogProduct = $pModel->findBySlug(trim($productSlugOrId));
        }
    } catch (Throwable $e) {}
}

if ($ogProduct) {
    $pageTitle = htmlspecialchars($ogProduct['name']) . " | Trisha Utsav";
    $ogTitle = htmlspecialchars($ogProduct['name']);
    $ogDesc = htmlspecialchars(substr(trim(strip_tags($ogProduct['short_description'] ?? $ogProduct['description'] ?? '')), 0, 200));
    
    $slugVal = $ogProduct['slug'] ?? $ogProduct['id'];
    $ogUrl = 'https://trishautsav.in/product?slug=' . urlencode($slugVal);
    
    $rawImg = $ogProduct['primary_image'] ?? (!empty($ogProduct['images']) ? $ogProduct['images'][0]['image_url'] : null);
    if ($rawImg) {
        $ogImage = (strpos($rawImg, 'http') === 0) ? $rawImg : ('https://trishautsav.in/' . ltrim($rawImg, '/'));
    } else {
        $ogImage = 'https://trishautsav.in/favicon.png';
    }
    $ogPrice = $ogProduct['sale_price'] ?? $ogProduct['price'] ?? 0;
}

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
  .pdp-page { background: #ffffff; font-family: 'Plus Jakarta Sans', sans-serif; }
  .pdp-page .font-display,
  .pdp-page h1, .pdp-page h2, .pdp-page h3 { font-family: 'Fraunces', serif; }
  
  .eyebrow {
    font-size: 11px; letter-spacing: 0.15em; text-transform: uppercase;
    font-weight: 800; color: var(--crimson);
  }

  /* ---- 12-Column Layout ---- */
  .pdp-split {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    align-items: start;
  }
  .pdp-gallery-wrapper { grid-column: span 12 / span 12; }
  .pdp-details-wrapper { grid-column: span 12 / span 12; }

  @media (min-width: 1024px) {
    .pdp-split { gap: 4rem; }
    .pdp-gallery-wrapper { grid-column: span 7 / span 7; position: sticky; top: 120px; }
    .pdp-details-wrapper { grid-column: span 5 / span 5; }
    /* Hide elements marked as mobile-only on desktop */
    .mobile-only { display: none !important; }
  }

  /* ---- Gallery (Left) ---- */
  .pdp-gallery {
    border-radius: 1rem;
    overflow: hidden;
    position: relative;
  }
  @media (max-width: 1023px) {
    /* Edge-to-edge on mobile */
    .pdp-gallery-wrapper { margin-left: -1rem; margin-right: -1rem; }
    .pdp-gallery { border-radius: 0; }
  }

  .pdp-main-swipe {
    display: flex; overflow-x: auto; scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch; scrollbar-width: none;
    background: #f9fafb;
  }
  .pdp-main-swipe::-webkit-scrollbar { display: none; }
  .pdp-main-slide {
    flex: 0 0 100%; scroll-snap-align: center;
    aspect-ratio: 4 / 5; overflow: hidden;
  }
  @media (min-width: 1024px) {
    .pdp-main-slide { aspect-ratio: 1 / 1.15; border-radius: 1rem; }
  }
  .pdp-main-slide img {
    width: 100%; height: 100%; object-fit: cover;
    user-select: none; -webkit-user-drag: none;
  }

  /* Gallery dots (mobile) */
  .pdp-dots { display: flex; justify-content: center; gap: 8px; padding: 16px 0; }
  .pdp-dot {
    width: 6px; height: 6px; border-radius: 50%; background: #e5e7eb;
    border: 0; padding: 0; cursor: pointer; transition: all 0.3s ease;
  }
  .pdp-dot.active { background: var(--crimson); transform: scale(1.5); }

  /* Thumbs row (desktop) */
  .pdp-thumbs {
    display: flex; gap: 12px; margin-top: 16px; overflow-x: auto;
    scrollbar-width: none; padding-bottom: 8px;
  }
  .pdp-thumbs::-webkit-scrollbar { display: none; }
  .pdp-thumb {
    flex: 0 0 80px; width: 80px; height: 80px; border-radius: 0.5rem;
    overflow: hidden; border: 2px solid transparent; cursor: pointer;
    transition: all 0.2s ease; background: #f9fafb; opacity: 0.6;
  }
  .pdp-thumb.active, .pdp-thumb:hover { border-color: var(--crimson); opacity: 1; }
  .pdp-thumb img { width: 100%; height: 100%; object-fit: cover; }

  /* ---- Buy Panel (Right) ---- */
  .pdp-buy { padding: 0; background: transparent; border: none; box-shadow: none; }

  /* Variant pills */
  .pdp-page .variant-pill {
    border-radius: 0.25rem; border-width: 1px;
    font-size: 13px; font-weight: 600; padding: 10px 20px; min-height: 48px;
    border-color: #d1d5db; background: #fff; color: #4b5563;
    transition: all 0.2s ease; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
  }
  .pdp-page .variant-pill:hover { border-color: var(--ink); color: var(--ink); }
  .pdp-page .variant-pill.selected {
    background: var(--ink); border-color: var(--ink); color: #fff; font-weight: 700;
  }

  /* Qty controls */
  .pdp-page .qty-controls {
    display: inline-flex; align-items: center;
    border: 1px solid #d1d5db; border-radius: 0.25rem;
    background: #fff; height: 48px;
  }
  .pdp-page .qty-btn {
    width: 40px; height: 100%; background: transparent; color: #4b5563;
    font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: all 0.2s ease; border: none;
  }
  .pdp-page .qty-btn:hover { background: #f3f4f6; color: var(--ink); }
  .pdp-page .qty-value {
    width: 48px; text-align: center; font-size: 14px; font-weight: 700; color: var(--ink);
  }

  /* Primary CTAs */
  .btn-primary-massive {
    width: 100%; background: var(--crimson); color: #fff; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;
    border-radius: 0.25rem; padding: 1rem 1.25rem; font-size: 13px; min-height: 48px;
    display: flex; align-items: center; justify-content: center; gap: 0.75rem;
    cursor: pointer; transition: all 0.2s ease; border: none;
  }
  .btn-primary-massive:hover { background: #7a001c; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(153, 0, 36, 0.3); }
  .btn-primary-massive:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

  .btn-secondary-massive {
    width: 100%; background: #fff; color: var(--ink); font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;
    border: 1px solid var(--ink); border-radius: 0.25rem; padding: 1rem 1.25rem; font-size: 13px; min-height: 48px;
    display: flex; align-items: center; justify-content: center; gap: 0.75rem;
    cursor: pointer; transition: all 0.2s ease;
  }
  .btn-secondary-massive:hover { background: #f9fafb; transform: translateY(-1px); }
  .btn-secondary-massive:disabled { opacity: 0.5; cursor: not-allowed; border-color: #d1d5db; color: #9ca3af; }

  /* Accordion (Clean) */
  .pdp-accordions { border-top: 1px solid #e5e7eb; margin-top: 3rem; }
  .pdp-accordion { border-bottom: 1px solid #e5e7eb; }
  .pdp-accordion-trigger {
    display: flex; align-items: center; justify-content: space-between;
    width: 100%; padding: 20px 0; background: none; border: none; cursor: pointer;
    font-weight: 700; font-size: 14px; color: var(--ink); letter-spacing: 0.02em; text-transform: uppercase;
  }
  .pdp-accordion-icon { font-size: 12px; color: var(--ink); transition: transform 0.3s; }
  .pdp-accordion.open .pdp-accordion-icon { transform: rotate(180deg); }
  .pdp-accordion-body { max-height: 0; overflow: hidden; transition: max-height 0.4s cubic-bezier(0.4,0,0.2,1); }
  .pdp-accordion.open .pdp-accordion-body { max-height: 800px; }
  .pdp-accordion-content { padding-bottom: 24px; color: #4b5563; font-size: 14px; line-height: 1.6; }

  /* Trust Badges */
  .trust-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 2rem; }
  @media (min-width: 640px) { .trust-row { grid-template-columns: repeat(4, 1fr); } }
  .trust-badge { display: flex; flex-direction: column; align-items: flex-start; gap: 8px; padding: 12px 0; }
  .trust-icon { font-size: 20px; color: var(--ink); }
  .trust-title { font-size: 12px; font-weight: 700; color: var(--ink); }
  .trust-desc { font-size: 11px; color: #6b7280; line-height: 1.4; }

  /* Sticky Mobile Bar */
  .sticky-cta-bar {
    position: fixed; left: 0; right: 0; bottom: 0; z-index: 60;
    background: #fff; border-top: 1px solid #e5e7eb;
    padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
    transition: transform 0.3s ease; box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
  }
  .sticky-cta-bar.hidden-bar { transform: translateY(120%); }

  /* Related carousel */
  .related-carousel {
    display: grid; grid-auto-flow: column; grid-auto-columns: minmax(210px, 1fr); gap: 1.25rem;
    overflow-x: auto; scroll-snap-type: x mandatory; padding-bottom: 1rem; scrollbar-width: thin;
  }
  .related-carousel > * { scroll-snap-align: start; }
  @media (min-width: 1024px) {
    .related-carousel { grid-auto-flow: row; grid-template-columns: repeat(4, minmax(0, 1fr)); overflow: visible; }
  }

  /* Skeletons */
  .sk { background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 37%, #f3f4f6 63%); background-size: 400% 100%; animation: sk 1.4s ease infinite; border-radius: 0.25rem; }
  @keyframes sk { 0% { background-position: 100% 50% } 100% { background-position: 0 50% } }
</style>

<main class="pdp-page">
  <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10 py-6 sm:py-10">
    
    <!-- Breadcrumb -->
    <nav class="mb-6 sm:mb-10" aria-label="Breadcrumb">
      <a href="<?php echo BASE_URL; ?>shop.php" class="eyebrow hover:text-gray-900 transition flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> BACK TO CATALOG
      </a>
    </nav>

    <!-- Product Details Container -->
    <article id="pdp-container" class="pdp-split" aria-label="Product Details">
      <!-- Skeleton (gallery edge-to-edge on mobile, no radius) -->
      <div class="pdp-gallery-wrapper">
        <div class="sk aspect-[4/5] lg:aspect-[1/1.15] w-full rounded-none lg:rounded-2xl"></div>
        <div class="hidden lg:flex gap-3 mt-4">
          <div class="sk h-20 w-20 rounded-lg"></div>
          <div class="sk h-20 w-20 rounded-lg"></div>
          <div class="sk h-20 w-20 rounded-lg"></div>
        </div>
      </div>
      <div class="pdp-details-wrapper pdp-buy">
        <div class="sk h-4 w-32 mb-6"></div>
        <div class="sk h-10 w-full mb-4"></div>
        <div class="sk h-10 w-3/4 mb-8"></div>
        <div class="sk h-12 w-48 mb-8"></div>
        <div class="sk h-32 w-full mb-8"></div>
        <div class="sk h-14 w-full mb-4"></div>
        <div class="sk h-14 w-full"></div>
      </div>
    </article>

    <!-- Related Products -->
    <section id="related-products-section" class="mt-20 sm:mt-32 hidden" aria-label="Related Products">
      <div class="mb-10">
        <span class="eyebrow block mb-3 text-center sm:text-left">Curated For You</span>
        <h2 class="font-display text-3xl sm:text-4xl text-[#12090c] tracking-tight text-center sm:text-left">
          You may also <span class="italic text-[#f59e0b]">love</span>
        </h2>
      </div>
      <div id="related-products-grid" class="related-carousel"></div>
    </section>

  </div>
</main>

<!-- Mobile Sticky CTA Bar (hidden on desktop via .mobile-only) -->
<div id="pdp-sticky-bar" class="sticky-cta-bar mobile-only hidden-bar" role="complementary" aria-label="Quick Actions">
  <div class="flex items-center gap-4 max-w-[1400px] mx-auto">
    <div class="flex-shrink-0 min-w-0 flex flex-col">
      <span id="sticky-bar-price" class="font-display text-xl font-bold text-[#12090c] truncate"></span>
    </div>
    <div class="flex gap-2 flex-1 justify-end">
      <button id="sticky-btn-buy" onclick="ProductPage.addToCart(true)" class="btn-primary-massive !py-3 !px-4 !text-[11px] !min-h-[44px]">
        BUY NOW
      </button>
    </div>
  </div>
</div>

<!-- Share Modal -->
<div id="share-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-[#12090c]/60 backdrop-blur-sm" onclick="ProductPage.closeShareModal()"></div>
    
    <!-- Modal Content -->
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 overflow-hidden animate-fade-in-up">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h3 class="font-display font-bold text-lg text-[#12090c]">Share Product</h3>
            <button type="button" onclick="ProductPage.closeShareModal()" class="text-gray-400 hover:text-red-500 transition p-2 -mr-2 rounded-full hover:bg-red-50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <!-- Grid of Share Options -->
        <div class="p-6 grid grid-cols-4 gap-4">
            <a id="share-btn-wa" href="#" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center gap-2 group">
                <div class="w-12 h-12 rounded-full bg-[#25D366]/10 text-[#25D366] flex items-center justify-center text-2xl group-hover:bg-[#25D366] group-hover:text-white transition duration-300">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <span class="text-[10px] font-bold text-gray-500 group-hover:text-[#12090c]">WhatsApp</span>
            </a>
            
            <a id="share-btn-fb" href="#" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center gap-2 group">
                <div class="w-12 h-12 rounded-full bg-[#1877F2]/10 text-[#1877F2] flex items-center justify-center text-2xl group-hover:bg-[#1877F2] group-hover:text-white transition duration-300">
                    <i class="fab fa-facebook-f"></i>
                </div>
                <span class="text-[10px] font-bold text-gray-500 group-hover:text-[#12090c]">Facebook</span>
            </a>
            
            <a id="share-btn-tw" href="#" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center gap-2 group">
                <div class="w-12 h-12 rounded-full bg-[#1DA1F2]/10 text-[#1DA1F2] flex items-center justify-center text-2xl group-hover:bg-[#1DA1F2] group-hover:text-white transition duration-300">
                    <i class="fab fa-twitter"></i>
                </div>
                <span class="text-[10px] font-bold text-gray-500 group-hover:text-[#12090c]">Twitter</span>
            </a>
            
            <a id="share-btn-tg" href="#" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center gap-2 group">
                <div class="w-12 h-12 rounded-full bg-[#0088cc]/10 text-[#0088cc] flex items-center justify-center text-2xl group-hover:bg-[#0088cc] group-hover:text-white transition duration-300">
                    <i class="fab fa-telegram-plane"></i>
                </div>
                <span class="text-[10px] font-bold text-gray-500 group-hover:text-[#12090c]">Telegram</span>
            </a>
        </div>
        
        <!-- Copy Link Row -->
        <div class="px-6 pb-6 pt-2">
            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-xl border border-gray-200">
                <input type="text" id="share-link-input" readonly class="bg-transparent border-none outline-none text-xs font-medium text-gray-500 flex-1 px-2 min-w-0" value="">
                <button type="button" onclick="ProductPage.copyShareLink()" class="flex-shrink-0 bg-white border border-gray-200 shadow-sm text-gray-700 px-4 py-2 rounded-lg text-xs font-bold hover:text-[#990024] hover:border-[#990024]/30 transition">
                    <i class="far fa-copy mr-1.5"></i>Copy
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/product.js?v=<?php echo time(); ?>"></script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>