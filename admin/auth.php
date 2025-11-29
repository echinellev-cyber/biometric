<?php
session_start();

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /biometric/login/login.php');
        exit();
    }
}

/**
 * Redirect if not super admin
 */
function requireSuperAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'super_admin') {
        header('Location: /biometric/admin/dashboard.php');
        exit();
    }
}

/**
 * Check if current user has specific role
 */
function hasRole($requiredRole) {
    if (!isLoggedIn()) return false;
    return $_SESSION['role'] === $requiredRole;
}

/**
 * Secure redirect function
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * CSRF token generation/verification
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>