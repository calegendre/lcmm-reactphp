<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials - make sure these match your config.php
$db_host = 'localhost';  // Change if needed
$db_name = 'legendrecloud_lcmm';
$db_user = 'legendrecloud_lcmmuser';
$db_pass = 'Royal&Downloader*2025*';

// Admin credentials
$admin_email = 'cl@legendremedia.com';
$admin_password = 'X9k#vP2$mL8qZ3nT';

echo "<h1>LCMM Login Diagnostics</h1>";

// Step 1: Test Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        echo "<p style='color:red'>❌ Connection failed: " . $conn->connect_error . "</p>";
        echo "<p>Please check your database credentials in config.php</p>";
        exit;
    } else {
        echo "<p style='color:green'>✅ Database connection successful</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Exception: " . $e->getMessage() . "</p>";
    exit;
}

// Step 2: Check if admin user exists
echo "<h2>2. Admin User Check</h2>";
$result = $conn->query("SELECT id, email, password, role FROM users WHERE email = '$admin_email'");

if ($result === false) {
    echo "<p style='color:red'>❌ Query error: " . $conn->error . "</p>";
} elseif ($result->num_rows === 0) {
    echo "<p style='color:red'>❌ Admin user not found in database</p>";
    echo "<p>Try importing the SQL file again or check if you're connecting to the correct database</p>";
} else {
    echo "<p style='color:green'>✅ Admin user found in database</p>";
    
    // Get the stored password hash
    $user = $result->fetch_assoc();
    $stored_hash = $user['password'];
    
    echo "<p>Stored hash: " . htmlspecialchars($stored_hash) . "</p>";
    
    // Step 3: Test password verification
    echo "<h2>3. Password Verification Test</h2>";
    
    // Test using PHP's password_verify function
    if (password_verify($admin_password, $stored_hash)) {
        echo "<p style='color:green'>✅ Password verification successful using password_verify()</p>";
    } else {
        echo "<p style='color:red'>❌ Password verification failed using password_verify()</p>";
        
        // Create a new hash for comparison
        $new_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        echo "<p>Newly generated hash: " . htmlspecialchars($new_hash) . "</p>";
        
        // Update the password in the database
        echo "<h2>4. Password Update</h2>";
        echo "<p>Updating password in database with fresh hash...</p>";
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_hash, $admin_email);
        
        if ($stmt->execute()) {
            echo "<p style='color:green'>✅ Password updated successfully</p>";
            echo "<p>Please try logging in again with:</p>";
            echo "<p>Email: " . htmlspecialchars($admin_email) . "</p>";
            echo "<p>Password: " . htmlspecialchars($admin_password) . "</p>";
        } else {
            echo "<p style='color:red'>❌ Failed to update password: " . $conn->error . "</p>";
        }
    }
}

// Step 5: Check API endpoints
echo "<h2>5. API Configuration Check</h2>";

if (file_exists('config.php')) {
    echo "<p style='color:green'>✅ config.php exists</p>";
} else {
    echo "<p style='color:red'>❌ config.php not found</p>";
}

if (file_exists('api/auth.php')) {
    echo "<p style='color:green'>✅ api/auth.php exists</p>";
} else {
    echo "<p style='color:red'>❌ api/auth.php not found</p>";
}

// Display PHP version info
echo "<h2>System Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Display local time and timezone
echo "<p>Local Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Timezone: " . date_default_timezone_get() . "</p>";

// Close connection
$conn->close();
?>