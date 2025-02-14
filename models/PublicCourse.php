<?php
namespace Models;

use PDO;
use ZipArchive;
use PDOException;
use Exception;

class PublicCourse {
    public static function create($conn, $name, $description, $price) {
        $sql = "SELECT COUNT(*) as count FROM public_courses WHERE name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            return "Public course with this name already exists!";
        }

        $sql = "INSERT INTO public_courses (name, description, price) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $name, PDO::PARAM_STR);
        $stmt->bindValue(2, $description, PDO::PARAM_STR);
        $stmt->bindValue(3, $price, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "Public course created successfully!";
        } else {
            $errorInfo = $stmt->errorInfo();
            return "Error creating public course: " . $errorInfo[2];
        }
    }

    public static function getAll($conn) {
        $sql = "SELECT * FROM public_courses";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($conn, $id) {
        $query = 'SELECT * FROM public_courses WHERE id = :id';
        $stmt = $conn->prepare($query);
        $stmt->execute(['id' => $id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($course) {
            // Decode JSON data
            $course['course_book'] = isset($course['course_book']) ? json_decode($course['course_book'], true) : [];
            $course['EC_content'] = isset($course['EC_content']) ? json_decode($course['EC_content'], true) : [];
            $course['additional_content'] = isset($course['additional_content']) ? json_decode($course['additional_content'], true) : [];
        }
        return $course;
    }

    

    public static function getCount($conn) {
        $sql = "SELECT COUNT(*) as course_count FROM public_courses";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['course_count'] ?? 0;
    }

    public static function getTransactionsCount($conn) {
        $sql = "SELECT COUNT(*) as course_count FROM transactions";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['course_count'] ?? 0;
    }
    public static function getAssignments($conn, $courseId) {
        $courseIdStr = "public:$courseId";
        $sql = "SELECT * FROM assignments WHERE JSON_CONTAINS(course_id, :course_id, '$')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':course_id' => json_encode($courseIdStr)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function archiveCourse($conn, $course_id) {
        $query = 'UPDATE public_courses SET status = :status WHERE id = :course_id';
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':status' => 'archived',
            ':course_id' => $course_id
        ]);
    }

    public static function updateAdditionalContent($conn, $course_id, $additional_content) {
        $sql = "UPDATE public_courses SET additional_content = :additional_content WHERE id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':additional_content' => json_encode($additional_content),
            ':course_id' => $course_id
        ]);
    }

    public static function unarchiveCourse($conn, $course_id) {
        $query = 'UPDATE public_courses SET status = :status WHERE id = :course_id';
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':status' => 'ongoing',
            ':course_id' => $course_id
        ]);
    }

    public static function addAssignmentToCourse($conn, $course_id, $assignment_id) {
        $sql = "SELECT assignments FROM public_courses WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $course_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $assignments = $result ? json_decode($result['assignments'], true) : [];
        $assignments[] = $assignment_id;

        $sql = "UPDATE public_courses SET assignments = :assignments WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':assignments' => json_encode($assignments),
            ':id' => $course_id
        ]);
    }
    public static function updateFeedbackStatus($conn, $course_id, $feedback_enabled) {
        $sql = "UPDATE public_courses SET feedback_enabled = :feedback_enabled WHERE id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':feedback_enabled' => $feedback_enabled,
            ':course_id' => $course_id
        ]);
    }

    public static function getEnrolledCourses($conn, $studentId) {
        $sql = "SELECT * FROM public_courses WHERE JSON_CONTAINS(enrolled_students, :student_id, '$')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':student_id' => json_encode((string)$studentId)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getFeaturedCourses($conn) {
        $sql = "SELECT * FROM public_courses";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function enrollStudent($conn, $courseId, $studentId) {
        // Fetch the current enrolled students
        $sql = "SELECT enrolled_students FROM public_courses WHERE id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':course_id' => $courseId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        $enrolledStudents = json_decode($course['enrolled_students'], true) ?? [];
        $studentIdStr = (string)$studentId;
        if (!in_array($studentIdStr, $enrolledStudents)) {
            $enrolledStudents[] = $studentIdStr;
        }

        // Update the enrolled students
        $sql = "UPDATE public_courses SET enrolled_students = :enrolled_students WHERE id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':enrolled_students' => json_encode($enrolledStudents),
            ':course_id' => $courseId
        ]);
    }

    public static function getFeedback($conn, $course_id) {
        $sql = "SELECT * FROM public_feedback WHERE course_id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':course_id' => $course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addEcContent($conn, $course_id, $unit_name, $scorm_file) {
        if (empty($unit_name)) {
            return ['message' => 'Unit name is required'];
        }
        if (empty($scorm_file)) {
            return ['message' => 'EC Content file is required'];
        }
        if ($scorm_file['error'] != 0) {
            return ['message' => 'Error uploading EC Content file: ' . $scorm_file['error']];
        }
    
        $sql = "SELECT * FROM public_courses WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$course) {
            return ['message' => 'Course not found'];
        }
    
        $course_content = json_decode($course['EC_content'], true);
        if (!is_array($course_content)) {
            $course_content = [];
        }
    
        $scorm_dir = "uploads/public_course-$course_id/EC" . time() . '-' . basename($scorm_file['name'], '.zip');
        if (!mkdir($scorm_dir, 0777, true)) {
            return ['message' => 'Failed to create directory for SCORM package'];
        }
    
        $zip = new ZipArchive;
        if ($zip->open($scorm_file['tmp_name']) === TRUE) {
            $zip->extractTo($scorm_dir);
            $zip->close();
        } else {
            return ['message' => 'Failed to unzip SCORM package'];
        }
    
        $index_path = $scorm_dir . '/index.html';
        if (!file_exists($index_path)) {
            return ['message' => 'index.html file not found'];
        }
    
        $new_unit = [
            'unitTitle' => $unit_name,
            'indexPath' => $index_path
        ];
        $course_content[] = $new_unit;
    
        $sql = "UPDATE public_courses SET EC_content = :ec_content WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $content_json = json_encode($course_content);
        $stmt->execute(['ec_content' => $content_json, 'id' => $course_id]);
    
        return ['message' => 'Unit added successfully with EC content', 'indexPath' => $index_path];
    }

    public static function addCourseBook($conn, $course_id, $unit_name, $scorm_file_path) {
        $query = 'UPDATE public_courses SET course_book = JSON_ARRAY_APPEND(course_book, "$", JSON_OBJECT("unit_name", :unit_name, "scorm_url", :scorm_file_path)) WHERE id = :course_id';
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':unit_name' => $unit_name,
            ':scorm_file_path' => $scorm_file_path,
            ':course_id' => $course_id
        ]);
    }

    public static function addAdditionalContent($conn, $course_id, $title, $link) {
        $query = 'UPDATE public_courses SET additional_content = JSON_ARRAY_APPEND(additional_content, "$", JSON_OBJECT("title", :title, "link", :link)) WHERE id = :course_id';
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':title' => $title,
            ':link' => $link,
            ':course_id' => $course_id
        ]);
    }
}