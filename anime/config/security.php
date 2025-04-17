<?php
/**
 * Anime Universe Security Helper Functions
 * 
 * This file contains security-related functions for input validation,
 * CSRF protection, and other security utilities.
 */

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * @param string $data The input data to sanitize
 * @param bool $strip_tags Whether to strip HTML tags (default: true)
 * @return string Sanitized data
 */
function sanitizeInput($data, $strip_tags = true) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    // Trim whitespace
    $data = trim($data);
    
    // Convert special characters to HTML entities
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Optionally strip HTML tags
    if ($strip_tags) {
        $data = strip_tags($data);
    }
    
    return $data;
}

/**
 * Validate an email address
 * 
 * @param string $email The email address to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate a CSRF token and store it in session
 * 
 * @return string The generated CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        if (function_exists('random_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            // Fallback (less secure)
            $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
        }
        
        // Set token expiration (1 hour)
        $_SESSION['csrf_token_expiry'] = time() + 3600;
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify a CSRF token
 * 
 * @param string $token The token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        error_log('CSRF verification failed: No session token');
        return false;
    }
    
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        error_log('CSRF verification failed: Token mismatch');
        error_log('Session token: ' . $_SESSION['csrf_token']);
        error_log('Submitted token: ' . $token);
        return false;
    }
    
    return true;
}

/**
 * Secure password hashing
 * 
 * @param string $password The password to hash
 * @return string The hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify a password against a hash
 * 
 * @param string $password The password to verify
 * @param string $hash The hash to compare against
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate a secure random string
 * 
 * @param int $length Length of the string to generate
 * @return string The generated random string
 */
function generateRandomString($length = 32) {
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length / 2));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes($length / 2));
    }
    
    // Fallback (less secure)
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $result;
}

/**
 * Secure session start with additional headers
 */
function secureSessionStart() {
    // Prevent session fixation
    ini_set('session.use_strict_mode', 1);
    
    // Use cookies only for session ID
    ini_set('session.use_only_cookies', 1);
    
    // Make the cookie HTTP-only and secure if using HTTPS
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams['lifetime'],
        'path' => $cookieParams['path'],
        'domain' => $cookieParams['domain'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    // Start the session
    session_start();
    
    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Set security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
    // Content Security Policy (adjust as needed for your site)
    $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data:;";
    header("Content-Security-Policy: $csp");
}

/**
 * Prevent XSS in output
 * 
 * @param string $data The data to output
 * @return string Safe output
 */

    function safeOutput($data) {
        if ($data === null) {
            return '';
        }
        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

/**
 * Validate and sanitize file uploads
 * 
 * @param array $file The $_FILES array element
 * @param array $allowedTypes Allowed MIME types
 * @param int $maxSize Maximum file size in bytes
 * @return array|false Returns sanitized file info or false on failure
 */
function validateUploadedFile($file, $allowedTypes = [], $maxSize = 2097152) { // 2MB default
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    // Verify MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    
    if (!empty($allowedTypes) && !in_array($mime, $allowedTypes)) {
        return false;
    }
    
    // Generate a secure filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = generateRandomString(16) . '.' . $extension;
    
    return [
        'tmp_name' => $file['tmp_name'],
        'name' => $filename,
        'type' => $mime,
        'size' => $file['size']
    ];
}

/**
 * Redirect with sanitized URL
 * 
 * @param string $url The URL to redirect to
 * @param int $statusCode HTTP status code (default: 302)
 */
function safeRedirect($url, $statusCode = 302) {
    // Sanitize URL
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    // Only allow relative URLs or same domain absolute URLs
    $host = parse_url($url, PHP_URL_HOST);
    if ($host && $host !== parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST)) {
        $url = '/';
    }
    
    header("Location: $url", true, $statusCode);
    exit;
}

/**
 * Log security events
 * 
 * @param string $event The event description
 * @param array $data Additional data to log
 */
function logSecurityEvent($event, $data = []) {
    $logFile = __DIR__ . '/../logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logData = [
        'timestamp' => $timestamp,
        'ip' => $ip,
        'user_agent' => $userAgent,
        'event' => $event,
        'data' => $data
    ];
    
    // Ensure directory exists
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    // Append to log file
    file_put_contents(
        $logFile,
        json_encode($logData) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

/**
 * Check if request is AJAX
 * 
 * @return bool True if AJAX request, false otherwise
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Rate limiting function
 * 
 * @param string $key Identifier for this rate limit (e.g., 'login_attempts')
 * @param int $limit Maximum number of attempts
 * @param int $window Time window in seconds
 * @return bool True if under limit, false if rate limited
 */
function checkRateLimit($key, $limit = 5, $window = 300) {
    $cacheKey = "rate_limit_{$key}_" . ($_SERVER['REMOTE_ADDR'] ?? '');
    
    if (!isset($_SESSION[$cacheKey])) {
        $_SESSION[$cacheKey] = [
            'count' => 1,
            'timestamp' => time()
        ];
        return true;
    }
    
    $data = $_SESSION[$cacheKey];
    
    // Reset if window has passed
    if (time() - $data['timestamp'] > $window) {
        $_SESSION[$cacheKey] = [
            'count' => 1,
            'timestamp' => time()
        ];
        return true;
    }
    
    // Check if under limit
    if ($data['count'] < $limit) {
        $_SESSION[$cacheKey]['count']++;
        return true;
    }
    
    return false;
}