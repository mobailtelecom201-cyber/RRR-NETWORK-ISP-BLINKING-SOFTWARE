<?php
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$response = ['status' => false, 'message' => 'Invalid request'];
$action = $_GET['action'] ?? '';

try {
    $auth = new Auth($conn);
    
    switch ($action) {
        case 'admin_login':
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $auth->adminLogin($data['username'] ?? '', $data['password'] ?? '');
            break;
        
        case 'customer_login':
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $auth->customerLogin($data['username'] ?? '', $data['password'] ?? '');
            break;
        
        case 'register_admin':
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $auth->registerAdmin(
                $data['fullname'] ?? '',
                $data['username'] ?? '',
                $data['email'] ?? '',
                $data['password'] ?? '',
                $data['role'] ?? ''
            );
            break;
        
        case 'register_customer':
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $auth->registerCustomer(
                $data['fullname'] ?? '',
                $data['email'] ?? '',
                $data['phone'] ?? '',
                $data['address'] ?? '',
                $data['package_id'] ?? 0,
                $data['username'] ?? '',
                $data['password'] ?? ''
            );
            break;
        
        case 'logout':
            session_start();
            $response = $auth->logout();
            break;
        
        default:
            $response = ['status' => false, 'message' => 'Invalid action'];
    }
} catch (Exception $e) {
    $response = ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
}

echo json_encode($response);
?>