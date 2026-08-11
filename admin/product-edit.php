<?php
// admin/product-edit.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-2">
            <a href="/admin/products.php" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                <i class="ph ph-arrow-left text-lg"></i>
            </a>
            <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Back to Catalog</span>
        </div>
        <h2 id="edit-prod-title" class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Add Product</h2>
    </div>
    <div class="flex items-center gap-3">
        <button type="button" onclick="document.getElementById('btn-save-product').click()" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors shadow-sm w-full sm:w-auto">
            <i class="ph ph-floppy-disk mr-2 text-lg"></i> Save Changes
        </button>
    </div>
</div>

<form id="frm-product-edit" class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8 pb-12">
    <!-- Main Form Fields Column -->
    <div class="xl:col-span-2 space-y-6">
        
        <!-- Basic Information Card -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-info mr-2 text-slate-400"></i> Basic Information
                </h3>
            </div>
            <div class="p-5 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="edit-prod-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Product Name</label>
                        <input type="text" id="edit-prod-name" required placeholder="e.g. iPhone 16 Pro Max" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                    <div>
                        <label for="edit-prod-slug" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Slug (URL Keyword)</label>
                        <input type="text" id="edit-prod-slug" placeholder="e.g. iphone-16-pro-max" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                </div>

                <div>
                    <label for="edit-prod-short-desc" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Short Description</label>
                    <textarea id="edit-prod-short-desc" rows="2" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors resize-none" placeholder="Brief summary for catalog preview..."></textarea>
                </div>

                <div>
                    <label for="edit-prod-desc" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Detailed Description</label>
                    <textarea id="edit-prod-desc" rows="6" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors" placeholder="Full product specifications and details (HTML supported)..."></textarea>
                </div>
            </div>
        </div>

        <!-- Pricing & Inventory Card -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-currency-inr mr-2 text-slate-400"></i> Pricing & Inventory
                </h3>
            </div>
            <div class="p-5 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label for="edit-prod-price" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Selling Price (₹)</label>
                        <input type="number" step="0.01" id="edit-prod-price" required placeholder="0.00" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                    <div>
                        <label for="edit-prod-mrp" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">MRP Price (₹)</label>
                        <input type="number" step="0.01" id="edit-prod-mrp" placeholder="0.00" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                    <div>
                        <label for="edit-prod-tax-rate" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">GST Rate (%)</label>
                        <input type="number" step="0.01" id="edit-prod-tax-rate" value="18.00" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label for="edit-prod-sku" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">SKU</label>
                        <input type="text" id="edit-prod-sku" placeholder="Stock Keeping Unit" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                    <div>
                        <label for="edit-prod-stock" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Stock Qty</label>
                        <input type="number" id="edit-prod-stock" value="0" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                    <div>
                        <label for="edit-prod-low-stock" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Low Stock Alert At</label>
                        <input type="number" id="edit-prod-low-stock" value="5" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping Card -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-truck mr-2 text-slate-400"></i> Shipping Details
                </h3>
            </div>
            <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label for="edit-prod-shipping" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Shipping Charge (₹)</label>
                    <input type="number" step="0.01" id="edit-prod-shipping" value="0.00" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                </div>
                <div>
                    <label for="edit-prod-weight" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Weight (kg)</label>
                    <input type="number" step="0.001" id="edit-prod-weight" placeholder="0.000" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                </div>
                <div>
                    <label for="edit-prod-dimensions" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Dimensions (LxWxH)</label>
                    <input type="text" id="edit-prod-dimensions" placeholder="e.g. 15x7x0.8 cm" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                </div>
        <!-- Search Engine Optimization (SEO) & Keywords Card -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-magnifying-glass mr-2 text-slate-400"></i> Search Engine Optimization (SEO)
                </h3>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label for="edit-prod-meta-title" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">SEO Title Tag</label>
                    <input type="text" id="edit-prod-meta-title" placeholder="e.g. Designer Rakhi Set for Brother | Trisha Utsav" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    <span class="text-xs text-slate-400 mt-1 block">Custom page title for search engines (defaults to product name if empty).</span>
                </div>

                <div>
                    <label for="edit-prod-meta-keywords" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        SEO Keywords <span class="text-xs text-slate-400 font-normal">(Comma-separated)</span>
                    </label>
                    <textarea id="edit-prod-meta-keywords" rows="2" placeholder="e.g. rakhi for brother, designer rakhi, raksha bandhan special, floral rakhi, bhaiya bhabhi rakhi set" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors"></textarea>
                    <span class="text-xs text-slate-400 mt-1 block">Enter target keywords and search phrases separated by commas for search engines and platform indexing.</span>
                </div>

                <div>
                    <label for="edit-prod-meta-desc" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Meta Description</label>
                    <textarea id="edit-prod-meta-desc" rows="3" placeholder="Brief search snippet description..." class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors"></textarea>
                    <span class="text-xs text-slate-400 mt-1 block">Recommended length: 150-160 characters.</span>
                </div>
            </div>
        </div>

        <!-- Hidden button for form submission via external button -->
        <button type="submit" id="btn-save-product" class="hidden"></button>
    </div>

    <!-- Sidebar Fields Column -->
    <div class="xl:col-span-1 space-y-6">
        
        <!-- Organization Card -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-folder mr-2 text-slate-400"></i> Organization
                </h3>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label for="edit-prod-status" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Publishing Status</label>
                    <select id="edit-prod-status" required class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                        <option value="draft">Draft (Hidden)</option>
                        <option value="published">Published (Visible)</option>
                        <option value="archived">Archived (Deactivated)</option>
                    </select>
                </div>

                <div>
                    <label for="edit-prod-category" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Category</label>
                    <select id="edit-prod-category" required class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                        <!-- Loaded dynamically -->
                    </select>
                </div>

                <div>
                    <label for="edit-prod-brand" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Brand</label>
                    <select id="edit-prod-brand" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                        <!-- Loaded dynamically -->
                    </select>
                </div>

                <div>
                    <label for="edit-prod-occasion" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1 flex items-center">
                        Festive Occasion
                    </label>
                    <select id="edit-prod-occasion" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                        <option value="">None / General Catalog</option>
                        <!-- Loaded dynamically -->
                    </select>
                </div>
            </div>
        </div>

        <!-- Curation Card -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-star mr-2 text-slate-400"></i> Curation & Badges
                </h3>
            </div>
            <div class="p-5 space-y-4">
                <label class="flex items-start cursor-pointer group">
                    <div class="flex items-center h-5">
                        <input type="checkbox" id="edit-prod-featured" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded dark:bg-slate-800 dark:border-slate-600 dark:checked:bg-primary-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <span class="font-medium text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Featured</span>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Show on homepage featured sections.</p>
                    </div>
                </label>
                
                <label class="flex items-start cursor-pointer group">
                    <div class="flex items-center h-5">
                        <input type="checkbox" id="edit-prod-is-trending" class="h-4 w-4 text-amber-500 focus:ring-amber-500 border-slate-300 rounded dark:bg-slate-800 dark:border-slate-600 dark:checked:bg-amber-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <span class="font-medium text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-500 transition-colors flex items-center"><i class="ph-fill ph-fire text-amber-500 mr-1.5 text-lg"></i> Trending</span>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Add 'Hot Right Now' badge.</p>
                    </div>
                </label>

                <label class="flex items-start cursor-pointer group">
                    <div class="flex items-center h-5">
                        <input type="checkbox" id="edit-prod-is-must-buy" class="h-4 w-4 text-emerald-500 focus:ring-emerald-500 border-slate-300 rounded dark:bg-slate-800 dark:border-slate-600 dark:checked:bg-emerald-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <span class="font-medium text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-500 transition-colors flex items-center"><i class="ph-fill ph-sparkle text-emerald-500 mr-1.5 text-lg"></i> Must Buy</span>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Add 'Editor's Choice' badge.</p>
                    </div>
                </label>
            </div>
        </div>

    </div>
