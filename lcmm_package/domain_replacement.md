# Domain Replacement Instructions

If you discover any references to the development domain `bc550490-db76-49bc-af39-9770ebe41b08.preview.emergentagent.com` in your built files, you can replace them all at once using the following commands:

## Find all occurrences

```bash
grep -r "bc550490-db76-49bc-af39-9770ebe41b08.preview.emergentagent.com" /path/to/web/root
```

## Replace all occurrences

```bash
grep -rl "bc550490-db76-49bc-af39-9770ebe41b08.preview.emergentagent.com" /path/to/web/root | xargs sed -i 's/bc550490-db76-49bc-af39-9770ebe41b08.preview.emergentagent.com/lcmm.legendre.cloud/g'
```

This command will:
1. Find all files containing the development domain
2. Replace all instances with your production domain (lcmm.legendre.cloud)

## Alternative approach

If the above command doesn't work (for example, if you're on macOS which uses a different version of sed), you can use this alternative:

```bash
find /path/to/web/root -type f -exec sed -i '' 's/bc550490-db76-49bc-af39-9770ebe41b08.preview.emergentagent.com/lcmm.legendre.cloud/g' {} \;
```

## For JavaScript files only

If you only want to replace in JavaScript files (which is where most references will be found):

```bash
find /path/to/web/root -name "*.js" -exec sed -i 's/bc550490-db76-49bc-af39-9770ebe41b08.preview.emergentagent.com/lcmm.legendre.cloud/g' {} \;
```

After running these commands, refresh your browser (clear cache if necessary) and the application should correctly communicate with your backend.
