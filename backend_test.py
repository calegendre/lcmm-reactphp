import os
import unittest
import requests
from pathlib import Path

class LCMMBackendTest(unittest.TestCase):
    """Test suite for LCMM application backend structure and basic functionality"""
    
    def setUp(self):
        """Set up test environment"""
        self.backend_dir = Path("/app/backend")
        self.frontend_dir = Path("/app/frontend")
        self.frontend_build_dir = Path("/app/frontend/build")
        
        # Get backend URL from frontend .env file
        env_file = self.frontend_dir / ".env"
        self.backend_url = None
        if env_file.exists():
            with open(env_file, "r") as f:
                for line in f:
                    if line.startswith("REACT_APP_BACKEND_URL="):
                        self.backend_url = line.strip().split("=", 1)[1].strip('"')
                        break
    
    def test_backend_structure(self):
        """Test if the PHP backend files are properly structured"""
        # Check essential backend files
        essential_files = [
            "config.php",
            "api/index.php",
            "api/auth.php",
            "api/users.php",
            "api/sonarr.php",
            "api/radarr.php",
            "api/invitations.php",
            "api/setup.php"
        ]
        
        for file_path in essential_files:
            full_path = self.backend_dir / file_path
            self.assertTrue(full_path.exists(), f"Missing essential backend file: {file_path}")
            self.assertTrue(full_path.stat().st_size > 0, f"Backend file is empty: {file_path}")
    
    def test_frontend_build_structure(self):
        """Test if the frontend build directory contains essential files"""
        # Check essential frontend build files
        essential_files = [
            "index.html",
            "service-worker.js",
            "manifest.json"
        ]
        
        for file_path in essential_files:
            full_path = self.frontend_build_dir / file_path
            self.assertTrue(full_path.exists(), f"Missing essential frontend build file: {file_path}")
            self.assertTrue(full_path.stat().st_size > 0, f"Frontend build file is empty: {file_path}")
    
    def test_static_assets(self):
        """Test if the frontend build directory contains static assets"""
        static_dir = self.frontend_build_dir / "static"
        self.assertTrue(static_dir.exists(), "Missing static directory in frontend build")
        
        # Check if static directory contains JS and CSS files
        js_files = list(static_dir.glob("**/*.js"))
        css_files = list(static_dir.glob("**/*.css"))
        
        self.assertTrue(len(js_files) > 0, "No JavaScript files found in static directory")
        self.assertTrue(len(css_files) > 0, "No CSS files found in static directory")
    
    def test_backend_api_endpoint(self):
        """Test if the backend API endpoint is accessible (if available)"""
        if self.backend_url:
            try:
                # Just check if the backend is reachable, don't test actual API functionality
                response = requests.options(f"{self.backend_url}/api/", timeout=5)
                self.assertTrue(response.status_code < 500, 
                               f"Backend API endpoint returned server error: {response.status_code}")
            except requests.RequestException as e:
                self.skipTest(f"Backend API endpoint not accessible: {str(e)}")
        else:
            self.skipTest("Backend URL not found in frontend .env file")

if __name__ == "__main__":
    unittest.main(verbosity=2)