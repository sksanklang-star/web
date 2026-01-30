<?php
// api.php
header('Content-Type: application/json');
session_start();

// จำลองข้อมูล Database (Mock Data)
$mockUsers = [
    [
        "id" => 101,
        "name" => "นาย สมชาย ใจดี",
        "houseNo" => "111",
        "village" => "1",
        "year" => 2569,
        // สถานะแต่ละเดือน: 0=ค้าง, 1=จ่ายแล้ว, 2=รอตรวจสอบ
        "months" => [1, 1, 1, 2, 0, 0, 0, 0, 0, 0, 0, 0] 
    ],
    [
        "id" => 102,
        "name" => "นาง สมหญิง รักสะอาด",
        "houseNo" => "111/2",
        "village" => "1",
        "year" => 2569,
        "months" => [1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0]
    ]
];

$action = $_GET['action'] ?? '';

// 1. Search Logic
if ($action === 'search') {
    $keyword = $_GET['houseNo'] ?? '';
    $results = [];
    
    // กรองข้อมูล (จำลอง query)
    foreach ($mockUsers as $user) {
        if (strpos($user['houseNo'], $keyword) !== false) {
            $results[] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'houseNo' => $user['houseNo'],
                'village' => $user['village'],
                'amount' => 480 // ยอดสมมติเพื่อแสดงหน้า List
            ];
        }
    }
    echo json_encode(['status' => 'success', 'data' => $results]);
    exit;
}

// 2. Get Detail Logic (สำหรับหน้า Payment)
if ($action === 'get_detail') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    foreach ($mockUsers as $user) {
        if ($user['id'] === $id) {
            // *** Security: เก็บ ID ลง Session เพื่อยืนยันตัวตนในขั้นตอนต่อไป ***
            $_SESSION['current_user_id'] = $id;
            $_SESSION['current_user_data'] = $user;
            
            echo json_encode(['status' => 'success', 'data' => $user]);
            exit;
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

// 3. Confirm Amount Logic (รับยอดเงินก่อนไปหน้า Upload)
if ($action === 'confirm_payment') {
    // รับ JSON จากหน้าบ้าน
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['total']) && isset($input['months'])) {
        // บันทึกลง Session ห้ามแก้ไข
        $_SESSION['payment_summary'] = [
            'total' => $input['total'],
            'months' => $input['months'],
            'phone' => $input['phone']
        ];
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>