#!/bin/bash

# Fix URLs script for LCMM
# This script will find and replace all instances of the development domain
# with the production domain in your web root

if [ $# -ne 1 ]; then
    echo "Usage: $0 /path/to/web/root"
    exit 1
fi

WEB_ROOT=$1
DEV_DOMAIN="bc550490-db76-49bc-af39-9770ebe41b08.preview.emergentagent.com"
PROD_DOMAIN="lcmm.legendre.cloud"

echo "=== LCMM URL Fixer ==="
echo "Searching for development domain in: $WEB_ROOT"

# Count occurrences first
COUNT=$(grep -r "$DEV_DOMAIN" "$WEB_ROOT" | wc -l)

if [ $COUNT -eq 0 ]; then
    echo "No occurrences found. All good!"
    exit 0
fi

echo "Found $COUNT occurrences. Replacing..."

# Replace in all files
grep -rl "$DEV_DOMAIN" "$WEB_ROOT" | xargs sed -i "s#$DEV_DOMAIN#$PROD_DOMAIN#g"

# Count occurrences again to verify
COUNT_AFTER=$(grep -r "$DEV_DOMAIN" "$WEB_ROOT" | wc -l)

if [ $COUNT_AFTER -eq 0 ]; then
    echo "Success! All occurrences replaced."
else
    echo "Warning: $COUNT_AFTER occurrences still remain. Check your files manually."
fi

echo "Done."
