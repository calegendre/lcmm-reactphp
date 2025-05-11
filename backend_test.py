#!/usr/bin/env python3
import requests
import json
import sys
from datetime import datetime

class LCMMAPITester:
    def __init__(self, base_url="https://lcmm.legendre.cloud/api"):
        self.base_url = base_url
        self.token = None
        self.tests_run = 0
        self.tests_passed = 0
        self.admin_token = None

    def run_test(self, name, method, endpoint, expected_status, data=None, token=None):
        """Run a single API test"""
        url = f"{self.base_url}/{endpoint}"
        headers = {'Content-Type': 'application/json'}
        
        if token:
            headers['Authorization'] = f'Bearer {token}'
        elif self.token:
            headers['Authorization'] = f'Bearer {self.token}'

        self.tests_run += 1
        print(f"\n🔍 Testing {name}...")
        
        try:
            if method == 'GET':
                response = requests.get(url, headers=headers)
            elif method == 'POST':
                response = requests.post(url, json=data, headers=headers)
            elif method == 'PUT':
                response = requests.put(url, json=data, headers=headers)
            elif method == 'DELETE':
                response = requests.delete(url, headers=headers)

            success = response.status_code == expected_status
            if success:
                self.tests_passed += 1
                print(f"✅ Passed - Status: {response.status_code}")
                try:
                    return success, response.json()
                except:
                    return success, {}
            else:
                print(f"❌ Failed - Expected {expected_status}, got {response.status_code}")
                try:
                    print(f"Response: {response.text}")
                    return False, response.json()
                except:
                    return False, {}

        except Exception as e:
            print(f"❌ Failed - Error: {str(e)}")
            return False, {}

    def test_login(self, email, password):
        """Test login and get token"""
        success, response = self.run_test(
            "Login",
            "POST",
            "auth.php?action=login",
            200,
            data={"email": email, "password": password}
        )
        if success and 'token' in response:
            self.token = response['token']
            return True
        return False
    
    def test_admin_login(self, email, password):
        """Test admin login and get token"""
        success, response = self.run_test(
            "Admin Login",
            "POST",
            "auth.php?action=login",
            200,
            data={"email": email, "password": password}
        )
        if success and 'token' in response:
            self.admin_token = response['token']
            return True
        return False

    def test_get_tv_shows(self):
        """Test getting TV shows from Sonarr"""
        success, response = self.run_test(
            "Get TV Shows",
            "GET",
            "sonarr.php?action=series",
            200,
            token=self.token
        )
        
        if success:
            # Check if we have TV shows with proper season and episode counts
            if len(response) > 0:
                print(f"Found {len(response)} TV shows")
                sample_show = response[0]
                print(f"Sample show: {sample_show.get('title')}")
                print(f"Season count: {sample_show.get('seasonCount')}")
                print(f"Episode count: {sample_show.get('episodeCount')}")
                
                # Check if season and episode counts are not zero
                if sample_show.get('seasonCount', 0) > 0 and sample_show.get('episodeCount', 0) > 0:
                    print("✅ TV Shows have proper season and episode counts")
                else:
                    print("❌ TV Shows still have zero season or episode counts")
            else:
                print("No TV shows found in the response")
        
        return success

    def test_get_movies(self):
        """Test getting movies from Radarr"""
        success, response = self.run_test(
            "Get Movies",
            "GET",
            "radarr.php?action=movies",
            200,
            token=self.token
        )
        
        if success:
            print(f"Found {len(response)} movies")
        
        return success

    def test_get_additions(self):
        """Test getting additions (admin only)"""
        success, response = self.run_test(
            "Get Additions",
            "GET",
            "additions.php",
            200,
            token=self.admin_token
        )
        
        if success:
            print(f"Found {len(response)} additions")
            if len(response) > 0:
                # Check if additions are properly filtered to only show added content
                actions = set([item.get('action') for item in response])
                print(f"Actions in additions: {actions}")
                
                # Check if only add_series and add_movie actions are present
                if actions.issubset({'add_series', 'add_movie'}):
                    print("✅ Additions are properly filtered to only show added content")
                else:
                    print("❌ Additions contain actions other than add_series and add_movie")
        
        return success

def main():
    # Setup
    tester = LCMMAPITester()
    
    # Test regular user login
    if not tester.test_login("cl@legendremedia.com", "X9k#vP2$mL8qZ3nT"):
        print("❌ Regular user login failed, stopping tests")
        return 1
    
    # Test getting TV shows
    tester.test_get_tv_shows()
    
    # Test getting movies
    tester.test_get_movies()
    
    # Test admin login
    if not tester.test_admin_login("cl@legendremedia.com", "X9k#vP2$mL8qZ3nT"):
        print("❌ Admin login failed, stopping admin tests")
    else:
        # Test getting additions (admin only)
        tester.test_get_additions()

    # Print results
    print(f"\n📊 Tests passed: {tester.tests_passed}/{tester.tests_run}")
    return 0 if tester.tests_passed == tester.tests_run else 1

if __name__ == "__main__":
    sys.exit(main())