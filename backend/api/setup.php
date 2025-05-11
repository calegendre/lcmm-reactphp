
<?php
// setup.php - One-time setup script for the application
require_once '../config.php';

// This script should only be run once to set up the database
// It will create the tables and insert the admin user

// For security, this script requires a setup token
$setup_token = $_GET['token'] ?? '';

if (empty($setup_token) || $setup_token !== 'setup_' . hash('sha256', APP_SECRET)) {
    send_json_response(['error' => 'Invalid setup token'], 403);
    exit;
}

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    send_json_response(['error' => 'Database connection failed: ' . $conn->connect_error], 500);
    exit;
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if (!$conn->query($sql)) {
    send_json_response(['error' => 'Error creating database: ' . $conn->error], 500);
    exit;
}

// Select the database
$conn->select_db(DB_NAME);

// Create users table
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    send_json_response(['error' => 'Error creating users table: ' . $conn->error], 500);
    exit;
}

// Create invitations table
$sql = "
CREATE TABLE IF NOT EXISTS invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(32) NOT NULL UNIQUE,
    email VARCHAR(255),
    created_by INT NOT NULL,
    used BOOLEAN DEFAULT 0,
    used_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (used_by) REFERENCES users(id)
)";

if (!$conn->query($sql)) {
    send_json_response(['error' => 'Error creating invitations table: ' . $conn->error], 500);
    exit;
}

// Create activity_logs table
$sql = "
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if (!$conn->query($sql)) {
    send_json_response(['error' => 'Error creating activity_logs table: ' . $conn->error], 500);
    exit;
}

// Check if admin user already exists
$result = $conn->query("SELECT id FROM users WHERE email = '" . ADMIN_EMAIL . "'");

if ($result->num_rows === 0) {
    // Insert admin user
    $hashed_password = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (email, password, role) VALUES ('" . ADMIN_EMAIL . "', '" . $hashed_password . "', 'admin')";
    
    if (!$conn->query($sql)) {
        send_json_response(['error' => 'Error creating admin user: ' . $conn->error], 500);
        exit;
    }
}

send_json_response(['message' => 'Setup completed successfully']);
