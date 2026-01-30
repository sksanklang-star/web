<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกรายการชำระเงิน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; padding: 20px 0; min-height: 100vh; }
        .main-card { border: none; border-radius: 15px; background: white; max-width: 800px; margin: 0 auto; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .header-section { background: linear-gradient(135deg, #4a148c 0%, #7c43bd 100%); color: white; padding: 30px 20px; text-align: center; }
        .step-indicator { background-color: rgba(255, 255, 255, 0.2); display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 0.95rem; margin-top: 10px; }
        .result-card { background: white; border: 1px solid #eee; border-radius: 12px; padding: 20px; margin-bottom: 15px; transition: all 0.3s ease; border-left: 5px solid #4a148c; }
        .result-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(74, 20, 140, 0.1); border-color: #d1c4e9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card">
            <div class="header-section">
                <div class="fs-4 fw-bold"><i class="bi bi-list-check"></i> ผลการค้นหา</div>
                <div class="step-indicator">2. เลือกรายการที่ต้องการชำระ</div>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-light border mb-4 text-muted"><i class="bi bi-info-circle me-2"></i> ผลการค้นหาสำหรับบ้านเลขที่: <strong id="searchKeyword">-</strong></div>
                <div id="resultsContainer" class="text-center py-4"><div class="spinner-border text-primary"></div> กำลังโหลดข้อมูล...</div>
                <div class="mt-5 text-center">
                    <a href="index.php" class="btn btn-light text-muted px-4 rounded-pill"><i class="bi bi-arrow-left"></i> กลับไปหน้าค้นหา</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const houseNo = urlParams.get('houseNo');
        document.getElementById('searchKeyword').innerText = houseNo || '-';

        fetch(`api.php?action=search&houseNo=${houseNo}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('resultsContainer');
                container.innerHTML = '';
                
                if(data.data && data.data.length > 0) {
                    data.data.forEach(user => {
                        // ใช้ Token แทน ID
                        container.innerHTML += `
                            <div class="result-card">
                                <div class="row align-items-center g-3">
                                    <div class="col-auto"><div class="bg-light rounded-circle p-3 text-primary"><i class="bi bi-person-fill fs-4"></i></div></div>
                                    <div class="col">
                                        <div class="fs-5 fw-bold text-dark">${user.name}</div>
                                        <div class="text-muted small"><i class="bi bi-house-door"></i> บ้านเลขที่ ${user.houseNo} หมู่ที่ ${user.village} (ปี ${user.year})</div>
                                    </div>
                                    <div class="col-12 col-md-auto">
                                        <button class="btn btn-outline-primary w-100 rounded-pill" onclick="selectUser('${user.token}')">
                                            เลือกรายการ <i class="bi bi-arrow-right-short"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    container.innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-search fs-1"></i><p class="mt-3">ไม่พบข้อมูลที่ค้นหา</p></div>';
                }
            });

        function selectUser(token) {
            window.location.href = `payment.php?token=${token}`;
        }
    </script>
</body>
</html>