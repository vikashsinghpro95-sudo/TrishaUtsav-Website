<?php
/**
 * Application Routes Definition
 * Maps HTTP method and URI regular expression patterns to "ControllerName@actionName".
 */

return [
    'POST' => [
        '/^\/api\/auth\/register$/' => 'AuthController@register',
        '/^\/api\/auth\/login$/' => 'AuthController@login',
        '/^\/api\/auth\/google$/' => 'AuthController@googleAuth',
        '/^\/api\/auth\/verify-msg91-token$/' => 'AuthController@verifyMsg91Token',
        '/^\/api\/auth\/send-phone-otp$/' => 'AuthController@sendPhoneOtp',
        '/^\/api\/auth\/verify-phone-otp$/' => 'AuthController@verifyPhoneOtp',
        '/^\/api\/auth\/admin\/login$/' => 'AuthController@adminLogin',
        '/^\/api\/auth\/logout$/' => 'AuthController@logout',
        '/^\/api\/auth\/forgot-password$/' => 'AuthController@forgotPassword',
        '/^\/api\/auth\/reset-password$/' => 'AuthController@resetPassword',
        '/^\/api\/admin\/categories$/' => 'CategoryController@create',
        '/^\/api\/admin\/products$/' => 'ProductController@create',
        '/^\/api\/admin\/products\/(\d+)\/images$/' => 'ProductController@uploadImages',
        '/^\/api\/admin\/products\/(\d+)\/images\/bulk-delete$/' => 'ProductController@bulkDeleteImages',
        
        // Cart
        '/^\/api\/cart\/add$/' => 'CartController@add',
        '/^\/api\/cart\/apply-coupon$/' => 'CartController@applyCoupon',

        // Wishlist
        '/^\/api\/wishlist\/toggle$/' => 'WishlistController@toggle',
        '/^\/api\/wishlist\/move-to-cart$/' => 'WishlistController@moveToCart',
        
        // Addresses
        '/^\/api\/addresses$/' => 'AddressController@store',
        
        // Checkout
        '/^\/api\/checkout$/' => 'CheckoutController@process',
        '/^\/api\/buy-now$/' => 'CheckoutController@buyNow',
        
        // Payments
        '/^\/api\/payments\/initiate$/' => 'PaymentController@initiate',
        '/^\/api\/payments\/verify$/' => 'PaymentController@verify',
        '/^\/api\/payments\/failed$/' => 'PaymentController@paymentFailed',
        '/^\/api\/webhooks\/razorpay$/' => 'PaymentController@webhook',
        
        // Orders (customer)
        '/^\/api\/orders\/(\d+)\/cancel$/' => 'OrderController@cancel',
        '/^\/api\/orders\/(\d+)\/return$/' => 'OrderController@requestReturn',
        
        // Admin Orders
        '/^\/api\/admin\/orders\/(\d+)\/cancel$/' => 'AdminOrderController@cancel',
        '/^\/api\/admin\/orders\/(\d+)\/refund$/' => 'AdminOrderController@refund',
        '/^\/api\/admin\/orders\/(\d+)\/shipment$/' => 'AdminOrderController@addShipment',
        '/^\/api\/admin\/orders\/(\d+)\/tracking$/' => 'AdminOrderController@updateTracking',
        
        // Admin Coupons
        '/^\/api\/admin\/coupons$/' => 'CouponController@store',

        // Profile password change
        '/^\/api\/profile\/change-password$/' => 'AuthController@changePassword',

        // Admin CMS
        '/^\/api\/admin\/banners$/' => 'AdminBannerController@store',
        '/^\/api\/admin\/pages$/' => 'AdminPageController@store',

        // Admin Brands
        '/^\/api\/admin\/brands$/' => 'BrandController@store',

        // Admin Media Upload Helper
        '/^\/api\/admin\/media\/upload$/' => 'AdminSettingsController@upload',

        // Admin Reels & Occasions
        '/^\/api\/admin\/reels$/' => 'ReelController@create',
        '/^\/api\/admin\/occasions$/' => 'OccasionController@create',
        // Admin Settings
        '/^\/api\/admin\/settings$/' => 'SettingsController@update',
        // Newsletter
        '/^\/api\/newsletter\/subscribe$/' => 'NewsletterController@subscribe',
    ],
    'GET' => [
        '/^\/api\/generate-invoices$/' => 'OrderController@generatePastInvoices',
        
        '/^\/api\/auth\/me$/' => 'AuthController@me',
        '/^\/api\/auth\/admin\/me$/' => 'AuthController@adminMe',
        '/^\/api\/categories$/' => 'CategoryController@index',
        '/^\/api\/categories-products$/' => 'CategoryController@products',
        '/^\/api\/categories\/([a-zA-Z0-9\-_]+)$/' => 'CategoryController@show',
        '/^\/api\/products$/' => 'ProductController@index',
        '/^\/api\/products\/([a-zA-Z0-9\-_]+)$/' => 'ProductController@show',

        // Public Reels, Occasions & Homepage Sections
        '/^\/api\/reels$/' => 'ReelController@index',
        '/^\/api\/occasions$/' => 'OccasionController@index',
        '/^\/api\/occasions-products$/' => 'OccasionController@products',
        '/^\/api\/occasions\/([a-zA-Z0-9\-_]+)$/' => 'OccasionController@show',
        '/^\/api\/homepage\/sections$/' => 'AdminSettingsController@getHomepageSections',
        
        // Cart
        '/^\/api\/cart$/' => 'CartController@index',
        '/^\/api\/cart\/count$/' => 'CartController@count',
        '/^\/api\/checkout\/direct-summary$/' => 'CheckoutController@getDirectSummary',
        
        // Wishlist
        '/^\/api\/wishlist$/' => 'WishlistController@index',
        '/^\/api\/wishlist\/count$/' => 'WishlistController@count',
        
        // Addresses
        '/^\/api\/addresses$/' => 'AddressController@index',
        '/^\/api\/addresses\/(\d+)$/' => 'AddressController@show',
        
        // Brands
        '/^\/api\/brands$/' => 'BrandController@index',

        // Public CMS Pages
        '/^\/api\/pages\/([a-zA-Z0-9\-_]+)$/' => 'PageController@show',

        // Public Settings
        '/^\/api\/settings$/' => 'SettingsController@show',
        
        // Shipping & Delhivery
        '/^\/api\/shipping\/check-pincode$/' => 'ShippingController@checkPincode',

        // Orders (customer)
        '/^\/api\/orders$/' => 'OrderController@index',
        '/^\/api\/orders\/(\d+)$/' => 'OrderController@show',
        '/^\/api\/orders\/(\d+)\/tracking$/' => 'OrderController@getTracking',
        
        // Admin Orders
        '/^\/api\/admin\/orders$/' => 'AdminOrderController@index',
        '/^\/api\/admin\/orders\/(\d+)$/' => 'AdminOrderController@show',
        
        // Admin Coupons
        '/^\/api\/admin\/coupons$/' => 'CouponController@index',

        // Admin Dashboard
        '/^\/api\/admin\/dashboard$/' => 'AdminDashboardController@index',

        // Admin Products
        '/^\/api\/admin\/products$/' => 'ProductController@index',
        '/^\/api\/admin\/products\/(\d+)$/' => 'ProductController@showAdmin',

        // Admin Customers
        '/^\/api\/admin\/customers$/' => 'AdminCustomerController@index',
        '/^\/api\/admin\/customers\/(\d+)$/' => 'AdminCustomerController@show',

        // Admin Settings
        '/^\/api\/admin\/settings$/' => 'AdminSettingsController@show',

        // Admin Newsletter
        '/^\/api\/admin\/newsletter$/' => 'NewsletterController@adminIndex',

        // Admin Audit Logs
        '/^\/api\/admin\/audit-logs$/' => 'AdminAuditLogsController@index',

        // Admin CMS
        '/^\/api\/admin\/banners$/' => 'AdminBannerController@index',
        '/^\/api\/admin\/pages$/' => 'AdminPageController@index',

        // Admin Reels & Occasions
        '/^\/api\/admin\/reels$/' => 'ReelController@adminIndex',
        '/^\/api\/admin\/occasions$/' => 'OccasionController@adminIndex',
    ],
    'PUT' => [
        '/^\/api\/admin\/categories\/(\d+)$/' => 'CategoryController@update',
        '/^\/api\/admin\/products\/(\d+)$/' => 'ProductController@update',
        '/^\/api\/admin\/products\/(\d+)\/images$/' => 'ProductController@updateImages',
        '/^\/api\/admin\/products\/(\d+)\/images\/(\d+)$/' => 'ProductController@updateImageMetadata',
        
        // Cart
        '/^\/api\/cart\/update\/(\d+)$/' => 'CartController@update',
        
        // Addresses
        '/^\/api\/addresses\/(\d+)$/' => 'AddressController@update',
        
        // Profile
        '/^\/api\/profile$/' => 'AuthController@updateProfile',
        
        // Admin Coupons
        '/^\/api\/admin\/coupons\/(\d+)$/' => 'CouponController@update',
        '/^\/api\/admin\/orders\/(\d+)\/tracking$/' => 'AdminOrderController@updateTracking',

        // Admin Settings & Homepage Layout
        '/^\/api\/admin\/settings$/' => 'AdminSettingsController@update',
        '/^\/api\/admin\/homepage\/sections$/' => 'AdminSettingsController@updateHomepageSections',

        // Admin CMS
        '/^\/api\/admin\/banners\/(\d+)$/' => 'AdminBannerController@update',
        '/^\/api\/admin\/pages\/(\d+)$/' => 'AdminPageController@update',

        // Admin Brands
        '/^\/api\/admin\/brands\/(\d+)$/' => 'BrandController@update',

        // Admin Reels & Occasions
        '/^\/api\/admin\/reels\/(\d+)$/' => 'ReelController@update',
        '/^\/api\/admin\/occasions\/(\d+)$/' => 'OccasionController@update',
    ],
    'DELETE' => [
        '/^\/api\/admin\/categories\/(\d+)$/' => 'CategoryController@destroy',
        '/^\/api\/admin\/products\/(\d+)$/' => 'ProductController@delete',
        
        // Cart
        '/^\/api\/cart\/remove\/(\d+)$/' => 'CartController@remove',
        '/^\/api\/cart\/coupon$/' => 'CartController@removeCoupon',
        
        // Addresses
        '/^\/api\/addresses\/(\d+)$/' => 'AddressController@destroy',
        
        // Admin Coupons
        '/^\/api\/admin\/coupons\/(\d+)$/' => 'CouponController@destroy',

        // Admin CMS
        '/^\/api\/admin\/banners\/(\d+)$/' => 'AdminBannerController@destroy',
        '/^\/api\/admin\/pages\/(\d+)$/' => 'AdminPageController@destroy',

        // Admin Brands
        '/^\/api\/admin\/brands\/(\d+)$/' => 'BrandController@destroy',

        // Admin Reels & Occasions
        '/^\/api\/admin\/reels\/(\d+)$/' => 'ReelController@destroy',
        '/^\/api\/admin\/occasions\/(\d+)$/' => 'OccasionController@destroy',
        '/^\/api\/admin\/products\/(\d+)\/images\/(\d+)$/' => 'ProductController@deleteImage',
    ],
    'PATCH' => [
        '/^\/api\/admin\/products\/(\d+)\/stock$/' => 'ProductController@adjustStock',
        
        // Admin Orders
        '/^\/api\/admin\/orders\/(\d+)\/status$/' => 'AdminOrderController@updateStatus',
    ],
];
