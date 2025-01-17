<?php
// Assignment.php (Model)
namespace Models;

use PDO;
use PDOException;

class Assignment {
    public static function getAllByFaculty($conn, $faculty_id) {
        $sql = "SELECT a.*, c.name as course_name 
                FROM assignments a
                JOIN courses c ON FIND_IN_SET(c.id, a.course_id) 
                WHERE FIND_IN_SET(:faculty_id, c.assigned_faculty)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':faculty_id' => $faculty_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByIds($conn, $assignmentIds) {
        if (empty($assignmentIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($assignmentIds), '?'));
        $sql = "SELECT * FROM assignments WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($assignmentIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getAssignmentsByFaculty($conn, $faculty_id) {
        // Step 1: Fetch assigned courses for the faculty
        $sql = "SELECT assigned_courses FROM faculty WHERE id = :faculty_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':faculty_id' => $faculty_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $assigned_courses = $result ? json_decode($result['assigned_courses'], true) : [];

        if (empty($assigned_courses)) {
            return [];
        }

        // Step 2: Fetch assignments from the courses
        $placeholders = implode(',', array_fill(0, count($assigned_courses), '?'));
        $sql = "SELECT id, assignments FROM courses WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($assigned_courses);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $assignment_ids = [];
        $course_names = [];
        foreach ($courses as $course) {
            $course_assignments = json_decode($course['assignments'] ?? '[]', true);
            if (is_array($course_assignments)) {
                $assignment_ids = array_merge($assignment_ids, $course_assignments);
                foreach ($course_assignments as $assignment_id) {
                    $course_names[$assignment_id] = $course['id']; // Store course ID for each assignment
                }
            }
        }

        if (empty($assignment_ids)) {
            return [];
        }

        // Remove duplicate assignment IDs
        $assignment_ids = array_unique($assignment_ids);

        // Step 3: Fetch assignment details from the assignments table
        $placeholders = implode(',', array_fill(0, count($assignment_ids), '?'));
        $sql = "SELECT * FROM assignments WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($assignment_ids);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Step 4: Add course names to assignments
        foreach ($assignments as &$assignment) {
            $course_id = $course_names[$assignment['id']];
            $sql = "SELECT name FROM courses WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $course_id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
            $assignment['course_name'] = $course['name'];
        }

        return $assignments;
    }

    public static function getAll($conn) {
        $sql = "SELECT a.*, c.name as course_name 
                FROM assignments a 
                JOIN courses c ON JSON_CONTAINS(a.course_id, JSON_QUOTE(CAST(c.id AS CHAR)), '$')";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getspocAssignmentsByIds($conn, $ids) {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM assignments WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public static function getAssignmentsByCourseIdsForSpoc($conn, $course_ids) {
        if (empty($course_ids)) {
            return [];
        }
    
        $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
        $sql = "SELECT * FROM assignments WHERE JSON_CONTAINS(course_id, JSON_QUOTE(CAST(id AS CHAR)), '$') AND course_id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($course_ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAssignmentsByIds($conn, $ids) {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM assignments WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSubmissionCount($conn, $assignment_id) {
        $sql = "SELECT submissions FROM assignments WHERE id = :assignment_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':assignment_id' => $assignment_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $submissions = isset($result['submissions']) ? json_decode($result['submissions'], true) : [];
        return is_array($submissions) ? count($submissions) : 0;
    }

    public static function getById($conn, $id) {
        $sql = "SELECT * FROM assignments WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getByCourseId($conn, $course_id) {
        $sql = "SELECT * FROM assignments WHERE JSON_CONTAINS(course_id, :course_id, '$')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':course_id' => json_encode($course_id)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public static function getSubmissions($conn, $assignmentId) {
        $sql = "SELECT submissions FROM assignments WHERE id = :assignment_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':assignment_id' => $assignmentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $submissions = isset($result['submissions']) ? json_decode($result['submissions'] ?? '[]', true) : [];

        // Fetch student details for each submission
        $submissionDetails = [];
        foreach ($submissions as $submission) {
            $sql = "SELECT name, email FROM students WHERE id = :student_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':student_id' => $submission['student_id']]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            $submissionDetails[] = array_merge($submission, $student);
        }

        return $submissionDetails;
    }

    public static function grade($conn, $assignmentId, $studentId, $grade, $feedback) {
        $sql = "SELECT submissions FROM assignments WHERE id = :assignment_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':assignment_id' => $assignmentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $submissions = isset($result['submissions']) ? json_decode($result['submissions'], true) : [];

        // Update the grade and feedback for the specific student
        foreach ($submissions as &$submission) {
            if ($submission['student_id'] == $studentId) {
                $submission['grade'] = $grade;
                $submission['feedback'] = $feedback;
                break;
            }
        }

        // Update the submissions in the database
        $sql = "UPDATE assignments SET submissions = :submissions WHERE id = :assignment_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':submissions' => json_encode($submissions),
            ':assignment_id' => $assignmentId
        ]);
    }

    public static function getSubmission($conn, $assignmentId, $studentId) {
        $sql = "SELECT * FROM assignment_submissions WHERE assignment_id = :assignment_id AND student_id = :student_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':assignment_id' => $assignmentId, ':student_id' => $studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAssignmentsByStudentId($conn, $student_email) {
        // Step 1: Fetch assigned courses for the student
        $sql = "SELECT assigned_courses FROM students WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $student_email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $assigned_courses = $result ? json_decode($result['assigned_courses'] ?? '[]', true) : [];

        if (empty($assigned_courses)) {
            return [];
        }

        // Step 2: Fetch assignments from the courses
        $placeholders = implode(',', array_fill(0, count($assigned_courses), '?'));
        $sql = "SELECT id, name, assignments FROM courses WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($assigned_courses);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $assignment_ids = [];
        $course_names = [];
        foreach ($courses as $course) {
            $course_assignments = json_decode($course['assignments'] ?? '[]', true);
            if (is_array($course_assignments)) {
                $assignment_ids = array_merge($assignment_ids, $course_assignments);
                foreach ($course_assignments as $assignment_id) {
                    $course_names[$assignment_id] = $course['name']; // Store course name for each assignment
                }
            }
        }

        if (empty($assignment_ids)) {
            return [];
        }

        // Remove duplicate assignment IDs
        $assignment_ids = array_unique($assignment_ids);

        // Step 3: Fetch assignment details from the assignments table
        $placeholders = implode(',', array_fill(0, count($assignment_ids), '?'));
        $sql = "SELECT * FROM assignments WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($assignment_ids);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Step 4: Add course names and grade information to assignments
        foreach ($assignments as &$assignment) {
            $assignment['course_name'] = $course_names[$assignment['id']] ?? 'Unknown Course';
            $sql = "SELECT submissions FROM assignments WHERE id = :assignment_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':assignment_id' => $assignment['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $submissions = isset($result['submissions']) ? json_decode($result['submissions'] ?? '[]', true) : [];
            foreach ($submissions as $submission) {
                if ($submission['student_id'] == $_SESSION['student_id']) {
                    $assignment['grade'] = $submission['grade'] ?? 'Not Graded';
                    break;
                }
            }
        }

        return $assignments;
    }

    public function viewAssignment($assignment_id) {
        $conn = Database::getConnection();
        $assignment = Assignment::getById($conn, $assignment_id);

        require 'views/student/view_assignment.php';
    }

    public function submitAssignment($assignment_id) {
        $conn = Database::getConnection();
        $student_id = $_SESSION['student_id']; // Assuming student_id is stored in session

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
                $file_content = file_get_contents($_FILES['submission_file']['tmp_name']);
                $sql = "UPDATE assignments SET submissions = JSON_ARRAY_APPEND(submissions, '$', :student_id) WHERE id = :assignment_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':student_id' => $student_id, ':assignment_id' => $assignment_id]);

                header('Location: /student/manage_assignments');
                exit;
            } else {
                $error = "Failed to upload file.";
            }
        }

        $assignment = Assignment::getById($conn, $assignment_id);
        require 'views/student/view_assignment.php';
    }

    public static function isSubmitted($conn, $assignment_id, $student_id) {
        $sql = "SELECT submissions FROM assignments WHERE id = :assignment_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':assignment_id' => $assignment_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $submissions = isset($result['submissions']) ? json_decode($result['submissions'], true) : [];
        foreach ($submissions as $submission) {
            if ($submission['student_id'] == $student_id) {
                return true;
            }
        }
        return false;
    }

    public static function getGradesByStudentId($conn, $student_id) {
        $sql = "SELECT a.title, s.grade, s.feedback 
                FROM assignments a
                JOIN assignment_submissions s ON a.id = s.assignment_id
                WHERE s.student_id = :student_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':student_id' => $student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($conn, $title, $description, $due_date, $course_ids, $file_content) {
        $sql = "INSERT INTO assignments (title, description, due_date, course_id, file_content) VALUES (:title, :description, :due_date, :course_id, :file_content)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':due_date' => $due_date,
            ':course_id' => json_encode($course_ids),
            ':file_content' => $file_content
        ]);
        return $conn->lastInsertId();
    }
}