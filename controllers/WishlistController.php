<?php
/**
 * Wishlist Controller
 * Manages user & guest wishlist items, toggles, cart transfers, and counts
 */

class WishlistController {
    private Wishlist $wishlistModel;
    private Product $productModel;
    private Cart $cartModel;

    public function __construct() {
        $this->wishlistModel = new Wishlist();
        $this->productModel = new Product();
        $this->cartModel = new Cart();
    }

    /**
     * Get current user or null without throwing 401 exception
     *
     * @return array|null
     */
    private function getUserOrNull(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $user = Auth::getCurrentUser();
        if ($user) return $user;

        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
            $token = $matches[1];
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    SELECT u.* FROM user_sessions us
                    INNER JOIN users u ON us.user_id = u.id
                    WHERE us.token = ? AND us.expires_at > NOW() AND u.status = 'active'
                    LIMIT 1
                ");
                $stmt->execute([$token]);
                $user = $stmt->fetch() ?: null;
                if ($user) {
                    unset($user['password']);
                    Auth::setCurrentUser($user);
                    return $user;
                }
            } catch (Throwable $e) {}
        }

        return null;
    }

    /**
     * POST /api/wishlist/toggle
     * Add or remove product from Wishlist
     */
    public function toggle(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $data = Helper::getRequestBody();
        $productId = (int)($data['product_id'] ?? 0);

        if ($productId <= 0) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Valid product_id is required.'
            ], 422);
        }

        $product = $this->productModel->find($productId);
        if (!$product) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        $user = $this->getUserOrNull();

        if ($user && !empty($user['id'])) {
            try {
                // Logged-in user wishlist toggle
                $result = $this->wishlistModel->toggleItem((int)$user['id'], $productId);
                Helper::jsonResponse([
                    'success' => true,
                    'action'  => $result['action'],
                    'count'   => $result['count']
                ], 200);
                return;
            } catch (Throwable $e) {
                // Fallback to guest session wishlist if DB query fails
            }
        }

        // Guest session wishlist toggle (or DB fallback)
        if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }

        $index = array_search($productId, $_SESSION['wishlist']);
        if ($index !== false) {
            unset($_SESSION['wishlist'][$index]);
            $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
            $action = 'removed';
        } else {
            $_SESSION['wishlist'][] = $productId;
            $action = 'added';
        }

        $count = count($_SESSION['wishlist']);

        Helper::jsonResponse([
            'success' => true,
            'action'  => $action,
            'count'   => $count
        ], 200);
    }

    /**
     * GET /api/wishlist
     * Retrieve wishlist product list for user or guest
     */
    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $user = $this->getUserOrNull();

        try {
            if ($user && !empty($user['id'])) {
                try {
                    $items = $this->wishlistModel->getItems((int)$user['id']);
                } catch (Throwable $eDb) {
                    $items = [];
                }
            } else {
                $items = [];
            }

            // Fallback for session items if DB items empty or for guest
            if (empty($items)) {
                $sessionIds = $_SESSION['wishlist'] ?? [];
                foreach ($sessionIds as $pId) {
                    $prod = $this->productModel->find((int)$pId);
                    if ($prod && $prod['status'] === 'published') {
                        $prod['is_in_wishlist'] = true;
                        
                        // Fetch images
                        try {
                            $db = Database::getInstance();
                            $stmtImg = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
                            $stmtImg->execute([$prod['id']]);
                            $images = $stmtImg->fetchAll();
                            $prod['images'] = $images;

                            $primary = array_filter($images, fn($img) => (int)$img['is_primary'] === 1);
                            if (!empty($primary)) {
                                $first = reset($primary);
                                $prod['primary_image'] = $first['image_url'];
                            } else if (!empty($images)) {
                                $prod['primary_image'] = $images[0]['image_url'];
                            } else {
                                $prod['primary_image'] = null;
                            }
                        } catch (Throwable $eImg) {
                            $prod['images'] = [];
                            $prod['primary_image'] = null;
                        }

                        $items[] = $prod;
                    }
                }
            }

            Helper::jsonResponse([
                'success' => true,
                'data' => $items,
                'count' => count($items)
            ], 200);
        } catch (Throwable $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/wishlist/move-to-cart
     * Remove item from wishlist and add to Cart
     */
    public function moveToCart(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $data = Helper::getRequestBody();
        $productId = (int)($data['product_id'] ?? 0);

        if ($productId <= 0) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Valid product_id is required.'
            ], 422);
        }

        $user = $this->getUserOrNull();

        // 1. Remove from Wishlist
        if ($user && !empty($user['id'])) {
            try {
                $this->wishlistModel->removeItem((int)$user['id'], $productId);
            } catch (Throwable $eRm) {}
        }
        
        if (isset($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
            $index = array_search($productId, $_SESSION['wishlist']);
            if ($index !== false) {
                unset($_SESSION['wishlist'][$index]);
                $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
            }
        }

        // 2. Add to Cart using existing Cart system
        try {
            if ($user && !empty($user['id'])) {
                $cart = $this->cartModel->getOrCreateCart((int)$user['id']);
                $cartItemModel = new CartItem();
                $cartItemModel->addOrUpdate((int)$cart['id'], $productId, 1, null, null);
            } else {
                // Guest cart
                if (!isset($_SESSION['guest_cart']) || !is_array($_SESSION['guest_cart'])) {
                    $_SESSION['guest_cart'] = [];
                }
                $prod = $this->productModel->find($productId);
                if ($prod) {
                    $found = false;
                    foreach ($_SESSION['guest_cart'] as &$cItem) {
                        if ((int)$cItem['product_id'] === $productId) {
                            $cItem['quantity'] += 1;
                            $found = true;
                            break;
                        }
                    }
                    unset($cItem);
                    if (!$found) {
                        $_SESSION['guest_cart'][] = [
                            'id' => time() + rand(100, 999),
                            'product_id' => $productId,
                            'quantity' => 1,
                            'price' => (float)$prod['price']
                        ];
                    }
                }
            }

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Moved to cart successfully.'
            ], 200);
        } catch (Throwable $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to move item to cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/wishlist/count
     * Get total count of items in wishlist
     */
    public function count(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        try {
            $user = $this->getUserOrNull();

            if ($user && !empty($user['id'])) {
                try {
                    $count = $this->wishlistModel->getItemCount((int)$user['id']);
                } catch (Throwable $eCount) {
                    $count = count($_SESSION['wishlist'] ?? []);
                }
            } else {
                $count = count($_SESSION['wishlist'] ?? []);
            }

            Helper::jsonResponse([
                'success' => true,
                'count'   => $count
            ], 200);
        } catch (Throwable $e) {
            Helper::jsonResponse([
                'success' => true,
                'count'   => count($_SESSION['wishlist'] ?? [])
            ], 200);
        }
    }
}
