<?php
// index.php (Main Router)
session_start(); // เปิด Session ที่นี่ที่เดียวพอ

// 1. รับค่าว่า user อยากไปหน้าไหน (ถ้าไม่มี ให้ไปหน้า search)
$page = $_GET['page'] ?? 'search';

// 2. กำหนดรายชื่อหน้าเว็บที่อนุญาต (Whitelist)
$allowed_pages = [
    'search'  => 'views/search.php',
    'result'  => 'views/result.php',
    'payment' => 'views/payment.php',
    'upload'  => 'views/upload.php'
];

// 3. ตรวจสอบและดึงไฟล์มาแสดง
if (array_key_exists($page, $allowed_pages)) {
    // โหลดไฟล์ View มาแปะตรงนี้
    include $allowed_pages[$page];
} else {
    // ถ้าหาหน้าไม่เจอ
    echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
    echo "<h1>404 - Page Not Found</h1>";
    echo "<a href='?page=search'>กลับหน้าหลัก</a>";
    echo "</div>";
    exit;
}
?>