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

    public function createCoursesTable() {
        $sql = "CREATE TABLE IF NOT EXISTS courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
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
}
?>