# Legendre Cloud Media Manager (LCMM)

A web application that connects to Sonarr and Radarr to allow users to browse and add media through an invitation-only interface.

## Deployment Instructions

### Prerequisites

- HestiaCP server with:
  - PHP 8.3 or higher
  - MySQL/MariaDB
  - Apache or Nginx
  - mod_rewrite enabled (Apache)

### Setup Steps

1. **Upload the files**
   - Upload all files to your web root directory: `/home/legendrecloud/web/lcmm.legendre.cloud/public_html`

2. **Set permissions**
   - Make sure the following directories and files have the correct permissions:
     ```
     chmod 755 /home/legendrecloud/web/lcmm.legendre.cloud/public_html
     chmod 644 /home/legendrecloud/web/lcmm.legendre.cloud/public_html/.htaccess
     ```

3. **Database Setup**
   - Import the `lcmm_setup.sql` file into your MySQL database:
     ```
     mysql -u legendrecloud_lcmmuser -p legendrecloud_lcmm < lcmm_setup.sql
     ```
   - Or use phpMyAdmin to import it if available in your HestiaCP installation.

4. **Configure Logo and Icons**
   - Place your logo file at `/home/legendrecloud/web/lcmm.legendre.cloud/public_html/logo.png`
   - For PWA support, add the following icons:
     - `/home/legendrecloud/web/lcmm.legendre.cloud/public_html/favicon.ico` (64x64)
     - `/home/legendrecloud/web/lcmm.legendre.cloud/public_html/logo192.png` (192x192)
     - `/home/legendrecloud/web/lcmm.legendre.cloud/public_html/logo512.png` (512x512)

5. **First Login**
   - Log in with the admin credentials:
     - Email: cl@legendremedia.com
     - Password: X9k#vP2$mL8qZ3nT
   - Change your password after first login for security

## Additional Information

### PWA Support

The application includes Progressive Web App (PWA) support, allowing users to add it to their home screen on mobile devices:

- **iOS**: Users will see a banner prompting them to add the app to their home screen
- **Android**: Chrome will show an "Add to Home Screen" option in the browser menu

### Security Considerations

- All API keys are stored securely in the backend
- User passwords are hashed before storage
- The invitation-only system prevents unauthorized access

### Troubleshooting

If you encounter issues:

1. Check PHP error logs: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
2. Verify database connectivity
3. Ensure Sonarr and Radarr API endpoints are accessible from your server