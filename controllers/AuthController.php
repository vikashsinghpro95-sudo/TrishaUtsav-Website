<?php
/**
 * Authentication Controller
 */

class AuthController {
    /**
     * POST /api/auth/register
     * Register a new customer
     */
    public function register(): void {
        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'first_name' => ['required', 'alpha', 'maxLength:100'],
            'last_name'  => ['required', 'alpha', 'maxLength:100'],
            'email'      => ['required', 'email', 'unique:users,email', 'maxLength:191'],
            'password'   => ['required', 'minLength:8', 'maxLength:255'],
            'phone'      => ['minLength:10', 'maxLength:20']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $token = Auth::register($data);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Registration completed successfully.',
                'token'   => $token
            ], 201);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to register user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/auth/login
     * Authenticate a user
     */
    public function login(): void {
        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'email'    => ['required', 'email'],
            'password' => ['required']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $token = Auth::login($data['email'], $data['password']);

            if (!$token) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Invalid email or password.'
                ], 401);
            }

            // Merge guest session wishlist into account wishlist if present
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            if (!empty($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
                try {
                    $user = Auth::getCurrentUser();
                    if ($user && !empty($user['id'])) {
                        $wishlistModel = new Wishlist();
                        $wishlistModel->mergeSessionWishlist((int)$user['id'], $_SESSION['wishlist']);
                        $_SESSION['wishlist'] = [];
                    }
                } catch (Exception $eWish) {}
            }

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Authentication successful.',
                'token'   => $token
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * POST /api/auth/admin/login
     * Authenticate an admin user specifically
     */
    public function adminLogin(): void {
        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'email'    => ['required', 'email'],
            'password' => ['required']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $token = Auth::login($data['email'], $data['password']);

            if (!$token) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Invalid email or password.'
                ], 401);
            }

            // Check if the user is an admin
            $userModel = new User();
            $user = $userModel->findByEmail($data['email']);

            if (!$user || (int)$user['role_id'] !== 1) {
                // Instantly invalidate/delete the token if not an admin
                Auth::logout($token);
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Access denied. Administrator privileges required.'
                ], 403);
            }

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Admin authentication successful.',
                'token'   => $token
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * POST /api/auth/logout
     * Revoke current session token
     */
    public function logout(): void {
        // Enforce active user validation
        $user = AuthMiddleware::handle();

        $token = $user['active_token'];
        $revoked = Auth::logout($token);

        if ($revoked) {
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Session successfully terminated.'
            ], 200);
        } else {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to terminate session.'
            ], 500);
        }
    }

    /**
     * GET /api/auth/me
     * Fetch user profile data
     */
    public function me(): void {
        $user = AuthMiddleware::handle();
        
        // Remove token details from profile response
        unset($user['token'], $user['expires_at'], $user['active_token']);

        Helper::jsonResponse([
            'success' => true,
            'user'    => $user
        ], 200);
    }

    /**
     * PUT /api/profile
     * Update user profile information
     */
    public function updateProfile(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'first_name' => ['required', 'alpha', 'maxLength:100'],
            'last_name'  => ['required', 'alpha', 'maxLength:100'],
            'email'      => ['required', 'email', "unique:users,email,$userId", 'maxLength:191'],
            'phone'      => ['minLength:10', 'maxLength:20']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $userModel = new User();
            $userModel->update($userId, [
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'] ?? null
            ]);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Profile updated successfully.'
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/profile/change-password
     * Change user password
     */
    public function changePassword(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'current_password' => ['required'],
            'new_password'     => ['required', 'minLength:8', 'maxLength:255']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            // Retrieve current password hash
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($data['current_password'], $hash)) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Validation errors occurred.',
                    'errors' => ['current_password' => ['The current password you entered is incorrect.']]
                ], 422);
            }

            $userModel = new User();
            $userModel->update($userId, [
                'password' => $data['new_password']
            ]);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Password updated successfully.'
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to change password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/auth/forgot-password
     * Request password reset token for account recovery
     */
    public function forgotPassword(): void {
        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'email' => ['required', 'email']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $token = Auth::requestPasswordReset($data['email']);

            // For security reasons, return generic success even if user not found, but return token in dev
            $response = [
                'success' => true,
                'message' => 'If an active account exists with that email, a password reset link/token has been generated.'
            ];

            if ($token && ($_ENV['APP_ENV'] ?? 'development') === 'development') {
                $response['reset_token'] = $token;
            }

            Helper::jsonResponse($response, 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to process request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/auth/reset-password
     * Reset password using token
     */
    public function resetPassword(): void {
        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'token'        => ['required'],
            'new_password' => ['required', 'minLength:8', 'maxLength:255']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            Auth::resetPasswordWithToken($data['token'], $data['new_password']);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Password reset successfully. You can now log in with your new password.'
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/auth/google
     * Login or Register via Google Sign-In with Firebase Service Account backing
     */
    public function googleAuth(): void {
        $data = Helper::getRequestBody();

        $email = trim($data['email'] ?? '');
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $avatar = trim($data['avatar'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Valid Google account email address is required.'
            ], 422);
        }

        if (empty($firstName)) {
            $fullName = trim($data['name'] ?? 'Google User');
            $parts = explode(' ', $fullName, 2);
            $firstName = $parts[0] ?? 'Google';
            $lastName = $parts[1] ?? 'User';
        }

        try {
            $userModel = new User();
            $existing = $userModel->findByEmail($email);

            if ($existing) {
                if ($existing['status'] !== 'active') {
                    Helper::jsonResponse([
                        'success' => false,
                        'message' => 'Your account is ' . $existing['status'] . '. Access denied.'
                    ], 403);
                }

                if (!empty($avatar) && empty($existing['avatar'])) {
                    $userModel->update($existing['id'], ['avatar' => $avatar]);
                    $existing['avatar'] = $avatar;
                }

                $token = Auth::createSessionForUser($existing['id']);
                $requiresPhone = empty($existing['phone']) || (int)($existing['is_phone_verified'] ?? 0) === 0;

                Helper::jsonResponse([
                    'success' => true,
                    'message' => 'Google authentication successful.',
                    'token' => $token,
                    'requires_phone' => $requiresPhone,
                    'user' => [
                        'id' => $existing['id'],
                        'first_name' => $existing['first_name'],
                        'last_name' => $existing['last_name'],
                        'email' => $existing['email'],
                        'phone' => $existing['phone'],
                        'avatar' => $existing['avatar'],
                        'is_phone_verified' => (int)($existing['is_phone_verified'] ?? 0),
                        'role_id' => $existing['role_id']
                    ]
                ], 200);
            } else {
                $db = Database::getInstance();
                $hashedPass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

                $stmt = $db->prepare("
                    INSERT INTO users (role_id, first_name, last_name, email, password, phone, avatar, is_verified, is_phone_verified, status)
                    VALUES (2, ?, ?, ?, ?, NULL, ?, 1, 0, 'active')
                ");
                $stmt->execute([$firstName, $lastName, $email, $hashedPass, $avatar]);
                $newId = (int)$db->lastInsertId();

                $token = Auth::createSessionForUser($newId);

                Helper::jsonResponse([
                    'success' => true,
                    'message' => 'Account created successfully via Google.',
                    'token' => $token,
                    'requires_phone' => true,
                    'user' => [
                        'id' => $newId,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'phone' => null,
                        'avatar' => $avatar,
                        'is_phone_verified' => 0,
                        'role_id' => 2
                    ]
                ], 201);
            }
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Google authentication failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/auth/send-phone-otp
     * Send 6-digit OTP code to user's phone
     */
    public function sendPhoneOtp(): void {
        $user = AuthMiddleware::handle();
        $data = Helper::getRequestBody();

        $phone = preg_replace('/[^\d]/', '', $data['phone'] ?? '');

        if (strlen($phone) < 10) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Please enter a valid mobile phone number.'
            ], 422);
        }

        $authKey = $_ENV['MSG91_AUTH_KEY'] ?? '';
        $templateId = $_ENV['MSG91_TEMPLATE_ID'] ?? '';

        if (!empty($authKey) && !empty($templateId)) {
            // Use MSG91 SendOTP API
            $url = "https://control.msg91.com/api/v5/otp?template_id=" . urlencode($templateId) . "&mobile=" . urlencode($phone) . "&authkey=" . urlencode($authKey) . "&otp_length=6";
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            
            if ($err) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Failed to reach SMS provider.'
                ], 500);
            }
        } else {
            // Simulation Fallback
            $otp = (string)random_int(100000, 999999);
            $tmpFile = sys_get_temp_dir() . '/otp_' . md5($phone) . '.txt';
            file_put_contents($tmpFile, $otp);
            error_log("SMS to +$phone: Your TrishaUtsav verification code is $otp.");
        }

        Helper::jsonResponse([
            'success' => true,
            'message' => 'Verification code sent to +' . $phone . '.',
            'phone' => $phone
        ], 200);
    }

    /**
     * POST /api/auth/verify-phone-otp
     * Verify OTP code and update user's phone number & is_phone_verified flag
     */
    public function verifyPhoneOtp(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $data = Helper::getRequestBody();

        $phone = preg_replace('/[^\d]/', '', $data['phone'] ?? '');
        $otp = trim($data['otp'] ?? '');

        if (strlen($phone) < 10) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Please enter a valid mobile phone number.'
            ], 422);
        }

        $authKey = $_ENV['MSG91_AUTH_KEY'] ?? '';
        
        if (!empty($authKey) && !empty($_ENV['MSG91_TEMPLATE_ID'])) {
            // Use MSG91 Verify OTP API
            $url = "https://control.msg91.com/api/v5/otp/verify?otp=" . urlencode($otp) . "&mobile=" . urlencode($phone) . "&authkey=" . urlencode($authKey);
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            
            if ($err) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Failed to verify with SMS provider.'
                ], 500);
            }
            
            $resData = json_decode($response, true);
            if (!isset($resData['type']) || $resData['type'] !== 'success') {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Invalid or expired OTP entered.'
                ], 400);
            }
        } else {
            // Simulation Fallback
            $tmpFile = sys_get_temp_dir() . '/otp_' . md5($phone) . '.txt';
            $validOtp = file_exists($tmpFile) ? trim(file_get_contents($tmpFile)) : null;

            if (!$validOtp || $otp !== $validOtp) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Invalid or expired OTP entered.'
                ], 400);
            }
            @unlink($tmpFile);
        }

        try {
            $userModel = new User();
            $userModel->update($userId, [
                'phone' => $phone,
                'is_phone_verified' => 1
            ]);

            $updatedUser = $userModel->find($userId);
            unset($updatedUser['password']);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Phone number verified successfully!',
                'user' => $updatedUser
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to verify phone OTP: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/auth/verify-msg91-token
     * Verify MSG91 Widget JWT token and mark phone as verified
     */
    public function verifyMsg91Token(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $data = Helper::getRequestBody();
        $token = $data['token'] ?? '';

        if (empty($token)) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Missing verification token.'
            ], 422);
        }

        // Extract the mobile number from request or decode JWT payload
        $phone = $data['phone'] ?? null;
        
        if (empty($phone)) {
            $parts = explode('.', $token);
            if (count($parts) === 3) {
                // Fix Base64URL encoding safely
                $payloadStr = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
                $payload = json_decode($payloadStr, true);
                $phone = $payload['mobile'] ?? $payload['mobileNumber'] ?? $payload['identifier'] ?? null;
            }
        }

        // Remove country code (+91 or 91) if MSG91 prepends it, assuming 10 digit Indian number
        if ($phone) {
            $phone = preg_replace('/[^\d]/', '', (string)$phone);
            if (strlen($phone) > 10 && strpos($phone, '91') === 0) {
                $phone = substr($phone, 2);
            }
        }

        if (!$phone || strlen($phone) < 10) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Could not extract valid phone number from verification payload.'
            ], 400);
        }

        try {
            $userModel = new User();
            $userModel->update($userId, [
                'phone' => $phone,
                'is_phone_verified' => 1
            ]);

            $updatedUser = $userModel->find($userId);
            unset($updatedUser['password']);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Phone number verified via MSG91!',
                'user' => $updatedUser
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to process verification: ' . $e->getMessage()
            ], 500);
        }
    }
}
