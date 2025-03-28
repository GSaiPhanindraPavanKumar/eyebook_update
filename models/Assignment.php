<?php
// Assignment.php (Model)
namespace Models;

use PDO;
use PDOException;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

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
        try {
            // Increase memory limit
            ini_set('memory_limit', '1024M');
            
            // Step 1: Get assigned courses efficiently
            $sql = "SELECT assigned_courses FROM faculty WHERE id = :faculty_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':faculty_id' => $faculty_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || empty($result['assigned_courses'])) {
                return [];
            }
            
            $assigned_courses = json_decode($result['assigned_courses'], true) ?: [];
            if (empty($assigned_courses)) {
                return [];
            }

            // Step 2: Get assignments and course names in a single query
            $placeholders = str_repeat('?,', count($assigned_courses) - 1) . '?';
            $sql = "SELECT DISTINCT a.*, c.name as course_name 
                    FROM assignments a
                    INNER JOIN (
                        SELECT c.id as course_id, c.name, 
                               JSON_UNQUOTE(JSON_EXTRACT(c.assignments, '$[*]')) as assignment_ids
                        FROM courses c 
                        WHERE c.id IN ($placeholders)
                    ) c ON FIND_IN_SET(a.id, c.assignment_ids) > 0";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($assigned_courses);
            
            // Fetch results in chunks to conserve memory
            $assignments = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $assignments[] = $row;
                
                // Free some memory after each chunk
                if (count($assignments) % 100 === 0) {
                    gc_collect_cycles();
                }
            }
            
            // Clean up
            $stmt->closeCursor();
            unset($result, $assigned_courses);
            gc_collect_cycles();
            
            return $assignments;
            
        } catch (\Exception $e) {
            error_log("Error in getAssignmentsByFaculty: " . $e->getMessage());
            return [];
        } finally {
            // Reset memory limit
            ini_set('memory_limit', '512M');
        }
    }

    public static function getAll($conn) {
        ini_set('memory_limit', '512M');
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
        try {
            $sql = "SELECT submissions FROM assignments WHERE id = :assignment_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':assignment_id' => $assignment_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return 0; // No record found
            }
            
            $submissions = isset($result['submissions']) ? json_decode($result['submissions'], true) : [];
            return is_array($submissions) ? count($submissions) : 0;
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return 0; // Return zero submissions on error
        }
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
    
            if ($student) {
                $submissionDetails[] = array_merge($submission, $student);
            } else {
                // Handle case where student data is not found
                $submissionDetails[] = $submission;
            }
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

    public static function getAssignmentsByfacultyId($conn, $email) {
        // Step 1: Fetch assigned courses for the student
        $sql = "SELECT assigned_courses FROM faculty WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
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


    public static function create($conn, $title, $description, $start_date, $due_date, $course_ids, $file) {
        // Upload file to S3
        $s3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => 'latest',
            'credentials' => [
                'key' => AWS_ACCESS_KEY_ID,
                'secret' => AWS_SECRET_ACCESS_KEY,
            ],
        ]);

        $bucketName = AWS_BUCKET_NAME;
        $key = "assignments/$title/" . basename($file['name']);
        $filePath = $file['tmp_name'];

        try {
            $result = $s3Client->putObject([
                'Bucket' => $bucketName,
                'Key' => $key,
                'SourceFile' => $filePath,
                'ContentType' => $file['type'],
                'ACL' => 'public-read',
            ]);
            $fileUrl = $result['ObjectURL'];
        } catch (AwsException $e) {
            error_log($e->getMessage());
            return false;
        }

        // Save assignment details in the database
        $sql = "INSERT INTO assignments (title, description, start_time, due_date, course_id, file_content) VALUES (:title, :description, :start_time, :due_date, :course_id, :file_content)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':start_time' => $start_date,
            ':due_date' => $due_date,
            ':course_id' => json_encode($course_ids),
            ':file_content' => $fileUrl,
        ]);
        return $conn->lastInsertId();
    }

    public static function createpublic($conn, $title, $description, $start_date, $due_date, $course_ids, $file) {
        $course_ids = array_map(function($id) {
            return 'public:' . $id;
        }, $course_ids);

        $s3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => 'latest',
            'credentials' => [
                'key' => AWS_ACCESS_KEY_ID,
                'secret' => AWS_SECRET_ACCESS_KEY,
            ],
        ]);

        $bucketName = AWS_BUCKET_NAME;
        $key = "assignments/$title/" . basename($file['name']);
        $filePath = $file['tmp_name'];

        try {
            $result = $s3Client->putObject([
                'Bucket' => $bucketName,
                'Key' => $key,
                'SourceFile' => $filePath,
                'ContentType' => $file['type'],
                'ACL' => 'public-read',
            ]);
            $fileUrl = $result['ObjectURL'];
        } catch (AwsException $e) {
            error_log($e->getMessage());
            return false;
        }
        
        $course_ids_json = json_encode($course_ids);
        $sql = "INSERT INTO assignments (title, description, start_time, due_date, course_id, file_content) VALUES (:title, :description, :start_time, :due_date, :course_id, :file_content)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':start_time' => $start_date,
            ':due_date' => $due_date,
            ':course_id' => $course_ids_json,
            ':file_content' => $fileUrl
        ]);
        return $conn->lastInsertId();
    }
    public static function update($conn, $id, $title, $description, $start_date, $due_date, $course_ids, $file) {
        $fileUrl = null;

        if ($file) {
            // Upload file to S3
            $s3Client = new S3Client([
                'region' => AWS_REGION,
                'version' => 'latest',
                'credentials' => [
                    'key' => AWS_ACCESS_KEY_ID,
                    'secret' => AWS_SECRET_ACCESS_KEY,
                ],
            ]);

            $bucketName = AWS_BUCKET_NAME;
            $key = "assignments/$title/" . basename($file['name']);
            $filePath = $file['tmp_name'];

            // Detect file type and set metadata
            $fileType = mime_content_type($filePath);
            $metadata = [
                'ContentType' => $fileType,
            ];

            try {
                $result = $s3Client->putObject([
                    'Bucket' => $bucketName,
                    'Key' => $key,
                    'SourceFile' => $filePath,
                    'ACL' => 'public-read',
                    'ContentType' => $fileType,
                    'Metadata' => $metadata,
                ]);
                $fileUrl = $result['ObjectURL'];
            } catch (AwsException $e) {
                error_log($e->getMessage());
                return false;
            }
        }

        // Save assignment details in the database
        $sql = "UPDATE assignments SET title = :title, description = :description, start_time = :start_time, due_date = :due_date, course_id = :course_id, file_content = :file_content WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':start_time' => $start_date,
            ':due_date' => $due_date,
            ':course_id' => json_encode($course_ids),
            ':file_content' => $fileUrl,
            ':id' => $id
        ]);
    }

    public static function updatepublic($conn, $id, $title, $description, $start_date, $due_date, $course_ids, $file) {
        $course_ids = array_map(function($id) {
            return 'public:' . $id;
        }, $course_ids);

        
        $fileUrl = null;

        if ($file) {
            // Upload file to S3
            $s3Client = new S3Client([
                'region' => AWS_REGION,
                'version' => 'latest',
                'credentials' => [
                    'key' => AWS_ACCESS_KEY_ID,
                    'secret' => AWS_SECRET_ACCESS_KEY,
                ],
            ]);

            $bucketName = AWS_BUCKET_NAME;
            $key = "assignments/$title/" . basename($file['name']);
            $filePath = $file['tmp_name'];

            // Detect file type and set metadata
            $fileType = mime_content_type($filePath);
            $metadata = [
                'ContentType' => $fileType,
            ];

            try {
                $result = $s3Client->putObject([
                    'Bucket' => $bucketName,
                    'Key' => $key,
                    'SourceFile' => $filePath,
                    'ACL' => 'public-read',
                    'ContentType' => $fileType,
                    'Metadata' => $metadata,
                ]);
                $fileUrl = $result['ObjectURL'];
            } catch (AwsException $e) {
                error_log($e->getMessage());
                return false;
            }
        }

        $course_ids_json = json_encode($course_ids);
        // Save assignment details in the database
        $sql = "UPDATE assignments SET title = :title, description = :description, start_time = :start_time, due_date = :due_date, course_id = :course_id, file_content = :file_content WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':start_time' => $start_date,
            ':due_date' => $due_date,
            ':course_id' => $course_ids_json,
            ':file_content' => $fileUrl,
            ':id' => $id
        ]);
    }

    public static function delete($conn, $id) {
        $sql = "DELETE FROM assignments WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}