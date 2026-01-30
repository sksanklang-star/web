<?php
// api.php - Final Secure Version with CSRF
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// ini_set('session.cookie_secure', 1); // เปิดเมื่อใช้ HTTPS
session_start();

// --- Helper Functions ---
function clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function verifyAdminCSRF($inputToken) {
    if (!isset($_SESSION['csrf_admin_token']) || $inputToken !== $_SESSION['csrf_admin_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Security Error: Invalid CSRF Token']);
        exit;
    }
}

// --- Mock Database (Session Based) ---
if (!isset($_SESSION['mock_db'])) {
    $_SESSION['mock_db'] = [
        [
            "id" => 101, 
            "hash_id" => "8f4a2c91e0", 
            "name" => "นาย สมชาย ใจดี", 
            "houseNo" => "111", 
            "village" => "1", 
            "year" => 2569,
            "months" => [1, 1, 1, 2, 0, 0, 0, 0, 0, 0, 0, 0],
            "slip_image" => "https://via.placeholder.com/400x500?text=Slip+Preview",
            "last_updated" => "15 ม.ค. 69 10:30"
        ],
        [
            "id" => 102, 
            "hash_id" => "c3b1a9d8f7", 
            "name" => "นาง สมหญิง รักสะอาด", 
            "houseNo" => "111/2", 
            "village" => "1", 
            "year" => 2569,
            "months" => [1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0],
            "slip_image" => "",
            "last_updated" => "14 ม.ค. 69 09:15"
        ],
        [
            "id" => 103, 
            "hash_id" => "a1b2c3d4e5", 
            "name" => "บริษัท ร้านค้าเจริญรุ่งเรือง จำกัด", 
            "houseNo" => "112", 
            "village" => "1", 
            "year" => 2569,
            "months" => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            "slip_image" => "",
            "last_updated" => "-"
        ]
    ];
}

$db = &$_SESSION['mock_db'];
$action = $_GET['action'] ?? '';

// ================= USER ACTIONS =================

if ($action === 'search') {
    $keyword = clean($_GET['houseNo'] ?? '');
    $results = [];
    foreach ($db as $user) {
        if ($keyword !== "" && strpos($user['houseNo'], $keyword) !== false) {
            $results[] = [
                'token' => $user['hash_id'],
                'name' => clean($user['name']),
                'houseNo' => clean($user['houseNo']),
                'village' => clean($user['village']),
                'year' => $user['year']
            ];
        }
    }
    echo json_encode(['status' => 'success', 'data' => $results]);
    exit;
}

if ($action === 'get_detail') {
    $token = clean($_GET['token'] ?? '');
    foreach ($db as $user) {
        if ($user['hash_id'] === $token) {
            $_SESSION['current_user_id'] = $user['id'];
            $_SESSION['current_user_data'] = $user;
            $safeUser = $user;
            unset($safeUser['id'], $safeUser['hash_id']);
            echo json_encode(['status' => 'success', 'data' => $safeUser]);
            exit;
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

if ($action === 'confirm_payment') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($_SESSION['current_user_data'])) {
        echo json_encode(['status' => 'error', 'message' => 'Session Timeout', 'redirect' => '/?page=search']);
        exit;
    }

    $userData = $_SESSION['current_user_data'];
    $monthsToPayIdx = $input['months'] ?? [];
    $feePerMonth = 40;
    $realTotal = 0;

    foreach ($monthsToPayIdx as $idx) {
        if (isset($userData['months'][$idx]) && $userData['months'][$idx] === 0) {
            $realTotal += $feePerMonth;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid month selection']);
            exit;
        }
    }

    if ($realTotal <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'No amount to pay']);
        exit;
    }

    $_SESSION['payment_summary'] = [
        'total' => $realTotal,
        'months' => $monthsToPayIdx,
        'phone' => clean($input['phone'] ?? '')
    ];
    
    echo json_encode(['status' => 'success']);
    exit;
}

// ================= ADMIN ACTIONS =================

if ($action === 'admin_login') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check CSRF
    verifyAdminCSRF($input['csrf_token'] ?? '');

    $user = $input['username'] ?? '';
    $pass = $input['password'] ?? '';

    if ($user === 'admin' && $pass === '1234') {
        $_SESSION['admin_logged_in'] = true;
        session_regenerate_id(true); 
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
    }
    exit;
}

if ($action === 'admin_get_all') {
    if (!isset($_SESSION['admin_logged_in'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
    }
    
    $pendingCount = 0;
    foreach ($db as $u) {
        if (in_array(2, $u['months'])) $pendingCount++;
    }
    
    echo json_encode([
        'status' => 'success', 
        'data' => $db, 
        'stats' => ['pending' => $pendingCount]
    ]);
    exit;
}

if ($action === 'admin_update_status') {
    if (!isset($_SESSION['admin_logged_in'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check CSRF
    verifyAdminCSRF($input['csrf_token'] ?? '');

    $targetId = $input['id'];
    $newStatus = $input['status'] === 'approve' ? 1 : 0; 

    foreach ($db as &$user) {
        if ($user['id'] == $targetId) {
            foreach ($user['months'] as $k => $v) {
                if ($v === 2) {
                    $user['months'][$k] = $newStatus;
                }
            }
            $user['last_updated'] = date('d M y H:i'); // Update Timestamp
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}
?>