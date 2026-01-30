<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกรายการ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; padding: 20px 0; }
        .main-card { border: none; border-radius: 15px; background: white; max-width: 800px; margin: 0 auto; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .header-section { background: linear-gradient(135deg, #4a148c 0%, #7c43bd 100%); color: white; padding: 30px 20px; text-align: center; }
        .result-card { border: 1px solid #eee; border-radius: 12px; padding: 20px; margin-bottom: 15px; border-left: 5px solid #4a148c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card">
            <div class="header-section">
                <h3>ผลการค้นหา</h3>
                <small>2. เลือกรายการที่ต้องการชำระ</small>
            </div>
            <div class="card-body p-4">
                <div id="resultsContainer" class="text-center"><div class="spinner-border text-primary"></div> กำลังโหลด...</div>
                <div class="mt-4 text-center">
                    <a href="index.php" class="btn btn-light rounded-pill">กลับไปค้นหา</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // รับค่า Search จาก URL
        const urlParams = new URLSearchParams(window.location.search);
        const houseNo = urlParams.get('houseNo');

        // เรียก API (Mock)
        fetch(`api.php?action=search&houseNo=${houseNo}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('resultsContainer');
                container.innerHTML = '';
                
                if(data.data.length > 0) {
                    data.data.forEach(user => {
                        container.innerHTML += `
                            <div class="result-card text-start">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-1">${user.name}</h5>
                                        <p class="mb-0 text-muted"><small>บ้านเลขที่ ${user.houseNo} หมู่ ${user.village}</small></p>
                                    </div>
                                    <button onclick="selectUser(${user.id})" class="btn btn-outline-primary rounded-pill">
                                        เลือก <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    container.innerHTML = '<div class="alert alert-warning">ไม่พบข้อมูล</div>';
                }
            });

        function selectUser(id) {
            // ส่งไปหน้า Payment โดยแนบ ID
            window.location.href = `payment.php?id=${id}`;
        }
    </script>
</body>
</html>