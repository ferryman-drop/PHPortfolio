<?php
/**
 * Security utilities for portfolio management
 */

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input validation
function validateTokenInput($tokenId, $amount, $price, $category = '', $txLink = '') {
    $errors = [];
    
    // Token ID validation
    if (empty($tokenId) || strlen($tokenId) > 50) {
        $errors[] = 'Невірний ID токена';
    }
    
    // Amount validation
    if (!is_numeric($amount) || $amount <= 0 || $amount > 999999999) {
        $errors[] = 'Невірна кількість токенів';
    }
    
    // Price validation
    if (!is_numeric($price) || $price <= 0 || $price > 999999999) {
        $errors[] = 'Невірна ціна покупки';
    }
    
    // Category validation
    $validCategories = ['BTC', 'ETH & Blue Chips', 'Stablecoins', 'DeFi & Altcoins'];
    if (!empty($category) && !in_array($category, $validCategories)) {
        $errors[] = 'Невірна категорія';
    }
    
    // Transaction link validation
    if (!empty($txLink) && !filter_var($txLink, FILTER_VALIDATE_URL)) {
        $errors[] = 'Невірне посилання на транзакцію';
    }
    
    return $errors;
}

// XSS Protection
function escapeHTML($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Rate limiting
function checkRateLimit($action, $maxAttempts = 10, $timeWindow = 300) {
    $key = "rate_limit_{$action}_" . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + $timeWindow];
    }
    
    if (time() > $_SESSION[$key]['reset_time']) {
        $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + $timeWindow];
    }
    
    if ($_SESSION[$key]['count'] >= $maxAttempts) {
        return false;
    }
    
    $_SESSION[$key]['count']++;
    return true;
}

// Session security
function secureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
}

// Sanitize portfolio data
function sanitizePortfolioData($portfolio) {
    $sanitized = [];
    foreach ($portfolio as $item) {
        $sanitized[] = [
            'token' => [
                'id' => escapeHTML($item['token']['id'] ?? ''),
                'symbol' => escapeHTML($item['token']['symbol'] ?? ''),
                'name' => escapeHTML($item['token']['name'] ?? ''),
                'current_price' => (float)($item['token']['current_price'] ?? 0),
                'category' => escapeHTML($item['token']['category'] ?? ''),
            ],
            'amount' => (float)($item['amount'] ?? 0),
            'buy_price' => (float)($item['buy_price'] ?? 0),
            'value' => (float)($item['value'] ?? 0),
            'roi' => (float)($item['roi'] ?? 0),
            'tx_link' => escapeHTML($item['tx_link'] ?? ''),
        ];
    }
    return $sanitized;
} 