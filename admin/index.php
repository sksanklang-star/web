<?php
// admin/index.php
session_start();

// --- 1. CSRF Protection: สร้าง Token ถ้ายังไม่มี ---
if (empty($_SESSION['csrf_admin_token'])) {
    $_SESSION['csrf_admin_token'] = bin2hex(random_bytes(32));
}
// -----------------------------------------------

// Security Gatekeeper
define('ACCESS_ALLOWED', true); 

$page = $_GET['page'] ?? 'dashboard';

// Security Check: Login
if (!isset($_SESSION['admin_logged_in']) && $page !== 'login') {
    header("Location: ?page=login");
    exit;
}

$allowed_pages = [
    'login'     => 'views/login.php',
    'dashboard' => 'views/dashboard.php',
    'detail'    => 'views/detail.php'
];

if (array_key_exists($page, $allowed_pages)) {
    include $allowed_pages[$page];
} else {
    echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>";
    echo "<h1>404 - Page Not Found</h1>";
    echo "<a href='?page=dashboard'>กลับหน้าหลัก</a>";
    echo "</div>";
}
?>