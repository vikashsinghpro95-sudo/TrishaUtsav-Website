<?php
// admin/product-edit.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Header & Top Action Bar -->
<div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center space-x-2 text-sm text-slate-500 dark:text-slate-400 mb-1.5">
            <a href="/admin/products" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center">
                <i class="ph ph-arrow-left mr-1.5 text-base"></i> Back to Catalog
            </a>
            <span>/</span>
            <span class="text-slate-700 dark:text-slate-300 font-medium">Product Editor</span>
        </div>
        <div class="flex items-center space-x-3">
            <h2 id="edit-prod-title" class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Add Product</h2>
            <span id="prod-status-badge" class="hidden px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Draft</span>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <a href="/admin/products" class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors shadow-sm text-center">
            Cancel
        </a>
        <button type="button" onclick="document.getElementById('btn-save-product').click()" class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all shadow-md hover:shadow-lg">
            <i class="ph ph-floppy-disk mr-2 text-lg"></i> Save Changes
        </button>
    </div>
</div>

<form id="frm-product-edit" class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8 pb-16">
    
    <!-- Main Content Column (2 Columns wide) -->
    <div class="lg:col-span-2 space-y-6 sm:space-y-8">
        
        <!-- 1. Basic Information Card -->
        <div class="bg-white dark:bg-slate-850 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 flex items-center justify-center mr-3 border border-primary-100 dark:border-primary-900/50">
                        <i class="ph ph-info text-lg"></i>
                    </div>
                    Basic Information
                </h3>
                <span class="text-xs text-slate-400 font-medium">* Required fields</span>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="edit-prod-name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Product Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit-prod-name" required placeholder="e.g. Pure Silver Pooja Thali Set" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm transition-all shadow-xs">
                    </div>
                    <div>
                        <label for="edit-prod-slug" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Slug (URL Keyword)
                        </label>
                        <div class="relative">
                            <input type="text" id="edit-prod-slug" placeholder="e.g. pure-silver-pooja-thali-set" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm transition-all font-mono text-xs shadow-xs">
                        </div>
                    </div>
                </div>

                <div>
                    <label for="edit-prod-short-desc" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Short Description <span class="text-xs font-normal text-slate-400">(Shows on product cards & summary)</span>
                    </label>
                    <textarea id="edit-prod-short-desc" rows="2" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm transition-all resize-none shadow-xs" placeholder="Brief summary of features, material, and festive usage..."></textarea>
                </div>

                <div>
                    <label for="edit-prod-desc" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Detailed Description & Specifications
                    </label>
                    <textarea id="edit-prod-desc" rows="6" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm transition-all shadow-xs" placeholder="Complete product specifications, dimensions, care instructions, and package inclusions..."></textarea>
                </div>
            </div>
        </div>

        <!-- 2. Pricing & Inventory Card -->
        <div class="bg-white dark:bg-slate-850 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mr-3 border border-emerald-100 dark:border-emerald-900/50">
                        <i class="ph ph-currency-inr text-lg"></i>
                    </div>
                    Pricing & Inventory Management
                </h3>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Pricing Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4 bg-slate-50/50 dark:bg-slate-900/40 rounded-xl border border-slate-200/60 dark:border-slate-800/60">
                    <div>
                        <label for="edit-prod-price" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Selling Price (₹) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-semibold text-sm">₹</span>
                            <input type="number" step="0.01" id="edit-prod-price" required placeholder="0.00" class="block w-full pl-8 pr-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-bold focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all shadow-xs">
                        </div>
                    </div>
                    <div>
                        <label for="edit-prod-mrp" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            MRP Original Price (₹)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-semibold text-sm">₹</span>
                            <input type="number" step="0.01" id="edit-prod-mrp" placeholder="0.00" class="block w-full pl-8 pr-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all shadow-xs">
                        </div>
                    </div>
                    <div>
                        <label for="edit-prod-tax-rate" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            GST Tax Rate (%)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" id="edit-prod-tax-rate" value="0.00" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all shadow-xs">
                            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 font-semibold text-sm">%</span>
                        </div>
                    </div>
                </div>

                <!-- Inventory Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="edit-prod-sku" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            SKU Code
                        </label>
                        <input type="text" id="edit-prod-sku" placeholder="e.g. TU-MOLD-001" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all shadow-xs">
                    </div>
                    <div>
                        <label for="edit-prod-stock" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Available Stock Qty
                        </label>
                        <input type="number" id="edit-prod-stock" value="0" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all shadow-xs">
                    </div>
                    <div>
                        <label for="edit-prod-low-stock" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Low Stock Alert Threshold
                        </label>
                        <input type="number" id="edit-prod-low-stock" value="5" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all shadow-xs">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Media Gallery Manager Card -->
        <div id="image-upload-section" class="bg-white dark:bg-slate-850 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center mr-3 border border-amber-100 dark:border-amber-900/50">
                        <i class="ph ph-image text-lg"></i>
                    </div>
                    Media Gallery Manager
                </h3>
                
                <!-- Bulk Actions Manager -->
                <div id="gallery-bulk-actions" class="hidden flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400"><span id="gallery-selected-count">0</span> Selected</span>
                    <button type="button" onclick="Products.bulkDeleteImages()" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm">
                        <i class="ph ph-trash mr-1.5"></i> Delete Selected
                    </button>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Drag and Drop Zone -->
                <div id="image-dropzone" class="border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-2xl p-8 hover:border-primary-500 dark:hover:border-primary-500 bg-slate-50/40 dark:bg-slate-900/20 transition-all flex flex-col items-center justify-center cursor-pointer text-center group">
                    <div class="w-14 h-14 rounded-full bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform mb-3 shadow-inner">
                        <i class="ph ph-cloud-arrow-up"></i>
                    </div>
                    <span class="text-sm font-bold text-slate-800 dark:text-slate-200">Drag & drop product photos here</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 mt-1">or <span class="text-primary-600 dark:text-primary-400 font-bold underline">browse local files</span></span>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 font-mono">Supports PNG, JPG, WEBP, AVIF (Max 5MB each)</span>
                    <input id="image-file-input" type="file" multiple accept="image/*" class="hidden">
                </div>

                <!-- Upload Queue Progress Bars -->
                <div id="upload-queue-container" class="hidden space-y-3 p-4 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center">
                            <i class="ph ph-spinner-gap animate-spin mr-1.5 text-primary-500"></i> Upload Queue
                        </span>
                        <span id="queue-status-text" class="text-xs font-semibold text-slate-500 dark:text-slate-400">0/0 uploaded</span>
                    </div>
                    <div id="queue-list" class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        <!-- Dynamic queue items -->
                    </div>
                </div>

                <!-- Grid Header & Selection tools -->
                <div id="grid-controls-bar" class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
                    <label class="flex items-center cursor-pointer group select-none">
                        <input type="checkbox" id="gallery-select-all" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded cursor-pointer">
                        <span class="ml-2 text-xs font-semibold text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">Select All Gallery Photos</span>
                    </label>
                    <span class="text-xs font-medium text-slate-400">Drag thumbnail cards to reorder</span>
                </div>

                <!-- Attached Images Grid -->
                <div id="attached-images-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <!-- Loaded dynamically by products.js -->
                </div>
            </div>
        </div>

        <!-- 4. Variants & Attributes Manager Card -->
        <div class="bg-white dark:bg-slate-850 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mr-3 border border-indigo-100 dark:border-indigo-900/50">
                        <i class="ph ph-squares-four text-lg"></i>
                    </div>
                    Variants & Attribute Options
                </h3>
            </div>
            
            <div class="p-6 space-y-6">
                <div id="attributes-list-container" class="space-y-3">
                    <!-- Loaded dynamically -->
                    <p class="text-xs text-slate-400 italic">No custom variants added yet.</p>
                </div>

                <div class="pt-5 border-t border-slate-200 dark:border-slate-800">
                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3">Add New Variant Option</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                        <div>
                            <label for="attr-name-input" class="sr-only">Attribute Name</label>
                            <input type="text" id="attr-name-input" placeholder="Name (e.g. Size)" class="block w-full px-3.5 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label for="attr-val-input" class="sr-only">Value</label>
                            <input type="text" id="attr-val-input" placeholder="Value (e.g. XL)" class="block w-full px-3.5 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label for="attr-price-input" class="sr-only">Extra Price</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-sm">₹</span>
                                <input type="number" step="0.01" id="attr-price-input" placeholder="0.00" class="block w-full pl-7 pr-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="Products.addAttribute()" class="inline-flex items-center justify-center px-4 py-2.5 border border-slate-300 dark:border-slate-700 shadow-sm text-sm font-semibold rounded-xl text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors w-full sm:w-auto">
                        <i class="ph ph-plus mr-1.5 text-base"></i> Add Variant Option
                    </button>
                </div>
            </div>
        </div>

        <!-- 5. Shipping & Logistics Card -->
        <div class="bg-white dark:bg-slate-850 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center mr-3 border border-blue-100 dark:border-blue-900/50">
                        <i class="ph ph-truck text-lg"></i>
                    </div>
                    Shipping & Package Logistics
                </h3>
            </div>
            
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="edit-prod-shipping" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Shipping Charge (₹)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">₹</span>
                        <input type="number" step="0.01" id="edit-prod-shipping" value="0.00" class="block w-full pl-8 pr-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all">
                    </div>
                </div>
                <div>
                    <label for="edit-prod-weight" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Item Weight (kg)
                    </label>
                    <input type="number" step="0.001" id="edit-prod-weight" placeholder="0.500" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all">
                </div>
                <div>
                    <label for="edit-prod-dimensions" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Dimensions (LxWxH)
                    </label>
                    <input type="text" id="edit-prod-dimensions" placeholder="e.g. 15x10x5 cm" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all">
                </div>
            </div>
        </div>

        <!-- 6. Search Engine Optimization (SEO) Card -->
        <div class="bg-white dark:bg-slate-850 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center mr-3 border border-purple-100 dark:border-purple-900/50">
                        <i class="ph ph-magnifying-glass text-lg"></i>
                    </div>
                    Search Engine Optimization (SEO)
                </h3>
            </div>
            
            <div class="p-6 space-y-6">
                <div>
                    <label for="edit-prod-meta-title" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        SEO Page Title Tag
                    </label>
                    <input type="text" id="edit-prod-meta-title" placeholder="e.g. Handmade Brass Diya Set for Diwali | Trisha Utsav" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all">
                    <span class="text-xs text-slate-400 mt-1 block">Custom title tag for search engines (defaults to product name if omitted).</span>
                </div>

                <div>
                    <label for="edit-prod-meta-keywords" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        SEO Meta Keywords <span class="text-xs text-slate-400 font-normal">(Comma-separated)</span>
                    </label>
                    <textarea id="edit-prod-meta-keywords" rows="2" placeholder="e.g. brass diya, diwali decoration, pooja essential, royal gift hamper" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all resize-none"></textarea>
                    <span class="text-xs text-slate-400 mt-1 block">Enter search keywords separated by commas for search engines and site search indexing.</span>
                </div>

                <div>
                    <label for="edit-prod-meta-desc" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Meta Description
                    </label>
                    <textarea id="edit-prod-meta-desc" rows="3" placeholder="Compelling 150-160 character snippet for Google search results..." class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all"></textarea>
                </div>
            </div>
        </div>

        <!-- Hidden submit trigger for external save button -->
        <button type="submit" id="btn-save-product" class="hidden"></button>
    </div>

    <!-- Sidebar Column (1 Column wide) -->
    <div class="lg:col-span-1 space-y-6 sm:space-y-8">
        
        <!-- 1. Publishing Status Card -->
        <div class="bg-white dark:bg-slate-850 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-globe-hemisphere-west mr-2.5 text-slate-400 text-lg"></i> Publishing Status
                </h3>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label for="edit-prod-status" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="edit-prod-status" required class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all cursor-pointer">
                        <option value="draft">Draft (Hidden)</option>
                        <option value="published">Published (Visible in Store)</option>
                        <option value="archived">Archived (Deactivated)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Organization Card -->
        <div class="bg-white dark:bg-slate-850 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-folder mr-2.5 text-slate-400 text-lg"></i> Catalog Organization
                </h3>
            </div>
            
            <div class="p-6 space-y-5">
                <div>
                    <label for="edit-prod-category" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select id="edit-prod-category" required class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all cursor-pointer">
                        <!-- Loaded dynamically -->
                    </select>
                </div>

                <div>
                    <label for="edit-prod-brand" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Brand / Collection
                    </label>
                    <select id="edit-prod-brand" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all cursor-pointer">
                        <!-- Loaded dynamically -->
                    </select>
                </div>

                <div>
                    <label for="edit-prod-occasion" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Festive Occasion
                    </label>
                    <select id="edit-prod-occasion" class="block w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm transition-all cursor-pointer">
                        <option value="">None / General Catalog</option>
                        <!-- Loaded dynamically -->
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. Curation Badges Card -->
        <div class="bg-white dark:bg-slate-850 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-star mr-2.5 text-slate-400 text-lg"></i> Curation & Homepage Badges
                </h3>
            </div>
            
            <div class="p-6 space-y-4">
                <label class="flex items-start cursor-pointer group p-3 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors">
                    <div class="flex items-center h-5">
                        <input type="checkbox" id="edit-prod-featured" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div class="ml-3 text-sm">
                        <span class="font-bold text-slate-900 dark:text-white group-hover:text-primary-600 transition-colors">Featured Product</span>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Show in Homepage Featured grid</p>
                    </div>
                </label>
                
                <label class="flex items-start cursor-pointer group p-3 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors">
                    <div class="flex items-center h-5">
                        <input type="checkbox" id="edit-prod-is-trending" class="h-4 w-4 text-amber-500 focus:ring-amber-500 border-slate-300 rounded dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div class="ml-3 text-sm">
                        <span class="font-bold text-slate-900 dark:text-white group-hover:text-amber-600 transition-colors flex items-center">
                            <i class="ph-fill ph-fire text-amber-500 mr-1.5 text-base"></i> Trending Selection
                        </span>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Show 'Hot Right Now' badge</p>
                    </div>
                </label>

                <label class="flex items-start cursor-pointer group p-3 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors">
                    <div class="flex items-center h-5">
                        <input type="checkbox" id="edit-prod-is-must-buy" class="h-4 w-4 text-emerald-500 focus:ring-emerald-500 border-slate-300 rounded dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div class="ml-3 text-sm">
                        <span class="font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 transition-colors flex items-center">
                            <i class="ph-fill ph-sparkle text-emerald-500 mr-1.5 text-base"></i> Must Buy Curation
                        </span>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Show 'Editor's Pick' badge</p>
                    </div>
                </label>
            </div>
        </div>

    </div>
