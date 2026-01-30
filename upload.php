<?php
session_start();
// Security Check: ต้องผ่านการคำนวณเงินมาก่อนเท่านั้น
if (!isset($_SESSION['payment_summary'])) {
    header("Location: index.php");
    exit();
}

$summary = $_SESSION['payment_summary'];
$userData = $_SESSION['current_user_data']; // ข้อมูลผู้ใช้ที่ดึงมาตั้งแต่หน้าแรก
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัปโหลดสลิป</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; padding: 20px 0; }
        .main-card { background: white; border-radius: 15px; max-width: 800px; margin: 0 auto; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .header-section { background: linear-gradient(135deg, #4a148c 0%, #7c43bd 100%); color: white; padding: 30px; text-align: center; }
        .summary-box { background-color: #e3f2fd; border-radius: 12px; padding: 20px; margin-bottom: 25px; }
        .total-highlight { color: #d32f2f; font-size: 1.2rem; font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card">
            <div class="header-section">
                <h3>ชำระเงินและแจ้งโอน</h3>
                <small>3. สแกนจ่ายและแนบสลิป</small>
            </div>
            <div class="card-body p-4">
                
                <div class="summary-box">
                    <h5 class="text-primary fw-bold">สรุปรายการ</h5>
                    <div class="d-flex justify-content-between">
                        <span>ชื่อผู้ชำระ:</span>
                        <strong><?php echo $userData['name']; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span>เบอร์ติดต่อ:</span>
                        <strong><?php echo $summary['phone']; ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>ยอดโอน:</span>
                        <span class="total-highlight"><?php echo number_format($summary['total'], 2); ?> บาท</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 text-center mb-3">
                        <div class="border p-3 rounded bg-light">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/d/d0/QR_code_for_mobile_English_Wikipedia.svg" style="max-width:150px;" alt="QR Code">
                            <p class="mt-2 mb-0 fw-bold">ธนาคารกรุงไทย</p>
                            <small>000-0-00000-0 (เทศบาล)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="mb-2 fw-bold">อัปโหลดสลิป</label>
                        <input type="file" class="form-control mb-3" accept="image/*" id="slipFile">
                        <button class="btn btn-primary w-100" style="background:#4a148c;" onclick="finalSubmit()">แจ้งชำระเงิน</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function finalSubmit() {
            const fileInput = document.getElementById('slipFile');
            if(fileInput.files.length === 0) {
                Swal.fire('แจ้งเตือน', 'กรุณาแนบสลิปการโอนเงิน', 'warning');
                return;
            }

            // จำลองการส่งสำเร็จ (Success Mock)
            Swal.fire({
                title: 'บันทึกสำเร็จ!',
                text: 'เจ้าหน้าที่จะตรวจสอบภายใน 24 ชม.',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'index.php'; // กลับหน้าแรก
                }
            });
            
            // ในอนาคต: ใช้ FormData ส่งไฟล์ไปที่ api.php
            // const formData = new FormData();
            // formData.append('slip', fileInput.files[0]);
            // fetch('api.php?action=upload', { method: 'POST', body: formData })...
        }
    </script>
</body>
</html>