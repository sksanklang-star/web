<?php 
// views/search.php
// ไม่ต้องใส่ session_start() เพราะ index.php เรียกให้แล้ว
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระค่าธรรมเนียมขยะ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .main-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; max-width: 800px; width: 100%; }
        .header-section { background: linear-gradient(135deg, #4a148c 0%, #7c43bd 100%); color: white; padding: 30px 20px; text-align: center; }
        .header-section h2 { font-weight: 600; font-size: 1.8rem; margin-bottom: 5px; }
        .step-indicator { color: #d32f2f; font-weight: 600; margin: 20px 0; text-align: center; font-size: 1.1rem; }
        .btn-search { background-color: #1a237e; color: white; padding: 12px; border-radius: 8px; font-size: 1.1rem; width: 100%; transition: transform 0.2s; border: none; }
        .btn-search:hover { background-color: #283593; transform: translateY(-2px); }
        .form-control, .form-select { padding: 12px; border-radius: 8px; background-color: #f8f9fa; border: 1px solid #dee2e6; }
        .form-control:focus { border-color: #7c43bd; background: white; box-shadow: 0 0 0 0.2rem rgba(124, 67, 189, 0.25); }
    </style>
</head>
<body>
    <div class="container px-3">
        <div class="main-card mx-auto">
            <div class="header-section">
                <h2><i class="bi bi-wallet2"></i> ชำระค่าธรรมเนียมจัดเก็บขยะ</h2>
                <p class="mb-0 opacity-75">ดูประวัติการชำระเงิน / ภาษีท้องถิ่น</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <div class="step-indicator">1. กรอกรายละเอียดเพื่อค้นหา</div>
                
                <form id="searchForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-calendar-event"></i> ปีงบประมาณ</label>
                            <select class="form-select" name="year"><option value="2569">2569</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-geo-alt"></i> หมู่ที่</label>
                            <select class="form-select" name="village">
                                <option value="" disabled selected>-- เลือกหมู่ --</option>
                                <option value="1">หมู่ที่ 1</option>
                                <option value="2">หมู่ที่ 2</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label"><i class="bi bi-house-door"></i> บ้านเลขที่</label>
                            <input type="text" class="form-control" name="houseNo" placeholder="เช่น 111" required>
                        </div>
                    </div>
                    <div class="mt-4 pt-2">
                        <button type="submit" class="btn btn-search"><i class="bi bi-search"></i> ค้นหาข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData).toString();
            // *** แก้ลิงก์ตรงนี้เป็น ?page=result ***
            window.location.href = `/?page=result&${params}`;
        });
    </script>
</body>
</html>