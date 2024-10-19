<?php
class AdminModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createAdminsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        )";
        if ($this->conn->query($sql) === TRUE) {
            return "Table 'admins' created successfully or already exists";
        } else {
            return "Error creating table: " . $this->conn->error;
        }
    }

    public function createUniversitiesTable() {
        $sql = "CREATE TABLE IF NOT EXISTS universities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            long_name VARCHAR(255) NOT NULL,
            short_name VARCHAR(255) NOT NULL UNIQUE,
            location VARCHAR(255) NOT NULL,
            country VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        if ($this->conn->query($sql) === TRUE) {
            return "Table 'universities' created successfully or already exists";
        } else {
            return "Error creating table: " . $this->conn->error;
        }
    }

    public function createSpocsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS spocs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            phone VARCHAR(255) NOT NULL,
            university_id INT NOT NULL,
            password VARCHAR(255) NOT NULL,
            reset_password_token VARCHAR(255),
            reset_password_expires DATETIME,
            FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE
        )";
        if ($this->conn->query($sql) === TRUE) {
            return "Table 'spocs' created successfully or already exists";
        } else {
            return "Error creating table: " . $this->conn->error;
        }
    }

    public function createStudentsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            regd_no VARCHAR(255) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            section VARCHAR(255) NOT NULL,
            stream VARCHAR(255) NOT NULL,
            year INT NOT NULL,
            dept VARCHAR(255) NOT NULL,
            university_id INT NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE
        )";
        if ($this->conn->query($sql) === TRUE) {
            return "Table 'students' created successfully or already exists";
        } else {
            return "Error creating table: " . $this->conn->error;
        }
    }

    public function createFacultyTable() {
        $sql = "CREATE TABLE IF NOT EXISTS faculty (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            phone VARCHAR(255) NOT NULL,
            section VARCHAR(255) NOT NULL,
            stream VARCHAR(255) NOT NULL,
            year VARCHAR(255) NOT NULL,
            department VARCHAR(255) NOT NULL,
            university_id INT NOT NULL,
            password VARCHAR(255) NOT NULL,
            reset_password_token VARCHAR(255),
            reset_password_expires DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE
        )";
        if ($this->conn->query($sql) === TRUE) {
            return "Table 'faculty' created successfully or already exists";
        } else {
            return "Error creating table: " . $this->conn->error;
        }
    }

    public function createCoursesTable() {
        $sql = "CREATE TABLE IF NOT EXISTS courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            university_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE
        )";
        if ($this->conn->query($sql) === TRUE) {
            return "Table 'courses' created successfully or already exists";
        } else {
            return "Error creating table: " . $this->conn->error;
        }
    }

    public function createStudentsProgressTable() {
        $sql = "CREATE TABLE IF NOT EXISTS students_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            course_id INT NOT NULL,
            progress INT NOT NULL,
            grade VARCHAR(10),
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )";
        if ($this->conn->query($sql) === TRUE) {
            return "Table 'students_progress' created successfully or already exists";
        } else {
            return "Error creating table: " . $this->conn->error;
        }
    }

    public function createAssessmentsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS assessments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            date DATE NOT NULL,
            max_marks INT NOT NULL,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )";
        if ($this->conn->query($sql) === TRUE) {
            return "Table 'assessments' created successfully or already exists";
        } else {
            return "Error creating table: " . $this->conn->error;
        }
    }

    public function createAssignmentsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            due_date DATE NOT NULL,
            status VARCHAR(50),
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )";
        if ($this->conn->query($sql) === TRUE) {
            return "Table 'assignments' created successfully or already exists";
        } else {
            return "Error creating table: " . $this->conn->error;
        }
    }

    public function createVirtualMeetingsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS virtual_meetings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            topic VARCHAR(255) NOT NULL,
            date DATE NOT NULL,
            meeting_link VARCHAR(255) NOT NULL,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )";
        if ($this->conn->query($sql) === TRUE) {
            return "Table 'virtual_meetings' created successfully or already exists";
        } else {
            return "Error creating table: " . $this->conn->error;
        }
    }

    public function createAttendanceTable() {
        $sql = "CREATE TABLE IF NOT EXISTS attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            course_id INT NOT NULL,
            attendance_percentage INT NOT NULL,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )";
        if ($this->conn->query($sql) === TRUE) {
            return "Table 'attendance' created successfully or already exists";
        } else {
            return "Error creating table: " . $this->conn->error;
        }
    }
}
?>