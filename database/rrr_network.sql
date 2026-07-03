CREATE DATABASE rrr_network;

USE rrr_network;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100),
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role ENUM('Super Admin','Admin','Operator','Technician'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id VARCHAR(20),
    fullname VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    package VARCHAR(50),
    username VARCHAR(50),
    password VARCHAR(100),
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_name VARCHAR(50),
    speed VARCHAR(20),
    price DECIMAL(10,2)
);