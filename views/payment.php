<?php
// views/payment.php
// ลบ session_start() ออก

if (!isset($_GET['token'])) { 
    // *** แก้ Redirect ให้ผ่าน Router ***
    header("Location: ?page=search"); 
    exit(); 
}
$token = htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำนวณยอดชำระเงิน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; padding: 20px 0; min-height: 100vh; }
        .main-card { border: none; border-radius: 15px; background: white; max-width: 900px; margin: 0 auto; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .header-section { background: linear-gradient(135deg, #4a148c 0%, #7c43bd 100%); color: white; padding: 30px 20px; text-align: center; }
        .info-card { background-color: #f8f9fa; border-radius: 12px; padding: 20px; border-left: 5px solid #4a148c; margin-bottom: 25px; }
        .row-paid { background-color: #e8f5e9 !important; color: #2e7d32; }
        .row-pending { background-color: #fff8e1 !important; color: #e65100; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-block; }
        .status-overdue { background-color: #ffebee; color: #c62828; }
        .status-paid { background-color: #c8e6c9; color: #2e7d32; }
        .status-pending { background-color: #ffe0b2; color: #e65100; }
        .total-display { font-size: 1.8rem; font-weight: 600; color: #4a148c; }
        .pay-checkbox { width: 1.3em; height: 1.3em; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card">
            <div class="header-section">
                <h3 class="fw-bold mb-1"><i class="bi bi-calculator"></i> คำนวณยอดชำระเงิน</h3>
                <small>ตรวจสอบสถานะและเลือกรายการที่ต้องการชำระ</small>
            </div>
            <div class="card-body p-4">
                <div class="info-card" id="userInfoCard">
                    <div class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div> กำลังโหลดข้อมูล...</div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light"><tr><th>เดือน</th><th class="text-end">ยอดเงิน</th><th class="text-center">สถานะ</th><th class="text-center">เลือก</th></tr></thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>

                <div class="bg-light p-4 rounded-3 mt-4">
                    <div class="row g-4 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เบอร์โทรศัพท์ติดต่อกลับ <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control form-control-lg" id="phoneInput" placeholder="0xx-xxx-xxxx" maxlength="10">
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="text-muted">ยอดชำระรวม (โดยประมาณ)</div>
                            <div class="total-display"><span id="grandTotal">0</span> บาท</div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="?page=search" class="btn btn-outline-secondary">ยกเลิก</a>
                        <button class="btn btn-primary px-5 btn-lg" style="background: #4a148c; border:none;" onclick="confirmPayment()">ยืนยันการชำระเงิน</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const userToken = "<?php echo $token; ?>";
        const feePerMonth = 40;
        const monthNames = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];

        fetch(`api.php?action=get_detail&token=${userToken}`)
            .then(res => res.json())
            .then(data => {
                // *** แก้ redirect JS ***
                if(data.status === 'error') { window.location.href = '/?page=search'; return; }
                const user = data.data;
                
                document.getElementById('userInfoCard').innerHTML = `
                    <div class="row g-2">
                        <div class="col-md-6"><span class="fw-bold text-secondary">ชื่อผู้ชำระ:</span> <span class="text-dark fw-bold">${user.name}</span></div>
                        <div class="col-md-6"><span class="fw-bold text-secondary">ปีงบประมาณ:</span> <span class="text-dark fw-bold">${user.year}</span></div>
                        <div class="col-md-12"><span class="fw-bold text-secondary">ที่อยู่:</span> <span class="text-dark">บ้านเลขที่ ${user.houseNo} หมู่ที่ ${user.village}</span></div>
                    </div>
                `;
                renderTable(user.months);
            });

        function renderTable(monthsStatus) {
            const tbody = document.getElementById('tableBody');
            let html = '';
            monthsStatus.forEach((status, index) => {
                let statusBadge = '', rowClass = '', checkHtml = '';
                
                if (status === 1) {
                    statusBadge = '<span class="status-badge status-paid"><i class="bi bi-check-circle-fill"></i> ชำระแล้ว</span>'; rowClass = 'row-paid';
                    checkHtml = '<input type="checkbox" class="form-check-input" disabled checked style="opacity:0.5">';
                } else if (status === 2) {
                    statusBadge = '<span class="status-badge status-pending"><i class="bi bi-hourglass-split"></i> รอตรวจสอบ</span>'; rowClass = 'row-pending';
                    checkHtml = '<input type="checkbox" class="form-check-input" disabled style="opacity:0.5">';
                } else {
                    statusBadge = '<span class="status-badge status-overdue">ค้างชำระ</span>';
                    checkHtml = `<input type="checkbox" class="form-check-input pay-checkbox" value="${index}" onchange="calculateTotal()">`;
                }
                html += `<tr class="${rowClass}"><td>${monthNames[index]}</td><td class="text-end fw-bold">${feePerMonth}</td><td class="text-center">${statusBadge}</td><td class="text-center">${checkHtml}</td></tr>`;
            });
            tbody.innerHTML = html;
        }

        function calculateTotal() {
            const count = document.querySelectorAll('.pay-checkbox:checked').length;
            document.getElementById('grandTotal').innerText = (count * feePerMonth).toLocaleString();
        }

        function confirmPayment() {
            const phone = document.getElementById('phoneInput').value;
            const checkboxes = document.querySelectorAll('.pay-checkbox:checked');
            
            if(checkboxes.length === 0) { Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนที่ต้องการชำระ', 'warning'); return; }
            if(!phone || phone.length < 9) { Swal.fire('แจ้งเตือน', 'กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง', 'warning'); return; }

            let monthsToPay = [];
            checkboxes.forEach(box => monthsToPay.push(parseInt(box.value)));

            fetch('api.php?action=confirm_payment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ months: monthsToPay, phone: phone })
            }).then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    // *** แก้ลิงก์ไปหน้า Upload ***
                    window.location.href = '/?page=upload'; 
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', data.message, 'error').then(() => {
                        if(data.redirect) window.location.href = data.redirect;
                    });
                }
            });
        }
    </script>
</body>
</html>