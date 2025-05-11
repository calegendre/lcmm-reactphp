#!/bin/bash

# Build script for LCMM
echo "=== LCMM Build Script ==="
echo "This script will prepare the application for deployment to your server."

# Create build directory
BUILD_DIR="lcmm_build"
echo "Creating build directory: $BUILD_DIR"
rm -rf $BUILD_DIR
mkdir -p $BUILD_DIR/public_html
mkdir -p $BUILD_DIR/public_html/api

# Build React frontend
echo "Building React frontend..."
cd frontend
REACT_APP_BACKEND_URL=https://lcmm.legendre.cloud yarn build
cd ..

# Copy backend files
echo "Copying backend files..."
cp -r backend/*.php $BUILD_DIR/public_html/
cp -r backend/api/* $BUILD_DIR/public_html/api/

# Copy frontend build
echo "Copying frontend build..."
cp -r frontend/build/* $BUILD_DIR/public_html/

# Copy config files
echo "Copying configuration files..."
cp frontend/public/config.js $BUILD_DIR/public_html/
cp lcmm_setup.sql $BUILD_DIR/
cp README.md $BUILD_DIR/
cp lcmm_diagnostics.php $BUILD_DIR/public_html/

# Create .htaccess file
echo "Creating .htaccess file..."
cat > $BUILD_DIR/public_html/.htaccess << EOL
# Enable rewrite engine
RewriteEngine On

# If the request is not for a file or directory, redirect to index.html
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.html [QSA,L]

# API routing
RewriteRule ^api/auth$ api/auth.php [L,QSA]
RewriteRule ^api/sonarr$ api/sonarr.php [L,QSA]
RewriteRule ^api/radarr$ api/radarr.php [L,QSA]
RewriteRule ^api/users$ api/users.php [L,QSA]
RewriteRule ^api/invitations$ api/invitations.php [L,QSA]

# Set security headers
<IfModule mod_headers.c>
  # Prevent MIME type sniffing
  Header set X-Content-Type-Options "nosniff"
  
  # Clickjacking protection
  Header set X-Frame-Options "SAMEORIGIN"
  
  # XSS protection
  Header set X-XSS-Protection "1; mode=block"
  
  # HSTS (uncomment to enable - requires SSL)
  # Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
  
  # Content Security Policy (adjust as needed)
  Header set Content-Security-Policy "default-src 'self'; connect-src 'self' http://plex.legendre.cloud:7878 http://plex.legendre.cloud:8989; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline';"
</IfModule>

# Enable compression
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript application/json
</IfModule>

# Set caching
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/jpg "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/gif "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
  ExpiresByType text/css "access plus 1 month"
  ExpiresByType application/javascript "access plus 1 month"
  ExpiresByType application/x-javascript "access plus 1 month"
  ExpiresByType image/x-icon "access plus 1 year"
  ExpiresDefault "access plus 2 days"
</IfModule>

# PHP settings
<IfModule mod_php8.c>
  php_value upload_max_filesize 10M
  php_value post_max_size 20M
  php_value memory_limit 256M
  php_value max_execution_time 300
  php_flag session.cookie_httponly on
</IfModule>
EOL

# Create a placeholder for readme
echo "Creating placeholder files..."
cat > $BUILD_DIR/public_html/placeholder.html << EOL
<!DOCTYPE html>
<html>
<head>
    <title>LCMM - Logo Placeholder</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .box { border: 2px dashed #ccc; padding: 20px; margin: 20px 0; text-align: center; }
    </style>
</head>
<body>
    <h1>LCMM Logo and Icon Placement</h1>
    <p>To customize the appearance of your application, place the following files in this directory:</p>
    
    <div class="box">
        <h2>logo.png</h2>
        <p>Main application logo - will appear in the header. Recommended size: 180px × 60px</p>
    </div>
    
    <div class="box">
        <h2>favicon.ico</h2>
        <p>Browser tab icon. Recommended size: 64x64 pixels</p>
    </div>
    
    <div class="box">
        <h2>logo192.png</h2>
        <p>Small PWA icon. Required size: 192x192 pixels</p>
    </div>
    
    <div class="box">
        <h2>logo512.png</h2>
        <p>Large PWA icon. Required size: 512x512 pixels</p>
    </div>
    
    <p>After placing these files, you can safely delete this placeholder file.</p>
</body>
</html>
EOL

# Create a zip file
echo "Creating zip archive..."
cd $BUILD_DIR
zip -r ../lcmm_deployment.zip *
cd ..

echo "Build completed!"
echo "Your deployment package is ready at: lcmm_deployment.zip"
echo "Upload the contents of this zip file to your server and follow the setup instructions in README.md"