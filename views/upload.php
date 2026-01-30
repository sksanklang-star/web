<?php
// views/upload.php
// ลบ session_start()

if (!isset($_SESSION['payment_summary']) || !isset($_SESSION['current_user_data'])) {
    // *** แก้ Redirect ให้ผ่าน Router ***
    header("Location: ?page=search");
    exit();
}

$summary = $_SESSION['payment_summary'];
$userData = $_SESSION['current_user_data'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงินและแจ้งหลักฐาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; padding: 20px 0; min-height: 100vh; }
        .main-card { border: none; border-radius: 15px; background: white; max-width: 800px; margin: 0 auto; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .header-section { background: linear-gradient(135deg, #4a148c 0%, #7c43bd 100%); color: white; padding: 30px 20px; text-align: center; }
        .summary-box { background-color: #e3f2fd; border: 1px solid #90caf9; border-radius: 12px; padding: 20px; margin-bottom: 25px; }
        .total-highlight { color: #d32f2f; font-size: 1.3rem; font-weight: 700; }
        .bank-box { background: #fff; border: 2px dashed #4a148c; border-radius: 12px; padding: 20px; text-align: center; background-color: #f3e5f5; }
        .upload-area { border: 2px dashed #ccc; border-radius: 10px; padding: 30px; text-align: center; background: #fafafa; cursor: pointer; transition: 0.3s; }
        .upload-area:hover { background: #f0f0f0; border-color: #4a148c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card">
            <div class="header-section">
                <h3 class="fw-bold mb-1"><i class="bi bi-qr-code-scan"></i> ชำระเงินและแจ้งหลักฐาน</h3>
                <small>3. โอนเงินและอัปโหลดสลิป</small>
            </div>
            <div class="card-body p-4">

                <div class="summary-box">
                    <h5 class="fw-bold text-primary border-bottom border-primary pb-2 mb-3"><i class="bi bi-receipt"></i> สรุปรายการที่ต้องชำระ</h5>
                    <div class="row g-2">
                        <div class="col-6 text-secondary">ชื่อผู้ชำระ:</div>
                        <div class="col-6 fw-bold text-end"><?php echo htmlspecialchars($userData['name']); ?></div>
                        
                        <div class="col-6 text-secondary">เบอร์โทรศัพท์:</div>
                        <div class="col-6 fw-bold text-end"><?php echo htmlspecialchars($summary['phone']); ?></div>
                        
                        <div class="col-12"><hr class="my-2"></div>
                        
                        <div class="col-6 fw-bold align-self-center">ยอดโอนสุทธิ:</div>
                        <div class="col-6 text-end total-highlight"><?php echo number_format($summary['total'], 2); ?> บาท</div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="bank-box h-100 d-flex flex-column justify-content-center">
                            <h5 class="fw-bold" style="color: #4a148c;">ช่องทางการชำระเงิน</h5>
                            <div class="my-3">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/2/22/Krung_Thai_Bank_logo.svg" style="width: 50px;" alt="Bank Logo">
                                <div class="fw-bold mt-2">ธนาคารกรุงไทย</div>
                                <div class="text-muted small">ชื่อบัญชี: เทศบาลเมืองแม่เหียะ</div>
                                <div class="fs-4 fw-bold text-primary mt-1">787-0-02669-7</div>
                            </div>
                            <div class="bg-white p-2 rounded border d-inline-block mx-auto" style="max-width: 150px;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/d/d0/QR_code_for_mobile_English_Wikipedia.svg" class="img-fluid" alt="QR">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="fw-bold mb-3"><i class="bi bi-cloud-upload"></i> แนบหลักฐานการโอน</h5>
                        
                        <div class="upload-area" onclick="document.getElementById('slipFile').click()">
                            <input type="file" id="slipFile" class="d-none" accept="image/*" onchange="previewFile()">
                            <i class="bi bi-image fs-1 text-muted"></i>
                            <p class="mb-0 mt-2">แตะเพื่อเลือกรูปภาพสลิป</p>
                            <small class="text-muted">(รองรับ .jpg, .png)</small>
                        </div>
                        
                        <div id="previewBox" class="mt-3 text-center d-none">
                            <img id="imgPreview" src="" class="img-fluid rounded border shadow-sm" style="max-height: 200px;">
                            <button class="btn btn-sm btn-outline-danger d-block mx-auto mt-2" onclick="clearFile()">ลบรูปภาพ</button>
                        </div>

                        <button class="btn btn-primary w-100 mt-4 py-2" style="background: #4a148c; border:none;" onclick="finalSubmit()">
                            <i class="bi bi-send-fill me-2"></i> แจ้งชำระเงิน
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function previewFile() {
            const file = document.getElementById('slipFile').files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imgPreview').src = e.target.result;
                    document.getElementById('previewBox').classList.remove('d-none');
                    document.querySelector('.upload-area').classList.add('d-none');
                }
                reader.readAsDataURL(file);
            }
        }

        function clearFile() {
            document.getElementById('slipFile').value = "";
            document.getElementById('previewBox').classList.add('d-none');
            document.querySelector('.upload-area').classList.remove('d-none');
        }

        function finalSubmit() {
            const fileInput = document.getElementById('slipFile');
            if(fileInput.files.length === 0) {
                Swal.fire('ยังไม่ได้แนบสลิป', 'กรุณาอัปโหลดหลักฐานการโอนเงิน', 'warning');
                return;
            }

            Swal.fire({
                title: 'กำลังบันทึกข้อมูล...',
                timer: 2000,
                timerProgressBar: true,
                didOpen: () => { Swal.showLoading() }
            }).then(() => {
                Swal.fire({
                    title: 'แจ้งชำระเงินสำเร็จ!',
                    text: 'เจ้าหน้าที่จะตรวจสอบข้อมูลภายใน 24 ชม.',
                    icon: 'success',
                    confirmButtonText: 'กลับหน้าหลัก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // *** แก้ลิงก์กลับหน้าหลัก Router ***
                        window.location.href = '/?page=search'; 
                    }
                });
            });
        }
    </script>
</body>
</html>