</form>

<!-- Inline Metadata Editing Modal -->
<div id="metadata-modal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-md overflow-hidden animate-scaleIn">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/60 dark:bg-slate-950/60">
            <h4 class="text-sm font-bold text-slate-900 dark:text-white flex items-center">
                <i class="ph ph-pencil-simple mr-2 text-primary-600"></i> Edit Image SEO Properties
            </h4>
            <button type="button" onclick="Products.closeMetadataModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label for="meta-alt-text" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Alt Text (SEO Description)
                </label>
                <input type="text" id="meta-alt-text" maxlength="125" placeholder="Describe this photo for search engines..." class="block w-full px-3.5 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                <div class="flex justify-between items-center mt-1.5">
                    <span id="alt-validation-msg" class="text-[10px] text-red-500 hidden font-semibold">Please enter an alt description.</span>
                    <span id="alt-char-counter" class="text-[10px] text-slate-400 font-mono ml-auto">0/125</span>
                </div>
            </div>
            <div>
                <label for="meta-title" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Title Attribute (Tooltip)
                </label>
                <input type="text" id="meta-title" placeholder="Image Title Attribute..." class="block w-full px-3.5 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-950/60 flex justify-end gap-3">
            <button type="button" onclick="Products.closeMetadataModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition">Cancel</button>
            <button type="button" id="btn-save-metadata" class="px-5 py-2 bg-primary-600 text-white rounded-xl text-xs font-semibold hover:bg-primary-700 transition shadow-sm">Save Metadata</button>
        </div>
    </div>
