# LCMM Drop-in Ready Package

This package contains a fully built and configured version of the Legendre Cloud Media Manager (LCMM) application, with all URLs already set to `lcmm.legendre.cloud`.

## Deployment Steps

1. **Upload the files**:
   - Extract `lcmm_ready_to_deploy/public_html` to your web root directory (`/home/legendrecloud/web/lcmm.legendre.cloud/public_html`)
   - Extract `lcmm_ready_to_deploy/lcmm_setup.sql` to a location where you can import it into your MySQL database

2. **Import the database**:
   - Import `lcmm_setup.sql` into your MySQL database:
     ```
     mysql -u legendrecloud_lcmmuser -p legendrecloud_lcmm < lcmm_setup.sql
     ```
   - Or use phpMyAdmin to import it if available in your HestiaCP installation.

3. **Run the diagnostics**:
   - Visit `https://lcmm.legendre.cloud/lcmm_diagnostics.php` in your browser
   - This will automatically check for any issues and fix common problems

4. **Log in**:
   - Visit your domain: `https://lcmm.legendre.cloud`
   - Log in with:
     - Email: cl@legendremedia.com
     - Password: X9k#vP2$mL8qZ3nT

5. **Add your logo and icons**:
   - Place your logo file at `/home/legendrecloud/web/lcmm.legendre.cloud/public_html/logo.png`
   - For PWA support, add the following icons:
     - `/home/legendrecloud/web/lcmm.legendre.cloud/public_html/favicon.ico` (64x64)
     - `/home/legendrecloud/web/lcmm.legendre.cloud/public_html/logo192.png` (192x192)
     - `/home/legendrecloud/web/lcmm.legendre.cloud/public_html/logo512.png` (512x512)

## Troubleshooting

If you encounter any issues:

1. Run the diagnostics script: `https://lcmm.legendre.cloud/lcmm_diagnostics.php`
2. Check PHP error logs in your HestiaCP panel
3. Ensure your domain DNS is properly configured to point to your server
4. Verify database connectivity and credentials in `config.php`

No build scripts or URL fixing is required - this package is ready to deploy as is!