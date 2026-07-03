<?php
// Application Constants

// App Configuration
define('APP_NAME', 'RRR Network ISP Billing Software');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/RRR-NETWORK-ISP-BLINKING-SOFTWARE');

// Admin Roles
define('ROLE_SUPER_ADMIN', 'Super Admin');
define('ROLE_ADMIN', 'Admin');
define('ROLE_OPERATOR', 'Operator');
define('ROLE_TECHNICIAN', 'Technician');

// Status Constants
define('STATUS_ACTIVE', 'Active');
define('STATUS_INACTIVE', 'Inactive');
define('STATUS_SUSPENDED', 'Suspended');

// Payment Methods
define('PAYMENT_METHOD_BKASH', 'bKash');
define('PAYMENT_METHOD_NAGAD', 'Nagad');
define('PAYMENT_METHOD_ROCKET', 'Rocket');

// API Keys (Replace with actual keys)
define('BKASH_API_KEY', 'YOUR_BKASH_API_KEY');
define('BKASH_API_SECRET', 'YOUR_BKASH_API_SECRET');
define('BKASH_USERNAME', 'YOUR_BKASH_USERNAME');
define('BKASH_PASSWORD', 'YOUR_BKASH_PASSWORD');

define('NAGAD_API_KEY', 'YOUR_NAGAD_API_KEY');
define('NAGAD_API_SECRET', 'YOUR_NAGAD_API_SECRET');

define('ROCKET_API_KEY', 'YOUR_ROCKET_API_KEY');
define('ROCKET_API_SECRET', 'YOUR_ROCKET_API_SECRET');

// SMS Gateway (Replace with actual credentials)
define('SMS_GATEWAY_API', 'YOUR_SMS_GATEWAY_API');
define('SMS_GATEWAY_KEY', 'YOUR_SMS_GATEWAY_KEY');
define('SMS_SENDER_ID', 'RRR_ISP');

// MikroTik Configuration
define('MIKROTIK_HOST', 'YOUR_MIKROTIK_IP');
define('MIKROTIK_USERNAME', 'YOUR_MIKROTIK_USER');
define('MIKROTIK_PASSWORD', 'YOUR_MIKROTIK_PASSWORD');
define('MIKROTIK_PORT', 8728);

// Email Configuration
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_FROM_EMAIL', 'noreply@rrrnetwork.com');
define('MAIL_FROM_NAME', 'RRR Network ISP');
?>