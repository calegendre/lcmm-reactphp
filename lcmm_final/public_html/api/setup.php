<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'legendrecloud_lcmm');
define('DB_USER', 'legendrecloud_lcmmuser');
define('DB_PASS', 'Royal&Downloader*2025*');

// API response helpers
function send_json_response($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data);
    exit();
}

// Main setup script
try {
    // Create database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        send_json_response(['error' => 'Database connection failed: ' . $conn->connect_error], 500);
    }
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if (!$conn->query($sql)) {
        send_json_response(['error' => 'Error creating database: ' . $conn->error], 500);
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
    }
    
    // Check if admin user already exists
    $result = $conn->query("SELECT id FROM users WHERE email = 'cl@legendremedia.com'");
    
    if ($result->num_rows === 0) {
        // Create admin user with password hash
        $password = 'X9k#vP2$mL8qZ3nT';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password, role) VALUES ('cl@legendremedia.com', '$hashed_password', 'admin')";
        
        if (!$conn->query($sql)) {
            send_json_response(['error' => 'Error creating admin user: ' . $conn->error], 500);
        }
        
        // Create initial invitation code
        $admin_id = $conn->insert_id;
        $invitation_code = bin2hex(random_bytes(10));
        $sql = "INSERT INTO invitations (code, created_by) VALUES ('$invitation_code', $admin_id)";
        
        if (!$conn->query($sql)) {
            send_json_response(['error' => 'Error creating initial invitation: ' . $conn->error], 500);
        }
        
        send_json_response([
            'message' => 'Setup completed successfully',
            'admin_email' => 'cl@legendremedia.com',
            'admin_password' => $password,
            'initial_invitation_code' => $invitation_code
        ]);
    } else {
        send_json_response([
            'message' => 'Setup already completed',
            'admin_email' => 'cl@legendremedia.com'
        ]);
    }
    
} catch (Exception $e) {
    send_json_response(['error' => 'Setup error: ' . $e->getMessage()], 500);
}
?>