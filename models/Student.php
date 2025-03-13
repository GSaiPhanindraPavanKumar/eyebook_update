<?php
namespace Models;

use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
require_once __DIR__ . '/../vendor/autoload.php';

class Student {
    public static function getCount($conn) {
        $sql = "SELECT COUNT(*) as student_count FROM students";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['student_count'] ?? 0;
    }
    
    public static function search($conn, $searchQuery, $sortColumn = 'name', $sortOrder = 'asc') {
        $validColumns = ['regd_no', 'name', 'email', 'last_login'];
        if (!in_array($sortColumn, $validColumns)) {
            $sortColumn = 'name';
        }
        $sql = "SELECT students.*, universities.short_name as university_short_name 
                FROM students 
                JOIN universities ON students.university_id = universities.id 
                WHERE students.regd_no LIKE :search OR students.name LIKE :search OR students.email LIKE :search 
                ORDER BY $sortColumn $sortOrder";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['search' => '%' . $searchQuery . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function existsByEmail($conn, $email) {
        $sql = "SELECT COUNT(*) as count FROM students WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function updateLoginDetails($student_id) {
        $conn = Database::getConnection();
        $sql = "UPDATE students SET 
                    last_login = NOW(), 
                    login_count = login_count + 1, 
                    first_login = IF(first_login IS NULL, NOW(), first_login) 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$student_id]);
    }

    public static function getAll($conn) {
        $stmt = $conn->prepare("SELECT s.*, u.short_name as university_short_name, u.long_name as university
                               FROM students s 
                               LEFT JOIN universities u ON s.university_id = u.id 
                               ORDER BY s.name");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public static function getUserDataByEmail($conn, $email) {
        $sql = "SELECT students.*, universities.long_name as university, universities.short_name as university_short_name 
                FROM students 
                JOIN universities ON students.university_id = universities.id 
                WHERE students.email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAssignedCourses($conn, $student_id) {
        $sql = "SELECT assigned_courses FROM students WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $student_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['assigned_courses'] ? json_decode($result['assigned_courses'], true) : [];
    }

    public static function assignCourse($conn, $student_id, $course_id) {
        $assigned_courses = self::getAssignedCourses($conn, $student_id);
        if (!in_array($course_id, $assigned_courses)) {
            $assigned_courses[] = $course_id;
            $sql = "UPDATE students SET assigned_courses = :assigned_courses WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':assigned_courses' => json_encode($assigned_courses),
                ':id' => $student_id
            ]);
        }
    }
    
    public static function unassignCourse($conn, $student_id, $course_id) {
        $assigned_courses = self::getAssignedCourses($conn, $student_id);
        if (in_array($course_id, $assigned_courses)) {
            $assigned_courses = array_values(array_diff($assigned_courses, [$course_id]));
            $sql = "UPDATE students SET assigned_courses = :assigned_courses WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':assigned_courses' => json_encode($assigned_courses),
                ':id' => $student_id
            ]);
        }
    }

    public static function getByUniversityId($conn, $universityId) {
        $sql = "SELECT * FROM students WHERE university_id = :university_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['university_id' => $universityId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByCourseId($conn, $courseId) {
        $sql = "SELECT * FROM students WHERE FIND_IN_SET(:course_id, course_ids)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['course_id' => $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($conn, $data) {
        $sql = "SELECT * FROM students WHERE regd_no = :regd_no OR email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':regd_no' => $data['regd_no'],
            ':email' => $data['email']
        ]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Return the duplicate record
            return [
                'duplicate' => true,
                'data' => $data
            ];
        } else {
            // Insert the new record
            $sql = "INSERT INTO students (regd_no, name, email, phone, section, stream, year, dept, university_id, password) 
                    VALUES (:regd_no, :name, :email, :phone, :section, :stream, :year, :dept, :university_id, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':regd_no' => $data['regd_no'],
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':phone' => $data['phone'],
                ':section' => $data['section'],
                ':stream' => $data['stream'],
                ':year' => $data['year'],
                ':dept' => $data['dept'],
                ':university_id' => $data['university_id'],
                ':password' => password_hash($data['password'], PASSWORD_BCRYPT)
            ]);

            return [
                'duplicate' => false
            ];
        }
    }

    public static function uploadStudents($conn, $data, $university_id) {
        // Check for duplicates
        $sql = "SELECT * FROM students WHERE regd_no = :regd_no OR email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':regd_no' => $data['regd_no'],
            ':email' => $data['email']
        ]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Return the duplicate record
            return [
                'duplicate' => true,
                'data' => $data
            ];
        } else {
            // Insert the new record
            $sql = "INSERT INTO students (regd_no, name, email, phone, section, stream, year, dept, university_id, password) 
                    VALUES (:regd_no, :name, :email, :phone, :section, :stream, :year, :dept, :university_id, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':regd_no' => $data['regd_no'],
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':phone' => $data['phone'],
                ':section' => $data['section'],
                ':stream' => $data['stream'],
                ':year' => $data['year'],
                ':dept' => $data['dept'],
                ':university_id' => $university_id,
                ':password' => password_hash($data['password'], PASSWORD_BCRYPT)
            ]);

            return [
                'duplicate' => false
            ];
        }
    }

    public static function existsByRegdNo($conn, $regd_no) {
        $sql = "SELECT COUNT(*) as count FROM students WHERE regd_no = :regd_no";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':regd_no' => $regd_no]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function login($username, $password) {
        $conn = Database::getConnection();
        $sql = "SELECT * FROM students WHERE email = :username";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':username' => $username]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($student && password_verify($password, $student['password'])) {
            $_SESSION['email'] = $student['email']; // Set email in session
            $_SESSION['user_id'] = $student['id'];
            $_SESSION['user_type'] = 'student';
            if (is_null($student['first_login']) || $student['login_count'] == 0) {
                $_SESSION['force_reset_password'] = true;
                header('Location: /force_reset_password');
                exit();
            }
            return $student;
        }
    
        return false;
    }

    public static function getByIds($conn, $student_ids) {
        // If input is a JSON string, decode it
        if (is_string($student_ids)) {
            $student_ids = json_decode($student_ids, true);
        }

        if (empty($student_ids)) {
            return [];
        }

        // Convert all IDs to strings and create placeholders
        $student_ids = array_map('strval', $student_ids);
        
        // Create named parameters for each ID
        $placeholders = [];
        $params = [];
        foreach ($student_ids as $i => $id) {
            $param = ":id" . $i;
            $placeholders[] = $param;
            $params[$param] = $id;
        }
        
        $sql = "SELECT s.*, u.short_name as university_short_name, u.long_name as university_name 
                FROM students s 
                LEFT JOIN universities u ON s.university_id = u.id 
                WHERE s.id IN (" . implode(',', $placeholders) . ")";
        
        $stmt = $conn->prepare($sql);
        
        // Bind each parameter
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($conn, $id) {
        $sql = "SELECT * FROM students WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getByEmail($conn, $email) {
        $sql = "SELECT * FROM students WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function getAllWithUniversity($conn) {
        $sql = "SELECT students.id, students.name, students.email, universities.long_name as university 
                FROM students 
                LEFT JOIN universities ON students.university_id = universities.id";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByIdsWithUniversity($conn, $ids) {
        if (empty($ids) || !is_array($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT students.id, students.name, universities.long_name as university 
                FROM students 
                LEFT JOIN universities ON students.university_id = universities.id
                WHERE students.id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function assignCourseToStudents($conn, $student_ids, $course_id) {
        foreach ($student_ids as $student_id) {
            $student = self::getById($conn, $student_id);
            $assigned_courses = json_decode($student['assigned_courses'], true) ?? [];
    
            if (!in_array($course_id, $assigned_courses)) {
                $assigned_courses[] = $course_id;
                $sql = "UPDATE students SET assigned_courses = :assigned_courses WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':assigned_courses' => json_encode($assigned_courses),
                    ':id' => $student_id
                ]);
            }
        }
    }
    public static function unassignCourseFromStudents($conn, $student_ids, $course_id) {
        foreach ($student_ids as $student_id) {
            $student = self::getById($conn, $student_id);
            $assigned_courses = json_decode($student['assigned_courses'], true) ?? [];
    
            if (in_array($course_id, $assigned_courses)) {
                $assigned_courses = array_diff($assigned_courses, [$course_id]);
                $sql = "UPDATE students SET assigned_courses = :assigned_courses WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':assigned_courses' => json_encode($assigned_courses),
                    ':id' => $student_id
                ]);
            }
        }
    }

    public static function assignCoursesToStudents($conn, $student_ids, $course_ids) {
        foreach ($student_ids as $student_id) {
            $student = self::getById($conn, $student_id);
            $assigned_courses = json_decode($student['assigned_courses'], true) ?? [];
    
            // Merge new course IDs with existing ones
            $updated_courses = array_unique(array_merge($assigned_courses, $course_ids));
    
            $sql = "UPDATE students SET assigned_courses = :assigned_courses WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':assigned_courses' => json_encode($updated_courses),
                ':id' => $student_id
            ]);
        }
    }

    public static function unassignCoursesFromStudents($conn, $student_ids, $course_ids) {
        foreach ($student_ids as $student_id) {
            $student = self::getById($conn, $student_id);
            $assigned_courses = json_decode($student['assigned_courses'], true) ?? [];
    
            foreach ($course_ids as $course_id) {
                if (in_array($course_id, $assigned_courses)) {
                    $assigned_courses = array_values(array_diff($assigned_courses, [$course_id]));
                }
            }
    
            $sql = "UPDATE students SET assigned_courses = :assigned_courses WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':assigned_courses' => json_encode(array_values($assigned_courses)), // Reindex the array
                ':id' => $student_id
            ]);
        }
    }

    public static function getAllByUniversity($conn, $university_id) {
        $sql = "SELECT * FROM students WHERE university_id = :university_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['university_id' => $university_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllByFaculty($conn, $facultyEmail) {
        $sql = "SELECT students.* 
                FROM students 
                JOIN faculty ON students.university_id = faculty.university_id 
                WHERE faculty.email = :facultyEmail";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':facultyEmail', $facultyEmail, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // public static function submitAssignment($conn, $student_id, $assignment_id, $file_path) {
    //     $sql = "INSERT INTO assignment_submissions (student_id, assignment_id, file_path) VALUES (:student_id, :assignment_id, :file_path)
    //             ON DUPLICATE KEY UPDATE file_path = :file_path";
    //     $stmt = $conn->prepare($sql);
    //     $stmt->execute([
    //         ':student_id' => $student_id,
    //         ':assignment_id' => $assignment_id,
    //         ':file_path' => $file_path
    //     ]);
    // }

    public static function updatePassword($conn, $studentId, $newPassword) {
        $sql = "UPDATE students SET password = :new_password WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':new_password' => $newPassword,
            ':id' => $studentId
        ]);
    }

    public static function getAssignmentsByStudentId($conn, $student_id) {
        $sql = "SELECT a.*, s.file_path, s.grade 
                FROM assignments a
                LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.student_id = :student_id
                WHERE a.course_id IN (SELECT course_id FROM student_courses WHERE student_id = :student_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':student_id' => $student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getAllBySectionYearAndUniversity($conn, $facultyEmail, $year = null, $section = null) {
        $sql = "SELECT students.* 
                FROM students 
                JOIN faculty ON students.university_id = faculty.university_id 
                WHERE faculty.email = :facultyEmail";

        $params = ['facultyEmail' => $facultyEmail];

        if ($year !== null) {
            $sql .= " AND students.year = :year";
            $params['year'] = $year;
        }

        if ($section !== null) {
            $sql .= " AND students.section = :section";
            $params['section'] = $section;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getCountByUniversityId($conn, $university_id) {
        $sql = "SELECT COUNT(*) as student_count FROM students WHERE university_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$university_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['student_count'] ?? 0;
    }
    public function updateLastLogin($student_id) {
        $conn = Database::getConnection();
        $sql = "UPDATE students SET last_login = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$student_id]);
    }

    // In Student.php
    public static function getAllByCourseAndSection($conn, $course_id, $section_id) {
        $sql = "SELECT * FROM students WHERE course_id = :course_id AND section_id = :section_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':course_id' => $course_id, ':section_id' => $section_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllByCourse($conn, $course_id) {
        $sql = "SELECT * FROM students WHERE course_id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':course_id' => $course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // In Course.php
    public static function getCoursesByFaculty($conn) {
        $faculty_id = $_SESSION['faculty_id']; // Assuming faculty_id is stored in session
        $sql = "SELECT * FROM courses WHERE faculty_id = :faculty_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':faculty_id' => $faculty_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function submitAssignment($conn, $student_id, $assignment_id, $file_content) {
        $sql = "INSERT INTO assignment_submissions (student_id, assignment_id, file_content) VALUES (:student_id, :assignment_id, :file_content)
                ON DUPLICATE KEY UPDATE file_content = :file_content";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':assignment_id', $assignment_id);
        $stmt->bindParam(':file_content', $file_content, PDO::PARAM_LOB);
        $stmt->execute();
    }

    public static function createFacultyStudentAccount($conn, $faculty) {
        // Create student email by adding _student before @
        $emailParts = explode('@', $faculty['email']);
        $studentEmail = $emailParts[0] . '_student@' . $emailParts[1];
        
        // Check if student account already exists
        if (self::existsByEmail($conn, $studentEmail)) {
            return false;
        }

        // Generate a registration number for faculty student account
        // Format: FS + current year + random 4 digits
        $year = date('Y');
        $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $regd_no = "FS{$year}{$random}";

        // Make sure the generated regd_no is unique
        while (self::existsByRegdNo($conn, $regd_no)) {
            $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $regd_no = "FS{$year}{$random}";
        }
        
        $sql = "INSERT INTO students (
            regd_no,
            name, 
            email, 
            phone, 
            section, 
            stream, 
            dept,
            university_id, 
            password,
            created_at
        ) VALUES (
            :regd_no,
            :name, 
            :email, 
            :phone, 
            :section, 
            :stream, 
            :dept,
            :university_id, 
            :password,
            NOW()
        )";
        
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':regd_no' => $regd_no,
            ':name' => $faculty['name'],
            ':email' => $studentEmail,
            ':phone' => $faculty['phone'] ?? null,
            ':section' => $faculty['section'] ?? null,
            ':stream' => $faculty['stream'] ?? null,
            ':dept' => $faculty['department'] ?? null,
            ':university_id' => $faculty['university_id'],
            ':password' => $faculty['password']
        ]);
    }

    public static function updateCheckIn($conn, $studentId) {
        $today = date('Y-m-d');
        $day = date('d');
        $month = date('m');
        $year = date('Y');
        
        // Get last check-in
        $stmt = $conn->prepare("SELECT last_check_in, check_in_streak FROM students WHERE id = ?");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $newStreak = ($result['last_check_in'] == $yesterday) ? $result['check_in_streak'] + 1 : 1;
        
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // Update student check-in status
            $sql = "UPDATE students 
                    SET last_check_in = ?, check_in_streak = ? 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$today, $newStreak, $studentId]);
            
            // Store check-in history
            $sql = "INSERT INTO student_checkins (student_id, check_in_date, check_in_time, check_in_day, check_in_month, check_in_year) 
                    VALUES (?, ?, NOW(), ?, ?, ?)
                    ON DUPLICATE KEY UPDATE check_in_time = NOW(), check_in_day = VALUES(check_in_day), check_in_month = VALUES(check_in_month), check_in_year = VALUES(check_in_year)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$studentId, $today, $day, $month, $year]);
            
            // Commit transaction
            $conn->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback on error
            $conn->rollBack();
            error_log("Check-in error: " . $e->getMessage());
            return false;
        }
    }

    public static function getCheckInHistory($conn, $studentId) {
        $stmt = $conn->prepare("SELECT check_in_date FROM student_checkins WHERE student_id = ? ORDER BY check_in_date DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCheckInStatus($conn, $studentId) {
        $stmt = $conn->prepare("SELECT check_in_streak, total_check_ins, last_check_in FROM students WHERE id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getStudentMetrics($conn, $studentId) {
        // Get student data including assigned courses and completed books
        $stmt = $conn->prepare("
            SELECT s.*, 
                   s.level as student_level,
                   s.assigned_courses,
                   s.completed_books
            FROM students s 
            WHERE s.id = ?
        ");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        // Parse assigned courses and completed books
        $assignedCourses = !empty($student['assigned_courses']) ? json_decode($student['assigned_courses'], true) : [];
        $completedBooks = !empty($student['completed_books']) ? json_decode($student['completed_books'], true) : [];

        // Get course details for assigned courses
        $courses = [];
        if (!empty($assignedCourses)) {
            $placeholders = implode(',', array_fill(0, count($assignedCourses), '?'));
            $stmt = $conn->prepare("SELECT id, name, course_book, status FROM courses WHERE id IN ($placeholders)");
            $stmt->execute($assignedCourses);
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Calculate overall progress
        $totalProgress = 0;
        $courseCount = 0;
        foreach ($courses as $course) {
            $courseId = $course['id'];
            $courseBooks = !empty($course['course_book']) ? json_decode($course['course_book'], true) : [];
            $totalBooks = is_array($courseBooks) ? count($courseBooks) : 0;
            $completedBooksCount = is_array($completedBooks[$courseId] ?? []) ? count($completedBooks[$courseId] ?? []) : 0;
            
            if ($totalBooks > 0) {
                $totalProgress += ($completedBooksCount / $totalBooks) * 100;
                $courseCount++;
            }
        }

        // Calculate completed projects (from contest submissions)
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT q.id) as completed_projects
            FROM contest_questions q 
            JOIN contest_questions cq ON q.id = cq.id 
            WHERE JSON_CONTAINS(cq.submissions, JSON_OBJECT('student_id', ?))
        ");
        $stmt->execute([$studentId]);
        $projectData = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_courses' => count($courses),
            'completed_projects' => $projectData['completed_projects'] ?? 0,
            'overall_progress' => $courseCount > 0 ? $totalProgress / $courseCount : 0,
            'student_level' => $student['student_level'] ?? 1
        ];
    }

    public static function getCoursesWithProgress($conn, $studentId) {
        // Fetch the assigned courses for the student
        $stmt = $conn->prepare("SELECT assigned_courses, completed_books FROM students WHERE id = :student_id");
        $stmt->execute(['student_id' => $studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        $assignedCourses = !empty($student['assigned_courses']) ? json_decode($student['assigned_courses'], true) : [];
        $completedBooks = !empty($student['completed_books']) ? json_decode($student['completed_books'], true) : [];

        if (empty($assignedCourses)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($assignedCourses), '?'));
        $stmt = $conn->prepare("SELECT id, name, description, course_book, status FROM courses WHERE id IN ($placeholders)");
        $stmt->execute($assignedCourses);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($courses as &$course) {
            $courseId = $course['id'];
            $courseBooks = !empty($course['course_book']) ? json_decode($course['course_book'], true) : [];
            $totalBooks = is_array($courseBooks) ? count($courseBooks) : 0;
            $completedBooksCount = is_array($completedBooks[$courseId] ?? []) ? count($completedBooks[$courseId] ?? []) : 0;
            $course['progress'] = ($totalBooks > 0) ? ($completedBooksCount / $totalBooks) * 100 : 0;
        }

        return $courses;
    }
}
?>