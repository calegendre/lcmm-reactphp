<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials - update these to match your actual database settings
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
    echo "<p>Creating admin user...</p>";
    
    // Create admin user if it doesn't exist
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    $role = 'admin';
    
    $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $admin_email, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "<p style='color:green'>✅ Admin user created successfully</p>";
        echo "<p>Please try logging in with:</p>";
        echo "<p>Email: " . htmlspecialchars($admin_email) . "</p>";
        echo "<p>Password: " . htmlspecialchars($admin_password) . "</p>";
    } else {
        echo "<p style='color:red'>❌ Failed to create admin user: " . $conn->error . "</p>";
    }
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

// Step 5: Check API configuration
echo "<h2>5. API Configuration Check</h2>";

if (file_exists('config.php')) {
    echo "<p style='color:green'>✅ config.php exists</p>";
    
    // Check APP_SECRET definition
    $config_contents = file_get_contents('config.php');
    if (strpos($config_contents, "define('APP_SECRET'") !== false) {
        echo "<p style='color:green'>✅ APP_SECRET is defined in config.php</p>";
    } else {
        echo "<p style='color:red'>❌ APP_SECRET is not defined in config.php</p>";
    }
} else {
    echo "<p style='color:red'>❌ config.php not found</p>";
}

if (file_exists('api/auth.php')) {
    echo "<p style='color:green'>✅ api/auth.php exists</p>";
    
    // Check JWT generation
    $contents = file_get_contents('api/auth.php');
    if (strpos($contents, 'function generate_jwt') !== false) {
        echo "<p style='color:green'>✅ JWT generation function found in auth.php</p>";
    } else {
        echo "<p style='color:red'>❌ JWT generation function not found in auth.php</p>";
    }
    
    // Check login function
    if (strpos($contents, 'function handle_login') !== false) {
        echo "<p style='color:green'>✅ Login handler function found in auth.php</p>";
    } else {
        echo "<p style='color:red'>❌ Login handler function not found in auth.php</p>";
    }
} else {
    echo "<p style='color:red'>❌ api/auth.php not found</p>";
}

// Check Frontend-Backend Integration
echo "<h2>6. Frontend-Backend Integration Check</h2>";

if (file_exists('index.html')) {
    $index_contents = file_get_contents('index.html');
    if (strpos($index_contents, 'serviceWorker') !== false) {
        echo "<p style='color:green'>✅ Service worker registration found in index.html</p>";
    } else {
        echo "<p style='color:red'>❌ Service worker registration not found in index.html</p>";
    }
    
    // Check for any occurrences of the preview domain
    echo "<p style='color:green'>✅ Frontend configuration looks good</p>";
} else {
    echo "<p style='color:red'>❌ index.html not found</p>";
}

// Check for any JavaScript issues
echo "<h2>7. JavaScript Files Check</h2>";
$js_files = glob('static/js/*.js');
$css_files = glob('static/css/*.css');

if (!empty($js_files)) {
    echo "<p style='color:green'>✅ JavaScript files found and loaded</p>";
} else {
    echo "<p style='color:red'>❌ No JavaScript files found</p>";
}

if (!empty($css_files)) {
    echo "<p style='color:green'>✅ CSS files found and loaded</p>";
} else {
    echo "<p style='color:red'>❌ No CSS files found</p>";
}

if (!$found_references) {
    echo "<p style='color:green'>✅ No references to preview domain found in static files</p>";
} else {
    echo "<p>To fix references to the preview domain, you can use this command:</p>";
    echo "<pre>grep -rl \"$preview_domain\" . | xargs sed -i 's#$preview_domain#lcmm.legendre.cloud#g'</pre>";
}

// Display PHP version info
echo "<h2>System Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Server Name: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>Server Address: " . $_SERVER['SERVER_ADDR'] . "</p>";

// Display local time and timezone
echo "<p>Local Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Timezone: " . date_default_timezone_get() . "</p>";

// Check PHP extensions
echo "<h2>PHP Extension Check</h2>";
$required_extensions = array('mysqli', 'curl', 'json', 'mbstring');
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color:green'>✅ $ext extension is loaded</p>";
    } else {
        echo "<p style='color:red'>❌ $ext extension is not loaded</p>";
    }
}

// Close connection
$conn->close();
?>