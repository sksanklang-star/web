<?php
session_start();
// สร้าง Token ป้องกันการยิง Form จากเว็บอื่น
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
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
        .header-section { background: linear-gradient(135deg, #4a148c 0%, #7c43bd 100%); color: white; padding: 30px; text-center; }
        .btn-search { background-color: #1a237e; color: white; width: 100%; padding: 12px; border-radius: 8px; border: none; }
        .btn-search:hover { background-color: #283593; }
    </style>
</head>
<body>
    <div class="container px-3">
        <div class="main-card mx-auto">
            <div class="header-section text-center">
                <h2><i class="bi bi-wallet2"></i> ชำระค่าธรรมเนียมจัดเก็บขยะ</h2>
                <p class="mb-0 opacity-75">ดูประวัติการชำระเงิน / ภาษีท้องถิ่น</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <form id="searchForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ปีงบประมาณ</label>
                            <select class="form-select" name="year"><option value="2569">2569</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">หมู่ที่</label>
                            <select class="form-select" name="village">
                                <option value="1">หมู่ที่ 1</option>
                                <option value="2">หมู่ที่ 2</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">บ้านเลขที่</label>
                            <input type="text" class="form-control" name="houseNo" required placeholder="เช่น 111">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-search">ค้นหาข้อมูล</button>
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
            // ส่งค่าไป result.php ผ่าน URL Parameter
            window.location.href = `result.php?${params}`;
        });
    </script>
</body>
</html>