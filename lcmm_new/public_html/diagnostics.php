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

// Sonarr/Radarr configuration
define('SONARR_URL', 'http://plex.legendre.cloud:8989');
define('SONARR_API_KEY', 'bae6e0f4548846e3b71290ce6817d081');
define('SONARR_AUTH_USER', 'admin');
define('SONARR_AUTH_PASS', 'Downloader2023*');

define('RADARR_URL', 'http://plex.legendre.cloud:7878');
define('RADARR_API_KEY', '0d3448b9b1364cfeadbbab6fc50d966e');
define('RADARR_AUTH_USER', 'admin');
define('RADARR_AUTH_PASS', 'Downloader2023*');

echo "<html><head><title>LCMM Diagnostics</title>";
echo "<style>
body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; background-color: #f0f0f0; }
.card { background-color: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
h1, h2 { color: #333; }
h1 { border-bottom: 2px solid #eee; padding-bottom: 10px; }
.success { color: green; }
.error { color: red; }
.test-group { margin-bottom: 30px; }
pre { background-color: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style></head><body>";
echo "<h1>LCMM Diagnostics Tool</h1>";

// Database Tests
echo "<div class='card test-group'>";
echo "<h2>Database Connection Test</h2>";

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        echo "<p class='error'>❌ Connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p class='success'>✅ Database connection successful!</p>";
        
        // Check tables
        $tables = ['users', 'invitations', 'activity_logs'];
        $missing_tables = [];
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                echo "<p class='success'>✅ Table '$table' exists</p>";
                
                // Get record count
                $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
                $count = $count_result->fetch_assoc()['count'];
                echo "<p>- Records: $count</p>";
            } else {
                echo "<p class='error'>❌ Table '$table' is missing</p>";
                $missing_tables[] = $table;
            }
        }
        
        // Check admin user
        $result = $conn->query("SELECT id, email, role FROM users WHERE email = 'cl@legendremedia.com'");
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<p class='success'>✅ Admin user found (ID: {$user['id']}, Role: {$user['role']})</p>";
            
            // Test password
            $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
            $email = 'cl@legendremedia.com';
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $hash = $result->fetch_assoc()['password'];
            
            $test_password = 'X9k#vP2$mL8qZ3nT';
            if (password_verify($test_password, $hash)) {
                echo "<p class='success'>✅ Password verification successful</p>";
            } else {
                echo "<p class='error'>❌ Password verification failed</p>";
                echo "<p>Current hash: " . substr($hash, 0, 20) . "...</p>";
                
                $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
                echo "<p>New hash: " . substr($new_hash, 0, 20) . "...</p>";
                
                echo "<form method='post' action=''>";
                echo "<input type='hidden' name='action' value='fix_password'>";
                echo "<button type='submit'>Fix Password Hash</button>";
                echo "</form>";
            }
        } else {
            echo "<p class='error'>❌ Admin user not found</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Exception: " . $e->getMessage() . "</p>";
}

echo "</div>";

// API Tests
echo "<div class='card test-group'>";
echo "<h2>API Connection Tests</h2>";

// Test Sonarr connection
echo "<h3>Sonarr API Test</h3>";
$sonarr_url = SONARR_URL . '/api/v3/system/status';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $sonarr_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Api-Key: ' . SONARR_API_KEY]);
curl_setopt($ch, CURLOPT_USERPWD, SONARR_AUTH_USER . ':' . SONARR_AUTH_PASS);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code == 200) {
    echo "<p class='success'>✅ Successfully connected to Sonarr API</p>";
    $data = json_decode($response, true);
    echo "<p>Sonarr version: " . ($data['version'] ?? 'Unknown') . "</p>";
} else {
    echo "<p class='error'>❌ Failed to connect to Sonarr API (HTTP code: $http_code)</p>";
    if ($curl_error) {
        echo "<p>Error: $curl_error</p>";
    }
    echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";
}

// Test Radarr connection
echo "<h3>Radarr API Test</h3>";
$radarr_url = RADARR_URL . '/api/v3/system/status';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $radarr_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Api-Key: ' . RADARR_API_KEY]);
curl_setopt($ch, CURLOPT_USERPWD, RADARR_AUTH_USER . ':' . RADARR_AUTH_PASS);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code == 200) {
    echo "<p class='success'>✅ Successfully connected to Radarr API</p>";
    $data = json_decode($response, true);
    echo "<p>Radarr version: " . ($data['version'] ?? 'Unknown') . "</p>";
} else {
    echo "<p class='error'>❌ Failed to connect to Radarr API (HTTP code: $http_code)</p>";
    if ($curl_error) {
        echo "<p>Error: $curl_error</p>";
    }
    echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";
}

echo "</div>";

// File checks
echo "<div class='card test-group'>";
echo "<h2>File System Checks</h2>";

// Check API files
$api_files = [
    '/api/auth.php', 
    '/api/sonarr.php', 
    '/api/radarr.php',
    '/api/users.php',
    '/api/invitations.php',
    '/api/setup.php'
];

foreach ($api_files as $file) {
    $full_path = __DIR__ . $file;
    if (file_exists($full_path)) {
        echo "<p class='success'>✅ File exists: $file</p>";
    } else {
        echo "<p class='error'>❌ File missing: $file</p>";
    }
}

// Check .htaccess
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "<p class='success'>✅ .htaccess file exists</p>";
} else {
    echo "<p class='error'>❌ .htaccess file is missing</p>";
}

echo "</div>";

// Server information
echo "<div class='card test-group'>";
echo "<h2>Server Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Server Name: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Current Script: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Check PHP extensions
$required_extensions = ['mysqli', 'curl', 'mbstring', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ PHP extension loaded: $ext</p>";
    } else {
        echo "<p class='error'>❌ PHP extension not loaded: $ext</p>";
    }
}

echo "</div>";

// Process form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'fix_password') {
        echo "<div class='card'>";
        echo "<h2>Action Result: Fix Password</h2>";
        
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                echo "<p class='error'>❌ Connection failed: " . $conn->connect_error . "</p>";
            } else {
                $password = 'X9k#vP2$mL8qZ3nT';
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $email = 'cl@legendremedia.com';
                $stmt->bind_param("ss", $hashed_password, $email);
                
                if ($stmt->execute()) {
                    echo "<p class='success'>✅ Password hash updated successfully!</p>";
                    echo "<p>You can now log in with:</p>";
                    echo "<p>Email: cl@legendremedia.com</p>";
                    echo "<p>Password: X9k#vP2$mL8qZ3nT</p>";
                } else {
                    echo "<p class='error'>❌ Failed to update password: " . $conn->error . "</p>";
                }
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Exception: " . $e->getMessage() . "</p>";
        }
        
        echo "</div>";
    }
}

echo "</body></html>";
?>