CREATE DATABASE rrr_network;

USE rrr_network;

-- Admins Table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Super Admin','Admin','Operator','Technician') DEFAULT 'Operator',
    phone VARCHAR(20),
    status ENUM('Active','Inactive') DEFAULT 'Active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customers Table
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id VARCHAR(20) UNIQUE NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    package_id INT NOT NULL,
    username VARCHAR(50),
    password VARCHAR(255),
    connection_type ENUM('PPPoE','Static IP') DEFAULT 'PPPoE',
    pppoe_username VARCHAR(100),
    pppoe_password VARCHAR(255),
    ip_address VARCHAR(50),
    mac_address VARCHAR(50),
    status ENUM('Active','Inactive','Suspended','Disconnected') DEFAULT 'Active',
    registration_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id)
);

-- Packages Table
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_name VARCHAR(100) NOT NULL,
    speed VARCHAR(50) NOT NULL,
    upload_speed VARCHAR(50),
    download_speed VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Subscriptions Table
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    package_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    renewal_date DATE,
    billing_cycle ENUM('Monthly','Quarterly','Yearly') DEFAULT 'Monthly',
    status ENUM('Active','Paused','Cancelled') DEFAULT 'Active',
    auto_renewal BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (package_id) REFERENCES packages(id)
);

-- Billing Table
CREATE TABLE billing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    subscription_id INT NOT NULL,
    billing_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('Pending','Paid','Overdue','Cancelled') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id)
);

-- Invoices Table
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    billing_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    tax DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('Draft','Sent','Paid','Overdue','Cancelled') DEFAULT 'Draft',
    payment_status ENUM('Unpaid','Partial','Paid') DEFAULT 'Unpaid',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (billing_id) REFERENCES billing(id)
);

-- Payments Table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    customer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('bKash','Nagad','Rocket','Bank Transfer','Cash','Cheque') NOT NULL,
    payment_gateway_id VARCHAR(100),
    transaction_id VARCHAR(100) UNIQUE,
    payment_date DATETIME NOT NULL,
    status ENUM('Pending','Completed','Failed','Refunded') DEFAULT 'Pending',
    reference_number VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- SMS Logs Table
CREATE TABLE sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    phone_number VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    sms_type ENUM('Invoice','Payment Reminder','Disconnection Warning','Activation','Deactivation','Custom') DEFAULT 'Custom',
    status ENUM('Queued','Sent','Failed','Delivered') DEFAULT 'Queued',
    gateway_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Expenses Table
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    receipt_file VARCHAR(255),
    approved_by INT,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (approved_by) REFERENCES admins(id)
);

-- Reports Table
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(100) NOT NULL,
    generated_by INT NOT NULL,
    report_data LONGTEXT,
    report_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES admins(id)
);

-- Settings Table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    setting_type VARCHAR(50),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- MikroTik Profiles Table
CREATE TABLE mikrotik_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_name VARCHAR(100) NOT NULL,
    max_limit_up VARCHAR(50),
    max_limit_down VARCHAR(50),
    burst_limit_up VARCHAR(50),
    burst_limit_down VARCHAR(50),
    burst_time VARCHAR(50),
    burst_on_login BOOLEAN DEFAULT FALSE,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- MikroTik Connections Table
CREATE TABLE mikrotik_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    profile_id INT NOT NULL,
    pppoe_username VARCHAR(100),
    pppoe_password VARCHAR(255),
    remote_address VARCHAR(50),
    uptime VARCHAR(50),
    rx_packets BIGINT,
    tx_packets BIGINT,
    rx_bytes BIGINT,
    tx_bytes BIGINT,
    last_sync DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (profile_id) REFERENCES mikrotik_profiles(id)
);

-- Audit Logs Table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(255) NOT NULL,
    affected_table VARCHAR(100),
    affected_record_id INT,
    old_values LONGTEXT,
    new_values LONGTEXT,
    ip_address VARCHAR(50),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);

-- Create Indexes for better performance
CREATE INDEX idx_customer_status ON customers(status);
CREATE INDEX idx_customer_phone ON customers(phone);
CREATE INDEX idx_billing_status ON billing(status);
CREATE INDEX idx_billing_customer ON billing(customer_id);
CREATE INDEX idx_invoice_status ON invoices(status);
CREATE INDEX idx_invoice_customer ON invoices(customer_id);
CREATE INDEX idx_payment_status ON payments(status);
CREATE INDEX idx_payment_customer ON payments(customer_id);
CREATE INDEX idx_subscription_customer ON subscriptions(customer_id);
CREATE INDEX idx_sms_status ON sms_logs(status);
CREATE INDEX idx_expense_status ON expenses(status);