</form>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 lg:gap-8 pb-12">
    <!-- Variants / Attributes Manager -->
    <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                <i class="ph ph-squares-four mr-2 text-slate-400"></i> Variants & Attributes
            </h3>
        </div>
        <div class="p-5 flex-1 flex flex-col">
            <div id="attributes-list-container" class="space-y-3 mb-6">
                <!-- Loaded dynamically -->
                <p class="text-sm text-slate-500 dark:text-slate-400">No variants added yet.</p>
            </div>

            <div class="pt-5 border-t border-slate-200 dark:border-slate-700 mt-auto">
                <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-3">Add Variant Option</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                    <div>
                        <label for="attr-name-input" class="sr-only">Attribute Name</label>
                        <input type="text" id="attr-name-input" placeholder="Name (e.g. Size)" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                    <div>
                        <label for="attr-val-input" class="sr-only">Value</label>
                        <input type="text" id="attr-val-input" placeholder="Value (e.g. XL)" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                    <div>
                        <label for="attr-price-input" class="sr-only">Extra Price</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-500 sm:text-sm">₹</span>
                            </div>
                            <input type="number" step="0.01" id="attr-price-input" placeholder="0.00" class="block w-full pl-7 pr-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>
                </div>
                <button type="button" onclick="Products.addAttribute()" class="w-full inline-flex items-center justify-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                    <i class="ph ph-plus mr-2"></i> Add Variant
                </button>
            </div>
        </div>
        <!-- Product Gallery Images Overhauled Section -->
    <div id="image-upload-section" class="hidden bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col xl:col-span-2">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-image mr-2 text-slate-400"></i> Media Gallery Manager
                </h3>
                <p class="text-xs text-slate-500 mt-1">Upload multiple files, drag to reorder, edit SEO alt tags, and crop images.</p>
            </div>
            
            <!-- Bulk Actions Manager -->
            <div id="gallery-bulk-actions" class="hidden flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-500"><span id="gallery-selected-count">0</span> Selected</span>
                <button type="button" onclick="Products.bulkDeleteImages()" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm">
                    <i class="ph ph-trash mr-1.5"></i> Delete Selected
                </button>
            </div>
        </div>
        
        <div class="p-5 flex-1 flex flex-col space-y-6">
            
            <!-- Drag and Drop Zone -->
            <div id="image-dropzone" class="border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-xl p-8 hover:border-red-500 dark:hover:border-red-500 bg-slate-50/30 transition-all flex flex-col items-center justify-center cursor-pointer text-center group">
                <i class="ph ph-cloud-arrow-up text-4xl text-slate-400 group-hover:text-red-500 group-hover:scale-105 transition-all mb-3 animate-pulse"></i>
                <span class="text-sm font-semibold text-slate-800">Drag & drop product images here</span>
                <span class="text-xs text-slate-500 mt-1">or <span class="text-red-600 font-bold underline">browse local files</span></span>
                <span class="text-[10px] text-slate-400 mt-2 font-mono">PNG, JPEG, WEBP, AVIF, GIF (Max 5MB per file)</span>
                <input id="image-file-input" type="file" multiple accept="image/*" class="hidden">
            </div>

            <!-- Upload Queue Progress Bars -->
            <div id="upload-queue-container" class="hidden space-y-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center"><i class="ph ph-spinner-gap animate-spin mr-1.5"></i> Upload Queue</span>
                    <span id="queue-status-text" class="text-xs font-semibold text-slate-500">0/0 uploaded</span>
                </div>
                <div id="queue-list" class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    <!-- Dynamic queue items -->
                </div>
            </div>

            <!-- Header of Grid / Selection tools -->
            <div id="grid-controls-bar" class="flex items-center justify-between pb-2 border-b border-slate-100">
                <label class="flex items-center cursor-pointer group select-none">
                    <input type="checkbox" id="gallery-select-all" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded cursor-pointer">
                    <span class="ml-2 text-xs font-semibold text-slate-600 group-hover:text-slate-900 transition-colors">Select All Images</span>
                </label>
                <span class="text-xs font-semibold text-slate-500">Drag to reorder grid items</span>
            </div>

            <!-- Image Grid -->
            <div id="attached-images-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <!-- Loaded dynamically -->
            </div>
            
        </div>
    </div>

    <!-- Inline Metadata Editing Modal -->
    <div id="metadata-modal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-250 shadow-2xl w-full max-w-md overflow-hidden animate-scaleIn">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h4 class="text-sm font-bold text-slate-950 flex items-center"><i class="ph ph-pencil-simple mr-1.5 text-red-600"></i> Edit Image Properties</h4>
                <button type="button" onclick="Products.closeMetadataModal()" class="text-slate-400 hover:text-slate-600 transition"><i class="ph ph-x text-lg"></i></button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label for="meta-alt-text" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Alt Text (Required for SEO)</label>
                    <input type="text" id="meta-alt-text" maxlength="125" placeholder="Describe this product photo (max 125 chars)..." class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <div class="flex justify-between items-center mt-1">
                        <span id="alt-validation-msg" class="text-[10px] text-red-500 hidden font-semibold">Please enter an alt description.</span>
                        <span id="alt-char-counter" class="text-[10px] text-slate-400 font-mono ml-auto">0/125</span>
                    </div>
                </div>
                <div>
                    <label for="meta-title" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Title (Optional Tooltip)</label>
                    <input type="text" id="meta-title" placeholder="Image Title Attribute..." class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3">
                <button type="button" onclick="Products.closeMetadataModal()" class="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition">Cancel</button>
                <button type="button" id="btn-save-metadata" class="px-4 py-2 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-700 transition shadow-sm">Save Metadata</button>
            </div>
        </div>
    </div>

    <!-- Cropper Modal -->
    <div id="cropper-modal" class="hidden fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden animate-scaleIn">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h4 class="text-sm font-bold text-slate-950 flex items-center"><i class="ph ph-crop mr-1.5 text-red-600"></i> Crop Product Image</h4>
                <button type="button" onclick="Products.closeCropperModal()" class="text-slate-400 hover:text-slate-600 transition"><i class="ph ph-x text-lg"></i></button>
            </div>
            <div class="p-5 flex justify-center bg-slate-950/5 border-b border-slate-100">
                <div class="max-h-[350px] w-full flex items-center justify-center overflow-hidden">
                    <img id="cropper-image" src="" alt="Source crop photo" class="max-w-full max-h-full block">
                </div>
            </div>
            <div class="p-3 bg-slate-50/50 flex flex-wrap justify-center gap-2 border-b border-slate-100">
                <button type="button" onclick="window.cropper.setAspectRatio(1)" class="px-3 py-1.5 bg-white border border-slate-300 rounded-md text-xs font-medium hover:bg-slate-50 transition">1:1 Square</button>
                <button type="button" onclick="window.cropper.setAspectRatio(4/3)" class="px-3 py-1.5 bg-white border border-slate-300 rounded-md text-xs font-medium hover:bg-slate-50 transition">4:3 Aspect</button>
                <button type="button" onclick="window.cropper.setAspectRatio(16/9)" class="px-3 py-1.5 bg-white border border-slate-300 rounded-md text-xs font-medium hover:bg-slate-50 transition">16:9 Aspect</button>
                <button type="button" onclick="window.cropper.setAspectRatio(NaN)" class="px-3 py-1.5 bg-white border border-slate-300 rounded-md text-xs font-medium hover:bg-slate-50 transition">Free Form</button>
            </div>
            <div class="px-5 py-4 bg-slate-50/50 flex justify-end gap-3">
                <button type="button" onclick="Products.closeCropperModal()" class="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition">Cancel</button>
                <button type="button" id="btn-confirm-crop" class="px-4 py-2 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-700 transition shadow-sm">Apply & Re-upload</button>
            </div>
        </div>
    </div>
</div>

<!-- CDNs & Scripts for Image Management Overhaul -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fslightbox/3.0.9/index.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="/admin/assets/js/products.js?v=<?php echo time(); ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Products.initForm();
        
        // Show selected file name
        const fileInput = document.getElementById('image-file-input');
        const fileChosenText = document.getElementById('file-chosen-text');
        if(fileInput && fileChosenText) {
            fileInput.addEventListener('change', function(){
                if(this.files && this.files.length > 0) {
                    fileChosenText.textContent = this.files[0].name;
                    fileChosenText.classList.remove('hidden');
                } else {
                    fileChosenText.classList.add('hidden');
                }
            });
        }
    });
</script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
