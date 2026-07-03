<?php
// Authentication Class

class Auth {
    private $conn;
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
    }
    
    // Admin Login
    public function adminLogin($username, $password) {
        $username = sanitizeInput($username);
        $password = sanitizeInput($password);
        
        if (empty($username) || empty($password)) {
            return ['status' => false, 'message' => 'Username and password are required'];
        }
        
        $query = "SELECT id, fullname, email, password, role, status FROM admins WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['status' => false, 'message' => 'Invalid username or password'];
        }
        
        $admin = $result->fetch_assoc();
        
        if ($admin['status'] === 'Inactive') {
            return ['status' => false, 'message' => 'Your account is inactive'];
        }
        
        if (!verifyPassword($password, $admin['password'])) {
            return ['status' => false, 'message' => 'Invalid username or password'];
        }
        
        // Update last login
        $update_query = "UPDATE admins SET last_login = NOW() WHERE id = ?";
        $update_stmt = $this->conn->prepare($update_query);
        $update_stmt->bind_param('i', $admin['id']);
        $update_stmt->execute();
        
        // Set session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_fullname'] = $admin['fullname'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['login_time'] = time();
        
        // Log audit
        logAudit($this->conn, $admin['id'], 'Admin Login', 'admins', $admin['id']);
        
        return ['status' => true, 'message' => 'Login successful', 'admin' => $admin];
    }
    
    // Customer Login
    public function customerLogin($username, $password) {
        $username = sanitizeInput($username);
        $password = sanitizeInput($password);
        
        if (empty($username) || empty($password)) {
            return ['status' => false, 'message' => 'Username and password are required'];
        }
        
        $query = "SELECT id, customer_id, fullname, email, phone, status FROM customers WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['status' => false, 'message' => 'Invalid username or password'];
        }
        
        $customer = $result->fetch_assoc();
        
        if ($customer['status'] !== 'Active') {
            return ['status' => false, 'message' => 'Your account is ' . strtolower($customer['status'])];
        }
        
        if (!verifyPassword($password, $customer['password'])) {
            return ['status' => false, 'message' => 'Invalid username or password'];
        }
        
        // Set session
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['customer_username'] = $customer['username'];
        $_SESSION['customer_fullname'] = $customer['fullname'];
        $_SESSION['customer_email'] = $customer['email'];
        $_SESSION['customer_phone'] = $customer['phone'];
        $_SESSION['login_time'] = time();
        $_SESSION['user_type'] = 'customer';
        
        return ['status' => true, 'message' => 'Login successful', 'customer' => $customer];
    }
    
    // Register Admin
    public function registerAdmin($fullname, $username, $email, $password, $role) {
        $fullname = sanitizeInput($fullname);
        $username = sanitizeInput($username);
        $email = sanitizeInput($email);
        $role = sanitizeInput($role);
        
        // Validation
        if (empty($fullname) || empty($username) || empty($email) || empty($password) || empty($role)) {
            return ['status' => false, 'message' => 'All fields are required'];
        }
        
        if (!validateEmail($email)) {
            return ['status' => false, 'message' => 'Invalid email format'];
        }
        
        if (strlen($password) < 6) {
            return ['status' => false, 'message' => 'Password must be at least 6 characters'];
        }
        
        // Check if username exists
        $check_query = "SELECT id FROM admins WHERE username = ? OR email = ?";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bind_param('ss', $username, $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            return ['status' => false, 'message' => 'Username or email already exists'];
        }
        
        // Hash password
        $hashed_password = hashPassword($password);
        
        // Insert admin
        $insert_query = "INSERT INTO admins (fullname, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, 'Active')";
        $insert_stmt = $this->conn->prepare($insert_query);
        $insert_stmt->bind_param('sssss', $fullname, $username, $email, $hashed_password, $role);
        
        if ($insert_stmt->execute()) {
            return ['status' => true, 'message' => 'Admin registered successfully', 'admin_id' => $this->conn->insert_id];
        } else {
            return ['status' => false, 'message' => 'Registration failed: ' . $this->conn->error];
        }
    }
    
    // Register Customer
    public function registerCustomer($fullname, $email, $phone, $address, $package_id, $username, $password) {
        $fullname = sanitizeInput($fullname);
        $email = sanitizeInput($email);
        $phone = sanitizeInput($phone);
        $address = sanitizeInput($address);
        $username = sanitizeInput($username);
        
        // Validation
        if (empty($fullname) || empty($email) || empty($phone) || empty($address) || empty($username) || empty($password)) {
            return ['status' => false, 'message' => 'All fields are required'];
        }
        
        if (!validateEmail($email)) {
            return ['status' => false, 'message' => 'Invalid email format'];
        }
        
        if (!validatePhone($phone)) {
            return ['status' => false, 'message' => 'Invalid phone number'];
        }
        
        if (strlen($password) < 6) {
            return ['status' => false, 'message' => 'Password must be at least 6 characters'];
        }
        
        // Check if username exists
        $check_query = "SELECT id FROM customers WHERE username = ? OR email = ?";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bind_param('ss', $username, $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            return ['status' => false, 'message' => 'Username or email already exists'];
        }
        
        // Check if package exists
        $package_query = "SELECT id FROM packages WHERE id = ? AND status = 'Active'";
        $package_stmt = $this->conn->prepare($package_query);
        $package_stmt->bind_param('i', $package_id);
        $package_stmt->execute();
        
        if ($package_stmt->get_result()->num_rows === 0) {
            return ['status' => false, 'message' => 'Invalid package selected'];
        }
        
        // Generate customer ID
        $customer_id = generateCustomerId();
        
        // Hash password
        $hashed_password = hashPassword($password);
        
        // Insert customer
        $insert_query = "INSERT INTO customers (customer_id, fullname, email, phone, address, package_id, username, password, status, registration_date) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active', NOW())";
        $insert_stmt = $this->conn->prepare($insert_query);
        $insert_stmt->bind_param('ssssisis', $customer_id, $fullname, $email, $phone, $address, $package_id, $username, $hashed_password);
        
        if ($insert_stmt->execute()) {
            return ['status' => true, 'message' => 'Registration successful', 'customer_id' => $customer_id];
        } else {
            return ['status' => false, 'message' => 'Registration failed: ' . $this->conn->error];
        }
    }
    
    // Logout
    public function logout() {
        if (isset($_SESSION['admin_id'])) {
            logAudit(DB_CONNECTION, $_SESSION['admin_id'], 'Admin Logout', 'admins', $_SESSION['admin_id']);
        }
        
        session_unset();
        session_destroy();
        return ['status' => true, 'message' => 'Logout successful'];
    }
}
?>