# LCMM Deployment Instructions

This package contains a fixed, standalone version of the Legendre Cloud Media Manager application that is configured for the domain `lcmm.legendre.cloud`.

## Quick Deployment Steps

1. **Upload Files**
   - Upload all files from the `public_html` directory to your web root: 
     `/home/legendrecloud/web/lcmm.legendre.cloud/public_html`

2. **Set Permissions**
   ```bash
   chmod 755 /home/legendrecloud/web/lcmm.legendre.cloud/public_html
   chmod 644 /home/legendrecloud/web/lcmm.legendre.cloud/public_html/.htaccess
   ```

3. **Import Database**
   - Import the SQL file via phpMyAdmin or command line:
   ```bash
   mysql -u legendrecloud_lcmmuser -p legendrecloud_lcmm < lcmm_setup.sql
   ```

4. **Access the Application**
   - Visit `https://lcmm.legendre.cloud`
   - Log in with:
     - Email: cl@legendremedia.com
     - Password: X9k#vP2$mL8qZ3nT

## About This Build

### Fixes Applied
- All API endpoints are standalone PHP files that don't rely on includes
- All files correctly point to the domain `lcmm.legendre.cloud`
- Fixed authentication and database connectivity
- Added proper CORS headers for API communication
- Added .htaccess file with correct rewrites

### File Structure
- `public_html/` - Main web directory
  - `api/` - Backend PHP API files
  - `static/` - Compiled frontend assets
  - `.htaccess` - Apache configuration
  - `config.js` - Frontend configuration
  - `index.html` - Main entry point

### Database
- MySQL database: `legendrecloud_lcmm`
- Username: `legendrecloud_lcmmuser`
- Password: `Royal&Downloader*2025*`

### Customization
- Add your logo at `/public_html/logo.png`
- Add favicon and PWA icons:
  - `/public_html/favicon.ico` (64x64)
  - `/public_html/logo192.png` (192x192)
  - `/public_html/logo512.png` (512x512)

## Troubleshooting

If you encounter issues:

1. **Check Permissions**
   Make sure PHP files are readable by the web server.

2. **Verify .htaccess**
   Ensure mod_rewrite is enabled in Apache:
   ```bash
   sudo a2enmod rewrite
   sudo service apache2 restart
   ```

3. **Database Connection**
   If the database connection fails, verify your credentials in all API files.

4. **API Errors**
   If you see "Failed to load library" errors, check:
   - Network access to Sonarr/Radarr from your server
   - Sonarr/Radarr API keys and authentication in the API files

5. **Clear Browser Cache**
   After deployment, clear your browser cache or use private/incognito mode.

## Security Note
After verifying everything works, consider removing `/api/setup.php` for security.