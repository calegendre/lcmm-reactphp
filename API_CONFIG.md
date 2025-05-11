# Legendre Cloud Media Manager API Configuration

This file contains installation and configuration guidance for integrating with the Sonarr and Radarr APIs.

## API Connection Details

These are the API details you'll be using to connect to your services:

### Sonarr API
- URL: http://plex.legendre.cloud:8989
- API Key: bae6e0f4548846e3b71290ce6817d081
- Authentication: Basic auth
  - Username: admin
  - Password: Downloader2023*

### Radarr API
- URL: http://plex.legendre.cloud:7878
- API Key: 0d3448b9b1364cfeadbbab6fc50d966e
- Authentication: Basic auth
  - Username: admin
  - Password: Downloader2023*

## MySQL Database
- Database Name: legendrecloud_lcmm
- Username: legendrecloud_lcmmuser
- Password: Royal&Downloader*2025*

## Admin Access
- Email: cl@legendremedia.com
- Password: X9k#vP2$mL8qZ3nT

## Configuration Changes

If you need to modify these settings, update the `/config.php` file in your web root:

```php
<?php
// Database configuration
define('DB_HOST', 'localhost');  // Change if your MySQL server is on a different host
define('DB_NAME', 'legendrecloud_lcmm');
define('DB_USER', 'legendrecloud_lcmmuser');
define('DB_PASS', 'Royal&Downloader*2025*');

// API configuration
define('SONARR_URL', 'http://plex.legendre.cloud:8989');
define('SONARR_API_KEY', 'bae6e0f4548846e3b71290ce6817d081');
define('SONARR_AUTH_USER', 'admin');
define('SONARR_AUTH_PASS', 'Downloader2023*');

define('RADARR_URL', 'http://plex.legendre.cloud:7878');
define('RADARR_API_KEY', '0d3448b9b1364cfeadbbab6fc50d966e');
define('RADARR_AUTH_USER', 'admin');
define('RADARR_AUTH_PASS', 'Downloader2023*');

// Application settings
define('ADMIN_EMAIL', 'cl@legendremedia.com');
define('ADMIN_PASSWORD', 'X9k#vP2$mL8qZ3nT');
define('APP_SECRET', 'legendrecloud_secure_key_2025'); // For session security
```