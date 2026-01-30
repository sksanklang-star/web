<?php
// admin/components/sidebar.php

// Security Check: ป้องกันการเรียกไฟล์นี้ตรงๆ
if (!defined('ACCESS_ALLOWED')) {
    die("Direct access not permitted.");
}

// Logic: เช็คว่าปัจจุบันอยู่หน้าไหน เพื่อ Highlight เมนู
$currentPage = $_GET['page'] ?? 'dashboard';
?>

<div class="sidebar d-flex flex-column" id="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-buildings"></i> <span>เทศบาล Admin</span>
    </div>
    
    <nav class="nav flex-column">
        <a href="?page=dashboard" class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i> <span>แดชบอร์ด</span>
        </a>

        <a href="?page=dashboard" class="nav-link position-relative <?php echo ($currentPage == 'detail') ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-check"></i> <span>ตรวจสอบรายการ</span>
            <span class="position-absolute top-50 end-0 translate-middle-y badge rounded-pill bg-danger me-3 d-none" id="sidebarBadge">0</span>
        </a>

        <a href="#" class="nav-link">
            <i class="bi bi-people"></i> <span>รายชื่อลูกบ้าน</span>
        </a>
    </nav>

    <div class="mt-auto p-3">
        <a href="?page=login" class="nav-link text-danger">
            <i class="bi bi-box-arrow-left"></i> <span>ออกจากระบบ</span>
        </a>
    </div>
</div>