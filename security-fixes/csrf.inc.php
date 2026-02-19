<?php
/**
 * CSRF Protection Functions
 * 
 * CRITICAL-005: Cross-Site Request Forgery Protection
 * 
 * Usage:
 * 1. Call csrf_token_field() in every form to generate the token input
 * 2. Call csrf_validate() at the start of every POST request handler
 * 3. Call csrf_token() to get the token for AJAX requests
 */

// Start session if not already started
if (php_sapi_name() != "cli" && session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a cryptographically secure CSRF token
 * 
 * @return string The CSRF token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate the HTML input field for CSRF token
 * 
 * @return string HTML input element
 */
function csrf_token_field() {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate the CSRF token from POST request
 * 
 * @param int $maxAge Maximum age of token in seconds (default: 3600 = 1 hour)
 * @return bool True if valid, false otherwise
 */
function csrf_validate($maxAge = 3600) {
    // Only validate on POST/PUT/DELETE requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && 
        $_SERVER['REQUEST_METHOD'] !== 'PUT' && 
        $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        return true;
    }
    
    // Check if token exists in session and request
    if (empty($_SESSION['csrf_token']) || empty($_POST['csrf_token'])) {
        error_log("CSRF validation failed: Missing token");
        return false;
    }
    
    // Validate token match using timing-safe comparison
    $sessionToken = $_SESSION['csrf_token'];
    $postToken = $_POST['csrf_token'];
    
    if (!hash_equals($sessionToken, $postToken)) {
        error_log("CSRF validation failed: Token mismatch");
        return false;
    }
    
    // Check token age
    if (!empty($_SESSION['csrf_token_time'])) {
        $age = time() - $_SESSION['csrf_token_time'];
        if ($age > $maxAge) {
            error_log("CSRF validation failed: Token expired");
            // Regenerate token
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
    }
    
    return true;
}

/**
 * Regenerate CSRF token (call after successful login/logout)
 */
function csrf_regenerate() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_time'] = time();
}

/**
 * Get CSRF token for AJAX requests (JSON response)
 * 
 * @return array Array with token and field name
 */
function csrf_ajax_token() {
    return array(
        'token' => csrf_token(),
        'field_name' => 'csrf_token'
    );
}
?>
