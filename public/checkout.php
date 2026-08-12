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
  .checkout-page { background: #ffffff; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 80vh; }
  .checkout-page .font-display,
  .checkout-page h1, .checkout-page h2, .checkout-page h3 { font-family: 'Fraunces', serif; }
  
  .eyebrow {
    font-size: 11px; letter-spacing: 0.15em; text-transform: uppercase;
    font-weight: 800; color: var(--crimson);
  }

  /* ---- 12-Column Layout ---- */
  .checkout-split {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    align-items: start;
  }
  .co-main-wrapper { grid-column: span 12 / span 12; display: flex; flex-direction: column; gap: 2.5rem; }
  .co-summary-wrapper { grid-column: span 12 / span 12; }

  @media (min-width: 1024px) {
    .checkout-split { gap: 4rem; }
    .co-main-wrapper { grid-column: span 7 / span 7; }
    .co-summary-wrapper { grid-column: span 5 / span 5; position: sticky; top: 100px; }
  }
  @media (min-width: 1280px) {
    .co-main-wrapper { grid-column: span 8 / span 8; }
    .co-summary-wrapper { grid-column: span 4 / span 4; }
  }

  /* ---- Form Elements ---- */
  .co-section {
    background: #fff; padding-bottom: 2rem; border-bottom: 1px solid #e5e7eb;
  }
  .co-section:last-child { border-bottom: none; }
  
  .co-step-header {
    display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;
  }
  .co-step-num {
    width: 32px; height: 32px; background: var(--crimson); color: #fff; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800;
  }
  .co-step-title { font-size: 1.25rem; font-weight: 800; color: var(--ink); }

  .co-form-grid {
    display: grid; grid-template-columns: 1fr; gap: 1.25rem;
  }
  @media (min-width: 640px) {
    .co-form-grid { grid-template-columns: repeat(2, 1fr); }
    .co-col-span-2 { grid-column: span 2 / span 2; }
  }

  .co-label {
    display: block; font-size: 12px; font-weight: 700; color: #4b5563; margin-bottom: 0.5rem;
  }
  .co-input {
    width: 100%; height: 52px; border: 1px solid #d1d5db; border-radius: 0.375rem;
    padding: 0 16px; font-size: 14px; color: var(--ink); font-weight: 500; background: #fff;
    transition: all 0.2s ease;
  }
  .co-input:focus {
    border-color: var(--crimson); outline: none; box-shadow: 0 0 0 3px rgba(153, 0, 36, 0.1);
  }
  .co-textarea {
    width: 100%; min-height: 100px; border: 1px solid #d1d5db; border-radius: 0.375rem;
    padding: 12px 16px; font-size: 14px; color: var(--ink); font-weight: 500; background: #fff;
    transition: all 0.2s ease; resize: vertical;
  }
  .co-textarea:focus {
    border-color: var(--crimson); outline: none; box-shadow: 0 0 0 3px rgba(153, 0, 36, 0.1);
  }

  /* ---- Payment Methods ---- */
  .payment-methods-grid {
    display: flex; flex-direction: column; gap: 1rem;
  }
  .pm-card {
    display: flex; align-items: center; gap: 1rem; padding: 1.25rem;
    border: 2px solid #e5e7eb; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s ease;
    background: #fff;
  }
  .pm-card:hover { border-color: #d1d5db; }
  .pm-card.active { border-color: var(--crimson); background: #fffdf7; }
  
  .pm-radio {
    width: 22px; height: 22px; border: 2px solid #d1d5db; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .pm-card.active .pm-radio { border-color: var(--crimson); }
  .pm-card.active .pm-radio::after {
    content: ''; width: 10px; height: 10px; background: var(--crimson); border-radius: 50%;
  }

  .pm-icon {
    width: 44px; height: 44px; background: #f3f4f6; border-radius: 0.375rem;
    display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: #4b5563;
  }
  .pm-card.active .pm-icon { background: rgba(153, 0, 36, 0.1); color: var(--crimson); }
  
  .pm-details h4 { font-size: 15px; font-weight: 800; color: var(--ink); margin-bottom: 2px; }
  .pm-details p { font-size: 12px; font-weight: 600; color: #6b7280; }

  /* ---- Order Summary ---- */
  .summary-card {
    background: #f9fafb; border-radius: 1rem; padding: 1.25rem; border: 1px solid #e5e7eb;
  }
  @media (min-width: 1024px) {
    .summary-card { padding: 2rem; }
  }
  .summary-items {
    max-height: 40vh; overflow-y: auto; margin-bottom: 1.5rem; padding-right: 0.5rem;
  }
  .summary-item {
    display: flex; gap: 1rem; align-items: center; padding: 1rem 0; border-bottom: 1px solid #e5e7eb;
  }
  .summary-item-img {
    width: 64px; height: 64px; border-radius: 0.5rem; overflow: hidden; background: #fff; border: 1px solid #e5e7eb; flex-shrink: 0;
  }
  .summary-item-img img { width: 100%; height: 100%; object-fit: cover; }
  
  .summary-row {
    display: flex; justify-content: space-between; font-size: 14px; font-weight: 600; color: #4b5563; margin-bottom: 1rem;
  }
  .summary-row.total {
    border-top: 1px solid #e5e7eb; padding-top: 1.5rem; margin-top: 0.5rem;
    font-size: 18px; font-weight: 800; color: var(--ink);
  }

  /* Buttons */
  .btn-outline {
    border: 1px solid #d1d5db; background: transparent; color: var(--ink);
    font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;
    padding: 12px 24px; border-radius: 0.375rem; cursor: pointer; transition: all 0.2s ease;
  }
  .btn-outline:hover { background: #f3f4f6; }
  
  .btn-primary-massive {
    width: 100%; background: var(--crimson); color: #fff; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;
    border-radius: 0.375rem; padding: 1.25rem; font-size: 14px; min-height: 56px;
    display: flex; align-items: center; justify-content: center; gap: 0.75rem;
    cursor: pointer; transition: all 0.2s ease; border: none;
  }
  .btn-primary-massive:hover { background: #7a001c; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(153, 0, 36, 0.3); }

  /* Trust Badges */
  .trust-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 24px; margin-top: 1.5rem; }
  .trust-badge { display: flex; align-items: center; gap: 8px; color: #6b7280; font-size: 12px; font-weight: 600; }
  .trust-badge i { font-size: 16px; color: var(--ink); }

  /* Mobile summary toggle */
  .mobile-summary-toggle {
    display: flex; justify-content: space-between; align-items: center; width: 100%;
    padding: 1.25rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem;
    font-size: 14px; font-weight: 800; color: var(--ink); margin-bottom: 1.5rem; cursor: pointer;
  }
  .mobile-summary-toggle i.fa-chevron-down { transition: transform 0.3s ease; }
  .mobile-summary-toggle.open i.fa-chevron-down { transform: rotate(180deg); }
  .order-summary-collapsible { display: none; }
  .order-summary-collapsible.expanded { display: block; }

  @media (min-width: 1024px) {
    .mobile-summary-toggle { display: none; }
    .order-summary-collapsible { display: block !important; }
  }
  
  /* Address Cards */
  .address-card {
    display: flex; align-items: flex-start; gap: 1rem; padding: 1.25rem;
    border: 2px solid #e5e7eb; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s ease;
    background: #fff;
  }
  .address-card:hover { border-color: #d1d5db; }
  .address-card.active { border-color: var(--crimson); background: #fffdf7; }
</style>

<div class="checkout-page">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10 py-6 sm:py-10">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <a href="<?php echo BASE_URL; ?>cart.php" class="eyebrow hover:text-gray-900 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> RETURN TO BAG
            </a>
            <div class="flex items-center gap-2 sm:gap-3 bg-green-50 px-2.5 sm:px-4 py-2 border border-green-100 rounded-full min-w-0">
                <i class="fas fa-lock text-green-600 shrink-0"></i>
                <span class="text-[10px] sm:text-xs font-extrabold text-green-800 uppercase tracking-widest truncate">100% Secure Checkout</span>
            </div>
        </div>

        <div class="checkout-split">
            <!-- Left Column: Forms -->
            <div class="co-main-wrapper">
                
                <!-- 1. Shipping Address -->
                <div class="co-section">
                    <div class="co-step-header">
                        <div class="co-step-num">1</div>
                        <h2 class="co-step-title font-display">Shipping Address</h2>
                    </div>

                    <div id="checkout-addresses-list" class="flex flex-col gap-4 mb-6">
                        <!-- Loaded via JS -->
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <button type="button" id="toggle-new-addr" onclick="CheckoutPage.toggleAddressForm()" class="eyebrow hover:text-gray-900 transition flex items-center gap-2 mb-4">
                            <i class="fas fa-plus"></i> ADD NEW ADDRESS
                        </button>
                        
                        <div id="new-addr-form-wrap" class="hidden bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <form id="frm-checkout-address" class="co-form-grid">
                                <div>
                                    <label for="addr-name" class="co-label">Full Name</label>
                                    <input type="text" id="addr-name" required placeholder="e.g. Rahul Sharma" autocomplete="name" class="co-input">
                                </div>
                                <div>
                                    <label for="addr-phone" class="co-label">Phone Number</label>
                                    <input type="tel" id="addr-phone" required placeholder="e.g. 9876543210" inputmode="tel" autocomplete="tel" class="co-input">
                                </div>

                                <div class="co-col-span-2">
                                    <label for="addr-line1" class="co-label">Address Line 1</label>
                                    <input type="text" id="addr-line1" required placeholder="Flat / House No, Building" autocomplete="address-line1" class="co-input">
                                </div>

                                <div class="co-col-span-2">
                                    <label for="addr-line2" class="co-label">Address Line 2 (Optional)</label>
                                    <input type="text" id="addr-line2" placeholder="Street, Landmark" autocomplete="address-line2" class="co-input">
                                </div>

                                <div>
                                    <label for="addr-city" class="co-label">City</label>
                                    <input type="text" id="addr-city" required placeholder="Bengaluru" autocomplete="address-level2" class="co-input">
                                </div>
                                <div>
                                    <label for="addr-state" class="co-label">State</label>
                                    <input type="text" id="addr-state" required placeholder="Karnataka" autocomplete="address-level1" class="co-input">
                                </div>
                                <div>
                                    <label for="addr-pincode" class="co-label">Pincode</label>
                                    <input type="text" id="addr-pincode" required placeholder="560001" inputmode="numeric" autocomplete="postal-code" class="co-input">
                                </div>
                                <div class="flex items-end pb-2">
                                    <label class="flex items-center text-sm font-bold text-gray-700 cursor-pointer">
                                        <input type="checkbox" id="addr-default" class="h-4 w-4 text-[#990024] border-gray-300 rounded mr-3 focus:ring-[#990024]">
                                        Save as default address
                                    </label>
                                </div>

                                <div class="co-col-span-2 pt-4 flex gap-4">
                                    <input type="hidden" id="addr-type" value="shipping">
                                    <button type="submit" id="btn-save-address" class="btn-outline bg-gray-900 text-white hover:bg-gray-800 flex-1">Save Address</button>
                                    <button type="button" onclick="CheckoutPage.toggleAddressForm()" class="btn-outline flex-1 text-center">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 2. Payment Method -->
                <div class="co-section">
                    <div class="co-step-header">
                        <div class="co-step-num">2</div>
                        <h2 class="co-step-title font-display">Payment Method</h2>
                    </div>

                    <div class="payment-methods-grid" id="payment-methods">
                        <!-- COD -->
                        <label class="pm-card active" data-method="cod" onclick="CheckoutPage.selectPayment(this, 'cod')">
                            <input type="radio" name="payment_method" value="cod" checked class="sr-only">
                            <div class="pm-radio"></div>
                            <div class="pm-icon"><i class="fas fa-box"></i></div>
                            <div class="pm-details">
                                <h4>Cash on Delivery</h4>
                                <p>Pay when you receive the order</p>
                            </div>
                        </label>

                        <!-- Razorpay -->
                        <label class="pm-card" data-method="razorpay" onclick="CheckoutPage.selectPayment(this, 'razorpay')">
                            <input type="radio" name="payment_method" value="razorpay" class="sr-only">
                            <div class="pm-radio"></div>
                            <div class="pm-icon"><i class="fas fa-credit-card"></i></div>
                            <div class="pm-details">
                                <h4>Cards / NetBanking</h4>
                                <p>Visa, Mastercard, NetBanking</p>
                            </div>
                        </label>

                        <!-- UPI -->
                        <label class="pm-card" data-method="upi" onclick="CheckoutPage.selectPayment(this, 'upi')">
                            <input type="radio" name="payment_method" value="upi" class="sr-only">
                            <div class="pm-radio"></div>
                            <div class="pm-icon"><i class="fas fa-qrcode"></i></div>
                            <div class="pm-details">
                                <h4>UPI Payment</h4>
                                <p>GPay, PhonePe, Paytm, Amazon Pay</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 3. Notes -->
                <div class="co-section">
                    <div class="co-step-header">
                        <div class="co-step-num">3</div>
                        <h2 class="co-step-title font-display">Delivery Notes <span class="text-sm font-sans font-normal text-gray-400">(Optional)</span></h2>
                    </div>
                    <textarea id="checkout-notes" placeholder="Add delivery instructions or special request..." class="co-textarea"></textarea>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="co-summary-wrapper">
                <!-- Mobile toggle -->
                <button type="button" class="mobile-summary-toggle" id="mobile-summary-toggle" onclick="CheckoutPage.toggleMobileSummary()">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-shopping-bag text-[#990024]"></i>
                        Show order summary
                    </span>
                    <span class="flex items-center gap-2 text-[#990024]">
                        <span id="co-total-inline">₹0</span>
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </button>

                <div class="order-summary-collapsible" id="order-summary-body">
                    <div class="summary-card">
                        <h3 class="font-display text-xl font-black text-[#12090c] pb-4 border-b border-gray-200 mb-6 hidden lg:block">
                            Order Summary
                        </h3>

                        <!-- Items -->
                        <div id="checkout-summary-items" class="summary-items custom-scrollbar">
                            <!-- Loaded via JS -->
                        </div>

                        <!-- Delhivery Pincode Serviceability & Live Rate Check -->
                        <div class="my-4 p-3.5 bg-white rounded-xl border border-amber-200/80 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between">
                                <label for="checkout-pincode-input" class="text-xs font-black text-[#12090c] uppercase tracking-wider flex items-center">
                                    <i class="fas fa-truck-fast text-[#990024] mr-1.5"></i> Delivery Pincode
                                </label>
                                <span class="text-[10px] font-bold text-gray-400">Delhivery Express</span>
                            </div>
                            <div class="flex gap-2">
                                <input type="text" id="checkout-pincode-input" placeholder="Enter 6-digit Pincode" maxlength="6" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs outline-none focus:border-[#990024] font-bold" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <button type="button" onclick="CheckoutPage.checkPincode()" class="bg-[#990024] hover:bg-[#7a001c] text-white text-xs font-extrabold px-3 py-2 rounded-lg transition duration-200 shrink-0">
                                    Check
                                </button>
                            </div>
                            <div id="pincode-status-badge"></div>
                        </div>

                        <!-- Totals -->
                        <div class="pt-4 border-t border-gray-200">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span class="font-bold text-[#12090c]" id="co-subtotal">₹0</span>
                            </div>
                            <div class="summary-row text-green-700">
                                <span>Discount</span>
                                <span id="co-discount">-₹0</span>
                            </div>
                            <div class="summary-row">
                                <span>Tax</span>
                                <span class="font-bold text-[#12090c]" id="co-tax">₹0</span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping Charges</span>
                                <span class="font-bold text-[#12090c]" id="co-shipping">₹0</span>
                            </div>

                            <!-- Grand Total -->
                            <div class="summary-row total items-end">
                                <span class="text-sm text-gray-500 font-bold uppercase tracking-widest mb-1">Total</span>
                                <span class="font-display text-3xl font-black text-[#990024]" id="co-total">₹0</span>
                            </div>
                        </div>

                        <div class="mt-8">
                            <button type="button" id="btn-place-order-desktop" onclick="CheckoutPage.placeOrder()" class="btn-primary-massive">
                                <i class="fas fa-lock text-[#f59e0b] mr-1"></i>
                                <span>PLACE ORDER</span>
                            </button>
                        </div>

                        <div class="trust-row mt-6">
                            <div class="trust-badge" title="Secure Encrypted Checkout"><i class="fas fa-lock"></i> Secure</div>
                            <div class="trust-badge" title="Guaranteed Quality"><i class="fas fa-shield-halved"></i> Guarantee</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Sticky Place Order Bar (Hidden on Desktop) -->
<div id="checkout-sticky-bar" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-[0_-4px_12px_rgba(0,0,0,0.05)] z-50 lg:hidden flex justify-between items-center pb-[calc(16px+env(safe-area-inset-bottom))] hidden">
    <div class="flex flex-col min-w-0 mr-2">
        <span class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Total</span>
        <span id="co-sticky-total" class="font-display text-xl font-black text-[#12090c] truncate">₹0</span>
    </div>
    <button type="button" id="btn-place-order" onclick="CheckoutPage.placeOrder()" class="bg-[#990024] hover:bg-[#7a001c] text-white font-extrabold text-xs uppercase tracking-widest px-4 sm:px-8 py-3.5 rounded-md shadow-md transition flex items-center gap-2 flex-shrink-0">
        <i class="fas fa-lock"></i> PLACE ORDER
    </button>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/checkout.js?v=<?php echo time(); ?>"></script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
