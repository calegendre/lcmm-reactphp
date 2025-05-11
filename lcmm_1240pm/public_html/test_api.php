<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<html><head><title>LCMM API Test</title>";
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
echo "<h1>LCMM API Test Tool</h1>";

// Determine the base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$base_url = $protocol . $_SERVER['HTTP_HOST'];

// Check if we're testing a specific endpoint
$endpoint = $_GET['endpoint'] ?? '';
$action = $_GET['action'] ?? '';
$method = $_GET['method'] ?? 'GET';
$token = $_GET['token'] ?? '';

if ($endpoint) {
    echo "<div class='card'>";
    echo "<h2>Testing API Endpoint: $endpoint ($method)</h2>";
    
    $api_url = "$base_url/api/$endpoint";
    if ($action) {
        $api_url .= "?action=$action";
    }
    
    echo "<p>URL: $api_url</p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = [];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    
    if ($method === 'POST' || $method === 'PUT') {
        $headers[] = 'Content-Type: application/json';
        
        if ($endpoint === 'auth' && $action === 'login') {
            $post_data = json_encode([
                'email' => 'cl@legendremedia.com',
                'password' => 'X9k#vP2$mL8qZ3nT'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            echo "<p>Request Body: $post_data</p>";
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "<h3>Response (HTTP Code: $http_code)</h3>";
    
    if ($curl_error) {
        echo "<p class='error'>cURL Error: $curl_error</p>";
    }
    
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Try to parse JSON
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<h3>Parsed JSON:</h3>";
        echo "<pre>" . print_r($data, true) . "</pre>";
        
        // If this is a login response with a token, show a link to test authenticated endpoints
        if ($endpoint === 'auth' && $action === 'login' && isset($data['token'])) {
            $token = $data['token'];
            echo "<h3>Authentication Success!</h3>";
            echo "<p>You can now test authenticated endpoints:</p>";
            echo "<ul>";
            echo "<li><a href='?endpoint=sonarr&action=series&method=GET&token=$token'>Get Sonarr Series</a></li>";
            echo "<li><a href='?endpoint=radarr&action=movies&method=GET&token=$token'>Get Radarr Movies</a></li>";
            echo "<li><a href='?endpoint=sonarr&action=rootfolders&method=GET&token=$token'>Get Sonarr Root Folders</a></li>";
            echo "<li><a href='?endpoint=radarr&action=rootfolders&method=GET&token=$token'>Get Radarr Root Folders</a></li>";
            echo "</ul>";
        }
    }
    
    echo "</div>";
}

// Show API test options
echo "<div class='card'>";
echo "<h2>Available Tests</h2>";

echo "<h3>Authentication</h3>";
echo "<ul>";
echo "<li><a href='?endpoint=auth&action=login&method=POST'>Login (POST)</a></li>";
echo "</ul>";

if (!empty($token)) {
    echo "<h3>Authenticated Endpoints (using token)</h3>";
    echo "<ul>";
    echo "<li><a href='?endpoint=sonarr&action=series&method=GET&token=$token'>Get Sonarr Series</a></li>";
    echo "<li><a href='?endpoint=radarr&action=movies&method=GET&token=$token'>Get Radarr Movies</a></li>";
    echo "</ul>";
} else {
    echo "<p>Login first to test authenticated endpoints</p>";
}

echo "</div>";

echo "</body></html>";
?>