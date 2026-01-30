<?php
if (!defined('ACCESS_ALLOWED')) exit;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - ตรวจสอบและแก้ไขข้อมูล</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; padding-bottom: 100px; overflow-x: hidden; }
        .sidebar { height: 100vh; width: 260px; position: fixed; top: 0; left: 0; background: linear-gradient(180deg, #2c0e58 0%, #4a148c 100%); color: white; padding-top: 20px; z-index: 1050; transition: all 0.3s ease; }
        .sidebar.collapsed { left: -260px; }
        .sidebar-brand { font-size: 1.5rem; font-weight: 600; text-align: center; margin-bottom: 30px; padding: 0 15px; }
        .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; font-size: 1.1rem; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: rgba(255,255,255,0.1); color: white; border-left: 5px solid #ffcc00; }
        .nav-link i { margin-right: 10px; }
        .main-content { margin-left: 260px; transition: all 0.3s ease; }
        .main-content.expanded { margin-left: 0; }
        #overlay { display: none; position: fixed; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 1040; top: 0; left: 0; }
        .top-bar { background: white; padding: 15px 30px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
        #toggleBtn { font-size: 1.8rem; cursor: pointer; margin-right: 15px; color: #2c0e58; background: white; padding: 2px 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .detail-card { background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); padding: 25px; height: 100%; }
        
        /* Edit Mode Logic */
        .editing .view-mode { display: none !important; } /* Force hide in edit mode */
        .editing .edit-mode { display: block !important; } /* Force show in edit mode */
        .view-mode { display: block; }
        .edit-mode { display: none; }

        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .bg-paid { background-color: #e8f5e9; color: #2e7d32; }
        .bg-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; } 
        .bg-unpaid { background-color: #ffebee; color: #c62828; }
        
        .log-box { background-color: #f8f9fa; border-radius: 8px; padding: 15px; max-height: 350px; overflow-y: auto; border: 1px solid #eee; }
        .log-session { border-bottom: 1px solid #dee2e6; padding-bottom: 12px; margin-bottom: 15px; }
        .log-session:last-child { border-bottom: none; margin-bottom: 0; }
        .log-session-time { display: block; color: #4a148c; font-weight: 600; font-size: 0.8rem; margin-bottom: 8px; background: #f0e6ff; padding: 2px 10px; border-radius: 4px; width: fit-content; }
        .log-item { font-size: 0.85rem; border-left: 3px solid #ffc107; padding-left: 10px; margin-bottom: 5px; }
        .log-detail b { color: #555; }

        .action-bar { position: fixed; bottom: 0; right: 0; left: 260px; background: white; padding: 15px 30px; box-shadow: 0 -4px 20px rgba(0,0,0,0.08); display: flex; justify-content: flex-end; align-items: center; gap: 15px; z-index: 1000; transition: all 0.3s ease; }
        .action-bar.full-width { left: 0; }

        @media (max-width: 768px) {
            .sidebar { left: -260px; }
            .sidebar.active { left: 0; }
            .main-content { margin-left: 0; }
            #overlay.show { display: block; }
            .action-bar { left: 0 !important; padding: 15px; justify-content: space-between; gap: 10px; }
            .action-bar .fs-5 { display: none; }
            .action-bar .btn { flex: 1; padding: 12px 0; font-size: 1rem; }
        }
    </style>
</head>
<body>

    <div id="overlay" onclick="toggleSidebar()"></div>

    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="top-bar">
            <div class="d-flex align-items-center">
                <i class="bi bi-list" id="toggleBtn" onclick="toggleSidebar()"></i>
                <h5 class="mb-0 fw-bold text-truncate" style="max-width: 200px;">ตรวจสอบ (Token: <span id="tokenDisplay">...</span>)</h5>
            </div>
            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill" id="statusBadge">โหลด...</span>
        </div>

        <div class="container-fluid p-3 p-md-4">
            <div class="row g-4">
                <div class="col-lg-7 order-2 order-lg-1">
                    <div class="detail-card" id="customerInfoCard">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold text-primary mb-0"><i class="bi bi-person-circle"></i> ข้อมูลลูกบ้าน</h5>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="toggleEditMode()">
                                <i class="bi bi-pencil"></i> แก้ไข
                            </button>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="text-muted small">ชื่อ-นามสกุล</label>
                                <div class="view-mode fw-bold fs-5" id="viewName">-</div>
                                <input type="text" class="form-control edit-mode" id="editName" value="">
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">ที่อยู่</label>
                                <div class="view-mode fw-bold" id="viewAddress">-</div>
                                <input type="text" class="form-control edit-mode" id="editAddress" value="">
                            </div>
                            <div class="col-12 edit-mode text-end mt-3">
                                <button class="btn btn-sm btn-light me-2" onclick="cancelEdit()">ยกเลิก</button>
                                <button class="btn btn-sm btn-primary px-4" onclick="saveEdit()">บันทึก</button>
                            </div>
                        </div>

                        <hr>
                        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-calendar-check"></i> รายการเดือน</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center">
                                <thead class="table-light"><tr><th>เดือน</th><th>ยอด</th><th>สถานะ</th></tr></thead>
                                <tbody id="monthTableBody"></tbody>
                            </table>
                        </div>

                        <hr>
                        <h6 class="fw-bold text-secondary mb-2"><i class="bi bi-clock-history"></i> ประวัติแก้ไข</h6>
                        <div class="log-box" id="logContainer"></div>
                    </div>
                </div>

                <div class="col-lg-5 order-1 order-lg-2">
                    <div class="detail-card text-center">
                        <h5 class="fw-bold text-start mb-3">หลักฐานการโอน</h5>
                        <div id="imageContainer" class="bg-light rounded d-flex align-items-center justify-content-center" style="min-height: 300px; border: 2px dashed #ddd;">
                            </div>
                        <p class="mt-3 text-muted small" id="lastUpdated">-</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-bar" id="actionBar">
            <div class="me-auto d-none d-md-block"><span class="fs-5">ยอดที่รอตรวจสอบ: <b id="pendingAmount" class="text-danger">0.00 บาท</b></span></div>
            <button class="btn btn-outline-danger px-4 rounded-pill" onclick="submitAction('reject')"><i class="bi bi-x-circle"></i> ไม่อนุมัติ</button>
            <button class="btn btn-success px-4 px-md-5 rounded-pill shadow-sm" style="background:#4a148c; border:none;" onclick="submitAction('approve')"><i class="bi bi-check-circle"></i> อนุมัติ (Approve)</button>
        </div>
    </div>

    <script> const CSRF_TOKEN = '<?php echo $_SESSION['csrf_admin_token']; ?>'; </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        document.getElementById('tokenDisplay').innerText = token ? token.substring(0,6)+'...' : '-';

        let currentUserData = null; 
        const monthNames = ["ต.ค.", "พ.ย.", "ธ.ค.", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย."];

        fetch(`/api.php?action=get_detail&token=${token}`)
            .then(res => res.json())
            .then(res => {
                if(res.status === 'error') {
                    Swal.fire('Error', 'ไม่พบข้อมูล', 'error').then(() => window.location.href='?page=dashboard');
                    return;
                }
                currentUserData = res.data;
                // Fix missing months array
                if (!Array.isArray(currentUserData.months)) currentUserData.months = Array(12).fill(0);
                
                renderUI(currentUserData);
            })
            .catch(err => Swal.fire('Network Error', err.message, 'error'));

        function renderUI(user) {
            // 1. Text Info
            document.getElementById('viewName').innerText = user.name || '-';
            document.getElementById('editName').value = user.name || '';
            const address = `บ้านเลขที่ ${user.houseNo} หมู่ ${user.village}`;
            document.getElementById('viewAddress').innerText = address;
            document.getElementById('editAddress').value = address;
            
            // 2. Image
            const imgContainer = document.getElementById('imageContainer');
            if (user.slip_image && user.slip_image.trim() !== '') {
                imgContainer.innerHTML = `<img src="${user.slip_image}" class="img-fluid rounded" style="max-height: 500px;">`;
            } else {
                imgContainer.innerHTML = `<div class="text-muted"><i class="bi bi-image-fill fs-1"></i><br>ไม่พบรูปภาพสลิป</div>`;
            }
            document.getElementById('lastUpdated').innerText = 'อัปเดตล่าสุด: ' + (user.last_updated || '-');

            // 3. Render Months Table (With Edit Dropdown)
            let html = '';
            let pendingTotal = 0;
            let hasPending = false;

            user.months.forEach((status, index) => {
                let badge = '';
                // View Mode Badge
                if(status === 1) badge = '<span class="status-badge bg-paid">ชำระแล้ว</span>';
                else if(status === 2) { 
                    badge = '<span class="status-badge bg-pending">รอตรวจสอบ</span>'; 
                    pendingTotal += 40; hasPending = true; 
                }
                else badge = '<span class="status-badge bg-unpaid">ค้างชำระ</span>';
                
                // Edit Mode Dropdown
                let selectHtml = `
                    <select class="form-select form-select-sm edit-mode month-select" data-month-index="${index}">
                        <option value="0" ${status === 0 ? 'selected' : ''}>ค้างชำระ</option>
                        <option value="1" ${status === 1 ? 'selected' : ''}>ชำระแล้ว</option>
                        <option value="2" ${status === 2 ? 'selected' : ''}>รอตรวจสอบ</option>
                    </select>
                `;

                let mName = monthNames[index % 12];
                html += `
                    <tr>
                        <td>${mName}</td>
                        <td>40</td>
                        <td>
                            <div class="view-mode">${badge}</div>
                            ${selectHtml}
                        </td>
                    </tr>`;
            });
            
            document.getElementById('monthTableBody').innerHTML = html;
            document.getElementById('pendingAmount').innerText = pendingTotal.toFixed(2) + ' บาท';
            
            // Update Top Badge
            const statusBadge = document.getElementById('statusBadge');
            if(hasPending) {
                statusBadge.className = 'badge bg-warning text-dark px-3 py-2 rounded-pill';
                statusBadge.innerText = 'รอตรวจสอบ';
            } else {
                statusBadge.className = 'badge bg-success text-white px-3 py-2 rounded-pill';
                statusBadge.innerText = 'เรียบร้อย';
            }
        }

        // --- Action Functions ---
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

        function toggleEditMode() { 
            document.getElementById('customerInfoCard').classList.add('editing'); 
        }

        function cancelEdit() { 
            document.getElementById('customerInfoCard').classList.remove('editing'); 
            // Reset Name/Addr
            document.getElementById('editName').value = document.getElementById('viewName').innerText;
            document.getElementById('editAddress').value = document.getElementById('viewAddress').innerText;
            // Reset Selects
            renderUI(currentUserData); // Re-render to reset dropdowns
        }

        function saveEdit() {
            const timestamp = new Date().toLocaleString('th-TH');
            const logContainer = document.getElementById('logContainer');
            let changes = [];

            // 1. Check Name Change
            const nameEl = document.getElementById('viewName');
            const newName = document.getElementById('editName').value;
            if (newName !== nameEl.innerText) {
                changes.push(`แก้ไขชื่อ จาก "<b>${nameEl.innerText}</b>" เป็น "<b>${newName}</b>"`);
                currentUserData.name = newName; // Update Local Data
            }

            // 2. Check Address Change
            const addrEl = document.getElementById('viewAddress');
            const newAddr = document.getElementById('editAddress').value;
            if (newAddr !== addrEl.innerText) {
                changes.push(`แก้ไขที่อยู่ จาก "<b>${addrEl.innerText}</b>" เป็น "<b>${newAddr}</b>"`);
                // (Note: In real DB we would parse this, but for mock display update text)
                currentUserData.addressStr = newAddr; 
            }

            // 3. Check Month Changes (New Feature!)
            const selects = document.querySelectorAll('.month-select');
            const statusMap = {0: 'ค้างชำระ', 1: 'ชำระแล้ว', 2: 'รอตรวจสอบ'};
            
            selects.forEach(sel => {
                const idx = parseInt(sel.getAttribute('data-month-index'));
                const newVal = parseInt(sel.value);
                const oldVal = currentUserData.months[idx];

                if (newVal !== oldVal) {
                    const mName = monthNames[idx % 12];
                    changes.push(`เปลี่ยนสถานะ <b>${mName}</b> จาก "${statusMap[oldVal]}" เป็น "${statusMap[newVal]}"`);
                    currentUserData.months[idx] = newVal; // Update Local Data
                }
            });

            // 4. Save & Log
            if (changes.length > 0) {
                let logItemsHtml = '';
                changes.forEach(msg => { logItemsHtml += `<div class="log-item"><div class="log-detail"><b>Admin:</b> ${msg}</div></div>`; });
                const sessionHtml = `<div class="log-session"><span class="log-session-time"><i class="bi bi-clock"></i> ${timestamp}</span>${logItemsHtml}</div>`;
                logContainer.insertAdjacentHTML('afterbegin', sessionHtml);
                
                Swal.fire('บันทึก', 'แก้ไขข้อมูลเรียบร้อย (Mock)', 'success');
                renderUI(currentUserData); // Refresh UI (Badges/Totals)
            } else {
                // No changes
                cancelEdit();
            }
            
            document.getElementById('customerInfoCard').classList.remove('editing');
        }

        function submitAction(status) {
            if(!currentUserData) return;
            Swal.fire({
                title: 'ยืนยัน?',
                text: status === 'approve' ? 'ต้องการอนุมัติรายการนี้ใช่ไหม' : 'ปฏิเสธรายการ',
                icon: 'question',
                showCancelButton: true
            }).then((res) => {
                if (res.isConfirmed) {
                    fetch('/api.php?action=admin_update_status', {
                        method: 'POST',
                        body: JSON.stringify({ 
                            id: currentUserData.id, 
                            status: status,
                            csrf_token: CSRF_TOKEN 
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if(data.status === 'success') {
                            Swal.fire('สำเร็จ', 'บันทึกสถานะเรียบร้อย', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>