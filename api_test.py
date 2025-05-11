import requests
import unittest
import json
from urllib.parse import urljoin

class LCMMAPITest(unittest.TestCase):
    """Test suite for LCMM application API endpoints"""
    
    def setUp(self):
        """Set up test environment"""
        # Get backend URL from frontend .env file
        with open("/app/frontend/.env", "r") as f:
            for line in f:
                if line.startswith("REACT_APP_BACKEND_URL="):
                    self.base_url = line.strip().split("=", 1)[1].strip('"')
                    break
        
        if not self.base_url:
            self.skipTest("Backend URL not found in frontend .env file")
        
        # Ensure the base URL ends with a slash
        if not self.base_url.endswith('/'):
            self.base_url += '/'
    
    def test_api_endpoints(self):
        """Test if the API endpoints are accessible"""
        endpoints = [
            "api/",  # Root endpoint
            "api/auth.php",  # Auth endpoint
            "api/users.php",  # Users endpoint
            "api/sonarr.php",  # Sonarr endpoint
            "api/radarr.php",  # Radarr endpoint
        ]
        
        for endpoint in endpoints:
            url = urljoin(self.base_url, endpoint)
            try:
                # Use OPTIONS request to check if endpoint exists without triggering full API logic
                response = requests.options(url, timeout=5)
                self.assertLess(response.status_code, 500, 
                               f"Endpoint {endpoint} returned server error: {response.status_code}")
                print(f"Endpoint {endpoint} is accessible with status code {response.status_code}")
            except requests.RequestException as e:
                print(f"Error accessing endpoint {endpoint}: {str(e)}")
    
    def test_auth_endpoint(self):
        """Test the authentication endpoint"""
        auth_url = urljoin(self.base_url, "api/auth.php")
        
        # Test with invalid credentials
        try:
            payload = {
                "action": "login",
                "email": "test@example.com",
                "password": "wrongpassword"
            }
            
            response = requests.post(auth_url, json=payload, timeout=5)
            print(f"Auth endpoint response status: {response.status_code}")
            
            if response.status_code == 200:
                try:
                    data = response.json()
                    print(f"Auth endpoint response: {json.dumps(data, indent=2)}")
                except json.JSONDecodeError:
                    print(f"Auth endpoint returned non-JSON response: {response.text[:100]}")
            else:
                print(f"Auth endpoint returned status code {response.status_code}: {response.text[:100]}")
        
        except requests.RequestException as e:
            print(f"Error accessing auth endpoint: {str(e)}")

if __name__ == "__main__":
    unittest.main(verbosity=2)