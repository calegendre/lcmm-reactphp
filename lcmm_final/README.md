# Legendre Cloud Media Manager (LCMM)

A standalone, drop-in ready web application that connects to Sonarr and Radarr to allow users to browse and add media through an invitation-only interface.

## Quick Deployment

**Requirements**:
- PHP 8.3+
- MySQL/MariaDB
- Apache with mod_rewrite
- Access to the domain lcmm.legendre.cloud

**Steps**:
1. Upload the `public_html` directory to your web root
2. Import `lcmm_setup.sql` into your MySQL database
3. Log in with the admin account:
   - Email: cl@legendremedia.com
   - Password: X9k#vP2$mL8qZ3nT

For detailed instructions, see the DEPLOYMENT.md file.

## Features

- Beautiful dark-themed UI
- Responsive design (works on mobile and desktop)
- Browse TV shows and movies from Sonarr/Radarr
- Search and add new content
- Invitation-only user system
- Admin dashboard
- Progressive Web App (PWA) support

## Troubleshooting

If you encounter issues:
1. Check the `/diagnostics.php` page
2. Try the `/test_api.php` page for API testing
3. Test login with `/login_test.html`

## Customization

- Add your logo at `/logo.png`
- Add favicon at `/favicon.ico`
- Add PWA icons at `/logo192.png` and `/logo512.png`