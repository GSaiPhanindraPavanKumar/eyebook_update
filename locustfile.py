from locust import HttpUser, task, between
import random

class EyebookUser(HttpUser):
    wait_time = between(1, 3)  # Wait between 1-3 seconds between tasks
    
    def on_start(self):
        """Initialize user session"""
        self.login()
    
    def login(self):
        """Attempt to login with test credentials"""
        # Test credentials - modify these according to your test environment
        credentials = [
            {"username": "9921004217@klu.ac.in", "password": "Kare@2024"},
            {"username": "rajasubramanian.r1@gmail.com", "password": "Test@123"},
            {"username": "admin@gmail.com", "password": "Admin123@"},
            {"username": "rajasubramanian.r@klu.ac.in", "password": "Test@123"}
        ]
        
        # Randomly select credentials
        cred = random.choice(credentials)
        
        # Login request
        response = self.client.post("/login", {
            "username": cred["username"],
            "password": cred["password"],
            "agree": "on"
        })
        
        # Store user type based on email
        if "student" in cred["username"]:
            self.user_type = "student"
        elif "faculty" in cred["username"]:
            self.user_type = "faculty"
        elif "admin" in cred["username"]:
            self.user_type = "admin"
        else:
            self.user_type = "spoc"

    @task(3)
    def view_dashboard(self):
        """View dashboard based on user type"""
        if self.user_type == "student":
            self.client.get("/student/dashboard")
        elif self.user_type == "faculty":
            self.client.get("/faculty/dashboard")
        elif self.user_type == "admin":
            self.client.get("/admin/dashboard")
        else:
            self.client.get("/spoc/dashboard")

    @task(2)
    def view_profile(self):
        """View user profile"""
        if self.user_type == "student":
            self.client.get("/student/profile")
        elif self.user_type == "faculty":
            self.client.get("/faculty/profile")
        elif self.user_type == "admin":
            self.client.get("/admin/profile")
        else:
            self.client.get("/spoc/profile")

    @task(2)
    def view_courses(self):
        """View courses"""
        if self.user_type == "student":
            self.client.get("/student/my_courses")
        elif self.user_type == "faculty":
            self.client.get("/faculty/my_courses")
        elif self.user_type == "admin":
            self.client.get("/admin/manage_courses")
        else:
            self.client.get("/spoc/manage_courses")

    @task(1)
    def view_assignments(self):
        """View assignments"""
        if self.user_type == "student":
            self.client.get("/student/manage_assignments")
        elif self.user_type == "faculty":
            self.client.get("/faculty/manage_assignments")
        elif self.user_type == "admin":
            self.client.get("/admin/manage_assignments")

    @task(1)
    def view_discussion_forum(self):
        """View discussion forum"""
        if self.user_type in ["student", "faculty", "spoc"]:
            self.client.get("/student/discussion_forum")

    @task(1)
    def logout(self):
        """Logout and login again"""
        self.client.get("/logout")
        self.login()

class WebsiteTest(HttpUser):
    wait_time = between(1, 2)
    
    @task(1)
    def test_home(self):
        """Test home page load"""
        self.client.get("/")
    
    @task(1)
    def test_login_page(self):
        """Test login page load"""
        self.client.get("/login")

    @task(1)
    def test_invalid_login(self):
        """Test invalid login attempt"""
        self.client.post("/login", {
            "username": f"invalid{random.randint(1,1000)}@test.com",
            "password": "wrongpassword",
            "agree": "on"
        })
