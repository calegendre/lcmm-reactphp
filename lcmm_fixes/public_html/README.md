# LCMM Fixes

This package contains fixes for the LCMM application to address issues with:
1. Header layout and logo placement
2. Login/signup page logo display
3. Fallback images for media items without cover art

## How to Use These Fixes

### 1. Add the JavaScript Fix

Copy `image_fix.js` to your `/public_html/` directory, then add this to your `index.html` right after the title tag:

```html
<script src="/image_fix.js"></script>
```

### 2. Add the CSS Fixes

Copy `fix_custom.css` to your `/public_html/` directory, then add this to your `index.html` in the head section:

```html
<link href="/fix_custom.css" rel="stylesheet">
```

### 3. Ensure Images Are Available

Make sure these images are in your `/public_html/` directory:
- `/public_html/logo.png`
- `/public_html/coverunavailable.png`

Verify they're accessible via:
- `https://lcmm.legendre.cloud/logo.png`
- `https://lcmm.legendre.cloud/coverunavailable.png`

### 4. Component Examples

The following files show the correct structure for each component:
- `fix_header.html` - Correct header/navbar structure
- `fix_login.html` - Correct login/signup page structure
- `fix_media_card.html` - Correct media card structure with fallback image

If the CSS and JavaScript fixes don't solve the issues, you may need to update the component structure directly.

## Additional Troubleshooting

If images still don't appear:
1. Check browser console for errors
2. Verify image paths are correct
3. Make sure the CDN (if used) isn't caching old versions
4. Clear browser cache and reload