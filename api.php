<?php
// api.php - (Router Version)
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

session_start();

function clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

$mockUsers = [
    [
        "id" => 101, 
        "hash_id" => "8f4a2c91e0", 
        "name" => "นาย สมชาย ใจดี", 
        "houseNo" => "111", 
        "village" => "1", 
        "year" => 2569,
        "months" => [1, 1, 1, 2, 0, 0, 0, 0, 0, 0, 0, 0] 
    ],
    [
        "id" => 102, 
        "hash_id" => "c3b1a9d8f7", 
        "name" => "นาง สมหญิง รักสะอาด", 
        "houseNo" => "111/2", 
        "village" => "1", 
        "year" => 2569,
        "months" => [1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0]
    ],
    [
        "id" => 103, 
        "hash_id" => "a1b2c3d4e5", 
        "name" => "บริษัท ร้านค้าเจริญรุ่งเรือง จำกัด", 
        "houseNo" => "112", 
        "village" => "1", 
        "year" => 2569,
        "months" => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
    ]
];

$action = $_GET['action'] ?? '';

if ($action === 'search') {
    $keyword = clean($_GET['houseNo'] ?? '');
    $results = [];
    foreach ($mockUsers as $user) {
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
    foreach ($mockUsers as $user) {
        if ($user['hash_id'] === $token) {
            $_SESSION['current_user_id'] = $user['id'];
            $_SESSION['current_user_data'] = $user;
            
            $safeUser = $user;
            unset($safeUser['id']);
            unset($safeUser['hash_id']);
            $safeUser['name'] = clean($user['name']);
            
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
        // *** แก้ตรงนี้: ให้ Redirect ไปหน้าแรกผ่าน Router ***
        echo json_encode(['status' => 'error', 'message' => 'Session Timeout', 'redirect' => '?page=search']);
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
?>