</div>

<!-- Cropper Modal -->
<div id="cropper-modal" class="hidden fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-lg overflow-hidden animate-scaleIn">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/60 dark:bg-slate-950/60">
            <h4 class="text-sm font-bold text-slate-900 dark:text-white flex items-center">
                <i class="ph ph-crop mr-2 text-primary-600"></i> Crop Product Image
            </h4>
            <button type="button" onclick="Products.closeCropperModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>
        <div class="p-5 flex justify-center bg-slate-950/10 border-b border-slate-200 dark:border-slate-800">
            <div class="max-h-[350px] w-full flex items-center justify-center overflow-hidden">
                <img id="cropper-image" src="" alt="Source crop photo" class="max-w-full max-h-full block">
            </div>
        </div>
        <div class="p-3 bg-slate-50/50 dark:bg-slate-950/40 flex flex-wrap justify-center gap-2 border-b border-slate-200 dark:border-slate-800">
            <button type="button" onclick="window.cropper.setAspectRatio(1)" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-750 transition text-slate-800 dark:text-slate-200">1:1 Square</button>
            <button type="button" onclick="window.cropper.setAspectRatio(4/3)" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-750 transition text-slate-800 dark:text-slate-200">4:3 Aspect</button>
            <button type="button" onclick="window.cropper.setAspectRatio(16/9)" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-750 transition text-slate-800 dark:text-slate-200">16:9 Aspect</button>
            <button type="button" onclick="window.cropper.setAspectRatio(NaN)" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-750 transition text-slate-800 dark:text-slate-200">Free Form</button>
        </div>
        <div class="px-6 py-4 bg-slate-50/60 dark:bg-slate-950/60 flex justify-end gap-3">
            <button type="button" onclick="Products.closeCropperModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition">Cancel</button>
            <button type="button" id="btn-confirm-crop" class="px-5 py-2 bg-primary-600 text-white rounded-xl text-xs font-semibold hover:bg-primary-700 transition shadow-sm">Apply & Re-upload</button>
        </div>
    </div>
</div>

<!-- CDNs & Scripts for Image Management & Editor -->
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
