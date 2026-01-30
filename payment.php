<?php
session_start();
// Security Check: ถ้าไม่มี ID ส่งมา ดีดกลับหน้าแรก
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำนวณยอดชำระ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; padding: 20px 0; }
        .main-card { background: white; border-radius: 15px; max-width: 900px; margin: 0 auto; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .header-section { background: linear-gradient(135deg, #4a148c 0%, #7c43bd 100%); color: white; padding: 30px; text-align: center; }
        .row-paid { background-color: #e8f5e9; color: #2e7d32; }
        .row-pending { background-color: #fff8e1; color: #e65100; }
        .total-display { font-size: 1.8rem; font-weight: 600; color: #4a148c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card">
            <div class="header-section">
                <h3>คำนวณยอดชำระ</h3>
            </div>
            <div class="card-body p-4">
                <div id="userInfo" class="alert alert-light border mb-4">
                    </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>เดือน</th><th class="text-end">ยอดเงิน</th><th>สถานะ</th><th>เลือก</th></tr></thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>

                <div class="bg-light p-4 rounded mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label>เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                        <input type="tel" id="phoneInput" class="form-control w-50" placeholder="0xx-xxx-xxxx">
                    </div>
                    <div class="text-end mb-3">
                        <div class="text-muted">ยอดรวมที่ต้องชำระ</div>
                        <div class="total-display"><span id="grandTotal">0</span> บาท</div>
                    </div>
                    <button class="btn btn-primary w-100 py-3" style="background:#4a148c;" onclick="confirmPayment()">ยืนยันการชำระเงิน</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const userId = <?php echo $_GET['id']; ?>;
        const feePerMonth = 40;
        let selectedMonths = [];
        const monthNames = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];

        // โหลดข้อมูลจาก API (get_detail)
        fetch(`api.php?action=get_detail&id=${userId}`)
            .then(res => res.json())
            .then(data => {
                if(data.status === 'error') { alert('ไม่พบข้อมูล'); window.location.href='index.php'; return; }
                
                const user = data.data;
                document.getElementById('userInfo').innerHTML = `<strong>${user.name}</strong> - บ้านเลขที่ ${user.houseNo}`;
                
                renderTable(user.months);
            });

        function renderTable(monthsStatus) {
            const tbody = document.getElementById('tableBody');
            let html = '';
            monthsStatus.forEach((status, index) => {
                let statusText = '', rowClass = '', checkHtml = '';
                
                if (status === 1) { // จ่ายแล้ว
                    statusText = '<span class="badge bg-success">ชำระแล้ว</span>';
                    rowClass = 'row-paid';
                    checkHtml = '<input type="checkbox" disabled checked>';
                } else if (status === 2) { // รอตรวจสอบ
                    statusText = '<span class="badge bg-warning text-dark">รอตรวจสอบ</span>';
                    rowClass = 'row-pending';
                    checkHtml = '<input type="checkbox" disabled>';
                } else { // ค้างชำระ
                    statusText = '<span class="badge bg-danger">ค้างชำระ</span>';
                    checkHtml = `<input type="checkbox" class="pay-checkbox" value="${index}" onchange="calculateTotal()">`;
                }

                html += `<tr class="${rowClass}">
                    <td>${monthNames[index]}</td>
                    <td class="text-end">${feePerMonth}</td>
                    <td>${statusText}</td>
                    <td>${checkHtml}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        function calculateTotal() {
            const checkboxes = document.querySelectorAll('.pay-checkbox:checked');
            const total = checkboxes.length * feePerMonth;
            document.getElementById('grandTotal').innerText = total;
        }

        function confirmPayment() {
            const phone = document.getElementById('phoneInput').value;
            const checkboxes = document.querySelectorAll('.pay-checkbox:checked');
            
            if(checkboxes.length === 0) { alert('กรุณาเลือกเดือนที่ต้องการชำระ'); return; }
            if(!phone) { alert('กรุณากรอกเบอร์โทรศัพท์'); return; }

            // รวบรวมข้อมูลเดือนที่เลือก
            let monthsToPay = [];
            checkboxes.forEach(box => monthsToPay.push(box.value));
            const total = checkboxes.length * feePerMonth;

            // ส่งข้อมูลกลับไปหา API เพื่อ Lock Session (Security)
            fetch('api.php?action=confirm_payment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ total: total, months: monthsToPay, phone: phone })
            }).then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    window.location.href = 'upload.php'; // ไปหน้า Upload อย่างปลอดภัย
                }
            });
        }
    </script>
</body>
</html>