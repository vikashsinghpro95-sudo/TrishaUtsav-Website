<?php
/**
 * Trisha Utsav - Bulk Festive Product Seeder
 * WARNING: This script will add 30 products to EVERY category and EVERY occasion.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

// Check if running from browser
if (php_sapi_name() !== 'cli') {
    set_time_limit(0);
    echo "<h1>Trisha Utsav - Live Database Mass Seeder</h1>";
    echo "<p>Executing...</p>";
} else {
    echo "Trisha Utsav - Live Database Mass Seeder\n";
    echo "Executing...\n";
}

try {
    $db = Database::getInstance();
    $db->beginTransaction();

    // Fetch all categories
    $stmt = $db->query("SELECT id, name FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all occasions
    $stmt = $db->query("SELECT id, name FROM occasions");
    $occasions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare inserts
    $stmtProd = $db->prepare("INSERT INTO products (category_id, occasion_id, name, slug, sku, short_description, description, price, mrp, tax_rate, stock_quantity, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', ?)");
    $stmtImg = $db->prepare("INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order) VALUES (?, ?, ?, 1, 0)");

    $premiumPrefixes = ["Royal", "Imperial", "Premium", "Signature", "Heritage", "Artisan", "Handcrafted", "Luxury", "Classic", "Authentic"];
    $productTypes = ["Kaju Katli", "Assorted Dry Fruits", "Ghee Motichoor Ladoo", "Pista Roll", "Festive Hamper", "Silver Plated Diya", "Gulab Jamun Tin", "Rasgulla Tin", "Chocolate Truffle Box", "Soan Papdi Special", "Baklava Assortment", "Besan Ladoo", "Mysore Pak", "Badam Halwa", "Festive Special Box"];
    $suffixes = ["Box", "Pack", "Collection", "Hamper", "Set", "Edition", "Special", "Delight"];

    // Premium Unsplash images of Indian sweets, festive items, celebrations
    $images = [
        "https://images.unsplash.com/photo-1605888967806-444427501ff2?q=80&w=800&auto=format&fit=crop", // Diyas
        "https://images.unsplash.com/photo-1545232979-fbfd42e000b9?q=80&w=800&auto=format&fit=crop", // Sweets
        "https://images.unsplash.com/photo-1610030469983-98e550d6193c?q=80&w=800&auto=format&fit=crop", // Indian Sweets
        "https://images.unsplash.com/photo-1513151233558-d860c5398176?q=80&w=800&auto=format&fit=crop", // Puja items
        "https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?q=80&w=800&auto=format&fit=crop", // Traditional thali
        "https://images.unsplash.com/photo-1542840410-3092f99611a3?q=80&w=800&auto=format&fit=crop", // Festive items
        "https://images.unsplash.com/photo-1549465220-1a8b9238cd48?q=80&w=800&auto=format&fit=crop", // Festive Boxes
        "https://images.unsplash.com/photo-1577083552431-6e5fd01aa342?q=80&w=800&auto=format&fit=crop", // Indian sweets
    ];

    $totalInserted = 0;

    function out($msg) {
        if (php_sapi_name() !== 'cli') {
            echo "<div>" . htmlspecialchars($msg) . "</div>";
            ob_flush();
            flush();
        } else {
            echo $msg . "\n";
        }
    }

    out("Found " . count($categories) . " Categories and " . count($occasions) . " Occasions.");

    // Loop Categories
    foreach ($categories as $cat) {
        out("Seeding 30 products for Category: " . $cat['name'] . "...");
        for ($i = 1; $i <= 30; $i++) {
            $prefix = $premiumPrefixes[array_rand($premiumPrefixes)];
            $type = $productTypes[array_rand($productTypes)];
            $suffix = $suffixes[array_rand($suffixes)];
            
            $name = "$prefix $type $suffix " . rand(100, 999);
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . uniqid();
            $sku = strtoupper(substr(preg_replace('/[^a-z]+/i', '', $cat['name']), 0, 3)) . '-' . uniqid() . $i;
            
            $price = rand(499, 4999);
            $mrp = $price + rand(100, 500);
            $tax = 18.00;
            $stock = rand(10, 100);
            $featured = (rand(1, 10) > 8) ? 1 : 0;
            
            $shortDesc = "Experience the rich heritage of Indian festivities with this $name. Pure ingredients, handcrafted perfection.";
            $desc = "<p>Indulge in our exquisite $name, meticulously crafted for the most joyous moments. Prepared using pure desi ghee and premium nuts, this exclusive selection from Trisha Utsav is perfect for gifting your loved ones.</p><ul><li>100% Vegetarian & Authentic</li><li>Zero Preservatives</li><li>Premium Festive Packaging</li></ul>";

            // Insert Product
            $stmtProd->execute([
                $cat['id'],
                null, // No occasion
                $name,
                $slug,
                $sku,
                $shortDesc,
                $desc,
                $price,
                $mrp,
                $tax,
                $stock,
                $featured
            ]);

            $prodId = $db->lastInsertId();

            // Insert Image
            $imgUrl = $images[array_rand($images)];
            $stmtImg->execute([$prodId, $imgUrl, $name]);
            
            $totalInserted++;
        }
    }

    // Loop Occasions
    foreach ($occasions as $occ) {
        out("Seeding 30 products for Occasion: " . $occ['name'] . "...");
        // Assign these to a random category so they aren't orphaned
        $randomCatId = $categories[array_rand($categories)]['id'];

        for ($i = 1; $i <= 30; $i++) {
            $prefix = $premiumPrefixes[array_rand($premiumPrefixes)];
            $type = $productTypes[array_rand($productTypes)];
            $suffix = $suffixes[array_rand($suffixes)];
            
            $name = "$prefix {$occ['name']} $type $suffix " . rand(100, 999);
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . uniqid();
            $sku = strtoupper(substr(preg_replace('/[^a-z]+/i', '', $occ['name']), 0, 3)) . '-O-' . uniqid() . $i;
            
            $price = rand(499, 4999);
            $mrp = $price + rand(100, 500);
            $tax = 18.00;
            $stock = rand(10, 100);
            $featured = (rand(1, 10) > 8) ? 1 : 0;
            
            $shortDesc = "Make your {$occ['name']} unforgettable with this $name. Curated to perfection.";
            $desc = "<p>Celebrate {$occ['name']} with our exclusive $name. Crafted with love and the finest ingredients to bring joy to your celebrations.</p>";

            // Insert Product
            $stmtProd->execute([
                $randomCatId,
                $occ['id'], 
                $name,
                $slug,
                $sku,
                $shortDesc,
                $desc,
                $price,
                $mrp,
                $tax,
                $stock,
                $featured
            ]);

            $prodId = $db->lastInsertId();

            // Insert Image
            $imgUrl = $images[array_rand($images)];
            $stmtImg->execute([$prodId, $imgUrl, $name]);
            
            $totalInserted++;
        }
    }

    $db->commit();
    out("🎉 Successfully seeded $totalInserted new products into the live store!");

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    out("❌ Error: " . $e->getMessage());
}
?>
