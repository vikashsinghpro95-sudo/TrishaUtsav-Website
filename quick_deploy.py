"""
Quick Deploy - Uploads specific changed files directly to server via FTP.
Navigates into public_html first, then uploads relative paths.
"""
import ftplib
import os

FTP_HOST = "145.79.212.119"
FTP_USER = "u445085246.trishautsav.in"
FTP_PASS = "Codeforthelife@99"
FTP_PORT = 21

LOCAL_ROOT = "/Users/macbookpro/Developement/FestiveTreat"

# Local file -> remote path relative to public_html
FILES = {
    ".htaccess":                            ".htaccess",
    "public/.htaccess":                     "public/.htaccess",
    "admin/assets/css/admin.css":           "admin/assets/css/admin.css",
    "admin/includes/admin-header.php":      "admin/includes/admin-header.php",
    "admin/includes/admin-footer.php":      "admin/includes/admin-footer.php",
    "admin/assets/js/api.js":               "admin/assets/js/api.js",
    "admin/assets/js/auth.js":              "admin/assets/js/auth.js",
    "admin/assets/js/products.js":          "admin/assets/js/products.js",
    "admin/assets/js/orders.js":            "admin/assets/js/orders.js",
    "admin/assets/js/customers.js":         "admin/assets/js/customers.js",
    "admin/assets/js/dashboard.js":         "admin/assets/js/dashboard.js",
    "admin/assets/js/settings.js":          "admin/assets/js/settings.js",
    "admin/categories.php":                 "admin/categories.php",
    "admin/brands.php":                     "admin/brands.php",
    "admin/order-detail.php":               "admin/order-detail.php",
    "admin/product-edit.php":               "admin/product-edit.php",
    "admin/products.php":                   "admin/products.php",
    "admin/login.php":                      "admin/login.php",
    "favicon.png":                          "favicon.png",
    "admin/settings.php":                   "admin/settings.php",
    "admin/homepage-sections.php":          "admin/homepage-sections.php",
    "admin/newsletter.php":                 "admin/newsletter.php",
    "admin/assets/js/newsletter.js":        "admin/assets/js/newsletter.js",
    "routes.php":                           "routes.php",
    "config/database.php":                  "config/database.php",
    "models/Newsletter.php":                "models/Newsletter.php",
    "models/Product.php":                   "models/Product.php",
    "controllers/NewsletterController.php": "controllers/NewsletterController.php",
    "controllers/OccasionController.php":   "controllers/OccasionController.php",
    "controllers/CategoryController.php":   "controllers/CategoryController.php",
    "controllers/ProductController.php":    "controllers/ProductController.php",
    "controllers/CheckoutController.php":   "controllers/CheckoutController.php",
    "controllers/SettingsController.php":   "controllers/SettingsController.php",
    "controllers/AdminSettingsController.php": "controllers/AdminSettingsController.php",
    "update_schema.php":                    "update_schema.php",
    "update_schema_newsletter.php":         "update_schema_newsletter.php",
    "update_copyright.php":                 "update_copyright.php",
    "deploy.php":                           "deploy.php",
    "public/includes/header.php":           "public/includes/header.php",
    "public/includes/footer.php":           "public/includes/footer.php",
    "public/index.php":                     "public/index.php",
    "public/shop.php":                      "public/shop.php",
    "public/assets/js/shop.js":             "public/assets/js/shop.js",
    "public/categories.php":                "public/categories.php",
    "public/assets/js/categories.js":       "public/assets/js/categories.js",
    "public/occasions.php":                 "public/occasions.php",
    "public/assets/js/occasions.js":        "public/assets/js/occasions.js",
    "public/about.php":                     "public/about.php",
    "public/faq.php":                       "public/faq.php",
    "public/contact.php":                   "public/contact.php",
    "public/checkout.php":                  "public/checkout.php",
    "public/order-success.php":             "public/order-success.php",
    "public/order-detail.php":              "public/order-detail.php",
    "public/update_reels.php":              "public/update_reels.php",
    "public/assets/js/cart.js":             "public/assets/js/cart.js",
    "public/assets/js/auth.js":             "public/assets/js/auth.js",
    "public/assets/js/checkout.js":         "public/assets/js/checkout.js",
    "public/assets/js/account.js":          "public/assets/js/account.js",
    "public/assets/js/product.js":          "public/assets/js/product.js",
    "public/assets/js/utils.js":            "public/assets/js/utils.js",
    "includes/Helper.php":                  "includes/Helper.php",
    "includes/Database.php":                "includes/Database.php",
    "includes/Auth.php":                    "includes/Auth.php",
    "includes/Validator.php":               "includes/Validator.php",
    "models/Address.php":                   "models/Address.php",
    "models/Cart.php":                      "models/Cart.php",
    "models/Order.php":                     "models/Order.php",
    "models/OrderItem.php":                 "models/OrderItem.php",
    "models/OrderStatusHistory.php":        "models/OrderStatusHistory.php",
    "controllers/PaymentController.php":    "controllers/PaymentController.php",
}

def ensure_remote_dir(ftp, remote_dir):
    """Recursively ensure directory path exists on remote."""
    parts = remote_dir.strip('/').split('/')
    for i in range(len(parts)):
        path = '/'.join(parts[:i+1])
        try:
            ftp.cwd(path)
            ftp.cwd('/')  # go back to root after each test
        except ftplib.error_perm:
            try:
                ftp.mkd(path)
                print(f"  Created dir: {path}")
            except Exception:
                pass

def upload_file(ftp, local_path, remote_path):
    remote_dir = '/'.join(remote_path.split('/')[:-1])
    remote_name = remote_path.split('/')[-1]
    # Navigate to directory
    try:
        ftp.cwd('/' + remote_dir)
    except ftplib.error_perm:
        ensure_remote_dir(ftp, remote_dir)
        ftp.cwd('/' + remote_dir)
    with open(local_path, 'rb') as f:
        ftp.storbinary(f"STOR {remote_name}", f)
    print(f"  ✅ {remote_path}")

def main():
    print(f"Connecting to {FTP_HOST}...")
    ftp = ftplib.FTP()
    ftp.connect(FTP_HOST, FTP_PORT, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.set_pasv(True)
    print("✅ Connected!\n")

    # Find root - try public_html
    try:
        ftp.cwd('public_html')
        root = '/public_html'
        print("📂 Working inside: /public_html\n")
    except ftplib.error_perm:
        root = '/'
        print("📂 Working in root /\n")

    for local_rel, remote_rel in FILES.items():
        local_abs = os.path.join(LOCAL_ROOT, local_rel)
        remote_path = root.rstrip('/') + '/' + remote_rel
        print(f"Uploading {local_rel}...")
        try:
            remote_dir = '/'.join(remote_path.split('/')[:-1])
            remote_name = remote_path.split('/')[-1]
            ftp.cwd(remote_dir)
            with open(local_abs, 'rb') as f:
                ftp.storbinary(f"STOR {remote_name}", f)
            print(f"  ✅ Done: {remote_path}")
        except Exception as e:
            print(f"  ❌ Error: {e}")

    ftp.quit()
    print("\n🎉 Done! All files updated on server instantly.")

if __name__ == "__main__":
    main()
