-- LCMM Database Setup Script

-- Drop tables if they exist (optional, remove these lines if you want to preserve existing data)
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS invitations;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create invitations table
CREATE TABLE invitations (
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
);

-- Create activity_logs table
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert admin user with freshly generated password hash (will work on PHP 8.3)
-- Password is X9k#vP2$mL8qZ3nT
INSERT INTO users (email, password, role) VALUES 
('cl@legendremedia.com', '$2y$10$5MF3OZjpkaLNQJ2lAe6vveSF1dOT0oM3c9tWmf6y2kGqQSfItkpya', 'admin');

-- Create a sample invitation code (owned by admin user created above)
INSERT INTO invitations (code, created_by) VALUES
('initialsetupcode123456789', 1);

-- Add initial log entry
INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES
(1, 'system_setup', 'Initial database setup completed', '127.0.0.1');