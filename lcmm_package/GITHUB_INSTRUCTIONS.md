# LCMM GitHub Repository Setup

## Files Prepared for GitHub

I've created the following files for your GitHub repository:

1. **lcmm_setup.sql** - MySQL database setup script
2. **lcmm_diagnostics.php** - Diagnostic tool to help with setup and troubleshooting
3. **API_CONFIG.md** - Configuration details for the Sonarr and Radarr APIs
4. **README.md** - Installation and deployment instructions
5. **domain_replacement.md** - Instructions for replacing development domain references
6. **fix_urls.sh** - Script to automatically replace development domains
7. **build.sh** - Script to build the application for deployment

## Source Code Structure

- **src/** - Contains the core React component files
- **backend/** - PHP backend files for the API
- **public/** - Public assets and files

## GitHub Repository Setup Instructions

1. Create a new repository on GitHub
2. Clone it to your local machine
3. Copy the files from this package into your local repository
4. Commit and push the changes

```bash
git init
git add .
git commit -m "Initial commit of LCMM application"
git remote add origin [your-github-repo-url]
git push -u origin main
```

## Build Instructions

After setting up the repository, you can build the application for deployment:

1. Run the build script: `./build.sh`
2. This will create a `lcmm_deployment.zip` file
3. Upload and extract this file to your web server
4. Follow the setup instructions in README.md

## Deployment Instructions

See the README.md file for detailed deployment instructions. The basic steps are:

1. Upload the files to your web server
2. Import the lcmm_setup.sql file into your MySQL database
3. Run the lcmm_diagnostics.php script to verify setup
4. Log in with the admin credentials provided in API_CONFIG.md

## Customization

- Place your logo at `/logo.png` in the web root
- Create PWA icons at `/logo192.png` and `/logo512.png`
- Create a favicon at `/favicon.ico`