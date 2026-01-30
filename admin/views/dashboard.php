<?php
// Security Check
if (!defined('ACCESS_ALLOWED')) exit;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - จัดการค่าขยะ</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; overflow-x: hidden; }

        /* Sidebar Styling */
        .sidebar { height: 100vh; width: 260px; position: fixed; top: 0; left: 0; background: linear-gradient(180deg, #2c0e58 0%, #4a148c 100%); color: white; padding-top: 20px; z-index: 1050; transition: all 0.3s ease; }
        .sidebar.collapsed { left: -260px; }
        .sidebar-brand { font-size: 1.5rem; font-weight: 600; text-align: center; margin-bottom: 30px; padding: 0 15px; }
        .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; font-size: 1.1rem; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: rgba(255,255,255,0.1); color: white; border-left: 5px solid #ffcc00; }
        .nav-link i { margin-right: 10px; }

        /* Main Content */
        .main-content { margin-left: 260px; padding: 30px; transition: all 0.3s ease; }
        .main-content.expanded { margin-left: 0; }

        /* Mobile Overlay */
        #overlay { display: none; position: fixed; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 1040; top: 0; left: 0; }
        
        /* Hamburger Button */
        #toggleBtn { font-size: 1.8rem; cursor: pointer; margin-right: 15px; color: #2c0e58; background: white; padding: 2px 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

        /* Stat Cards */
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: transform 0.3s; background: white; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        
        /* Table & Filters */
        .table-card { background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); padding: 20px; margin-top: 25px; }
        
        /* === IMPROVED FILTER BUTTONS (SCROLLABLE ON MOBILE) === */
        .filter-btn { 
            border-radius: 50px; /* Pill shape looks better */
            padding: 8px 20px; 
            font-size: 0.95rem; 
            margin-right: 8px; 
            border: 1px solid #eee; 
            background: #f8f9fa; 
            color: #666; 
            transition: 0.2s; 
            white-space: nowrap; /* ห้ามตัดคำ */
        }
        .filter-btn:hover { background: #e9ecef; }
        
        /* Active States with Stronger Colors */
        .filter-btn.active.pending { background-color: #fff3cd; color: #856404; border-color: #ffeeba; font-weight: 600; box-shadow: 0 2px 5px rgba(255, 193, 7, 0.2); }
        .filter-btn.active.approved { background-color: #d4edda; color: #155724; border-color: #c3e6cb; font-weight: 600; box-shadow: 0 2px 5px rgba(40, 167, 69, 0.2); }
        .filter-btn.active.all { background-color: #e2e3e5; color: #383d41; border-color: #d6d8db; font-weight: 600; }

        .badge-pending { background-color: #fff8e1; color: #f57c00; border: 1px solid #ffe0b2; }
        .badge-success { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .badge-danger { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }

        /* === MOBILE RESPONSIVE FIXES === */
        @media (max-width: 768px) {
            .sidebar { left: -260px; }
            .sidebar.active { left: 0; }
            .main-content { margin-left: 0; padding: 15px; padding-bottom: 80px; } /* เพิ่ม Padding ล่างกันตกขอบ */
            #overlay.show { display: block; }
            
            /* ทำให้ Filter เลื่อนแนวนอนได้ */
            .mobile-filter-container {
                display: flex;
                overflow-x: auto;
                padding-bottom: 10px;
                margin-top: 15px;
                width: 100%;
                -webkit-overflow-scrolling: touch; /* ลื่นขึ้นบน iOS */
            }
            /* ซ่อน Scrollbar แต่ยังเลื่อนได้ */
            .mobile-filter-container::-webkit-scrollbar { display: none; }
            
            .filter-btn { flex-shrink: 0; } /* ห้ามปุ่มหดตัว */
        }
    </style>
</head>
<body>

    <div id="overlay" onclick="toggleSidebar()"></div>

    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-list" id="toggleBtn" onclick="toggleSidebar()"></i>
            <div>
                <h3 class="fw-bold mb-0">ภาพรวมระบบ</h3>
                <small class="text-muted">เทศบาลตำบลสันกลาง</small>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4">
                <div class="card stat-card p-3 h-100">
                    <div class="d-flex align-items-center flex-column flex-md-row text-center text-md-start">
                        <div class="icon-box bg-success bg-opacity-10 text-success me-md-3 mb-2 mb-md-0"><i class="bi bi-currency-dollar"></i></div>
                        <div><div class="text-muted small">ยอดเดือนนี้</div><h5 class="fw-bold mb-0">12,400 ฿</h5></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card stat-card p-3 h-100 border-warning border-start border-4">
                    <div class="d-flex align-items-center flex-column flex-md-row text-center text-md-start">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning me-md-3 mb-2 mb-md-0"><i class="bi bi-hourglass-split"></i></div>
                        <div><div class="text-muted small">รอตรวจสอบ</div><h5 class="fw-bold mb-0 text-warning" id="pendingCount">0</h5></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card stat-card p-3 h-100">
                    <div class="d-flex align-items-center flex-row"> <div class="icon-box bg-danger bg-opacity-10 text-danger me-3"><i class="bi bi-exclamation-circle"></i></div>
                        <div><div class="text-muted small">ค้างชำระ (>3 เดือน)</div><h5 class="fw-bold mb-0">2 ครัวเรือน</h5></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                <h5 class="fw-bold mb-0">รายการธุรกรรมล่าสุด</h5>
                
                <div class="mobile-filter-container">
                    <button class="filter-btn pending active" onclick="setFilter('pending')">
                        <i class="bi bi-clock-history"></i> รอตรวจสอบ
                    </button>
                    <button class="filter-btn approved" onclick="setFilter('approved')">
                        <i class="bi bi-check-circle-fill"></i> อนุมัติแล้ว
                    </button>
                    <button class="filter-btn all" onclick="setFilter('all')">
                        <i class="bi bi-list-ul"></i> ทั้งหมด
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 100px;">วันที่</th>
                            <th style="min-width: 150px;">ชื่อผู้ชำระ</th>
                            <th style="min-width: 200px;">รายละเอียด</th>
                            <th>ยอดเงิน</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="adminTableBody"></tbody>
                </table>
                <div id="noDataMessage" class="text-center py-5 text-muted d-none">
                    <i class="bi bi-inbox fs-1 opacity-50"></i><p class="mt-2">ไม่มีข้อมูลในสถานะนี้</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const overlay = document.getElementById('overlay');

            if (window.innerWidth > 768) {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            } else {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('show');
            }
        }

        let allUsers = [];
        let currentFilter = 'pending';

        fetch('/api.php?action=admin_get_all')
            .then(res => res.json())
            .then(data => {
                if(data.status !== 'success') { window.location.href = '?page=login'; return; }
                
                allUsers = data.data;
                const pendingCount = data.stats.pending;
                document.getElementById('pendingCount').innerText = `${pendingCount} รายการ`;
                
                const sbBadge = document.getElementById('sidebarBadge');
                if(sbBadge) {
                    sbBadge.innerText = pendingCount;
                    if(pendingCount > 0) sbBadge.classList.remove('d-none');
                }

                renderAdminTable();
            });

        function setFilter(status) {
            currentFilter = status;
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.classList.contains(status)) btn.classList.add('active');
            });
            renderAdminTable();
        }

        function renderAdminTable() {
            const tableBody = document.getElementById('adminTableBody');
            const noDataMsg = document.getElementById('noDataMessage');
            
            let filteredData = allUsers.filter(user => {
                let hasPending = user.months.includes(2);
                let hasPaid = user.months.includes(1);
                
                if (currentFilter === 'pending') return hasPending;
                if (currentFilter === 'approved') return hasPaid && !hasPending;
                if (currentFilter === 'all') return true;
                return false;
            });

            if (filteredData.length === 0) {
                tableBody.innerHTML = '';
                noDataMsg.classList.remove('d-none');
            } else {
                noDataMsg.classList.add('d-none');
                let html = '';
                filteredData.forEach(user => {
                    let hasPending = user.months.includes(2);
                    let badge = '';
                    let actionBtn = '';
                    let amountDisplay = 0;

                    if (hasPending) {
                        badge = '<span class="badge badge-pending"><i class="bi bi-clock"></i> รอตรวจสอบ</span>';
                        actionBtn = `<a href="?page=detail&token=${user.hash_id}" class="btn btn-sm btn-primary shadow-sm text-nowrap"><i class="bi bi-eye"></i> ตรวจสอบ</a>`;
                        user.months.forEach(m => { if(m===2) amountDisplay += 40; });
                    } else if (user.months.includes(1)) {
                        badge = '<span class="badge badge-success"><i class="bi bi-check"></i> ชำระแล้ว</span>';
                        actionBtn = `<a href="?page=detail&token=${user.hash_id}" class="btn btn-sm btn-outline-secondary text-nowrap"><i class="bi bi-search"></i> ดูข้อมูล</a>`;
                    } else {
                        badge = '<span class="badge badge-danger">ค้างชำระ</span>';
                        actionBtn = `<a href="?page=detail&token=${user.hash_id}" class="btn btn-sm btn-outline-secondary text-nowrap"><i class="bi bi-search"></i> ดูข้อมูล</a>`;
                    }

                    html += `
                        <tr>
                            <td class="small text-muted text-nowrap">${user.last_updated}</td>
                            <td class="fw-bold">${user.name}</td>
                            <td class="small text-muted"><i class="bi bi-geo-alt"></i> ม.${user.village} บ้านเลขที่ ${user.houseNo}</td>
                            <td class="fw-bold text-primary">${amountDisplay > 0 ? amountDisplay : '-'}</td>
                            <td>${badge}</td>
                            <td>${actionBtn}</td>
                        </tr>`;
                });
                tableBody.innerHTML = html;
            }
        }
    </script>
</body>
</html>