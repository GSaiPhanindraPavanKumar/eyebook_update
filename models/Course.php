<?php
namespace Models;

use PDO;
use ZipArchive;
use PDOException;
use Exception;

class Course {
    public static function getCount($conn) {
        $sql = "SELECT COUNT(*) as course_count FROM courses";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['course_count'] ?? 0;
    }

    public static function getAll($conn) {
        $sql = "SELECT * FROM courses";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($conn, $id) {
        $query = 'SELECT * FROM courses WHERE id = :id';
        $stmt = $conn->prepare($query);
        $stmt->execute(['id' => $id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($course) {
            // Decode JSON data
            $course['course_plan'] = isset($course['course_plan']) ? json_decode($course['course_plan'], true) : [];
            $course['course_book'] = isset($course['course_book']) ? json_decode($course['course_book'], true) : [];
            $course['course_materials'] = isset($course['course_materials']) ? json_decode($course['course_materials'], true) : [];
            if (!is_array($course['course_materials'])) {
                $course['course_materials'] = [];
            }
    
            // Sort course materials by unit number in ascending order
            usort($course['course_materials'], function($a, $b) {
                return $a['unitNumber'] <=> $b['unitNumber'];
            });
        }
    
        return $course;
    }

    public static function saveFeedback($conn, $course_id, $student_id, $feedback) {
        $query = 'SELECT feedback FROM courses WHERE id = :course_id';
        $stmt = $conn->prepare($query);
        $stmt->execute(['course_id' => $course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        $feedbacks = [];
        if ($course && !empty($course['feedback'])) {
            $feedbacks = json_decode($course['feedback'], true);
            if (!is_array($feedbacks)) {
                $feedbacks = [];
            }
        }

        $feedbacks[] = ['student_id' => $student_id, 'feedback' => $feedback];
        $feedbackJson = json_encode($feedbacks);

        $query = 'UPDATE courses SET feedback = :feedback WHERE id = :course_id';
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':feedback' => $feedbackJson,
            ':course_id' => $course_id
        ]);
    }
    
    public static function getFeedback($conn, $course_id) {
        $query = 'SELECT feedback FROM courses WHERE id = :course_id';
        $stmt = $conn->prepare($query);
        $stmt->execute(['course_id' => $course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
        $feedbacks = [];
        if ($course && !empty($course['feedback'])) {
            $feedbacks = json_decode($course['feedback'], true);
            if (!is_array($feedbacks)) {
                $feedbacks = [];
            }
        }
    
        return $feedbacks;
    }
    
    public static function getCoursesByFaculty($conn) {
        $sql = "SELECT * FROM courses";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($conn, $name, $description) {
        $sql = "SELECT COUNT(*) as count FROM courses WHERE name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($result['count'] > 0) {
            return "Course with this name already exists!";
        }
    
        $sql = "INSERT INTO courses (name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $name, PDO::PARAM_STR);
        $stmt->bindValue(2, $description, PDO::PARAM_STR);
    
        if ($stmt->execute()) {
            return "Course created successfully!";
        } else {
            $errorInfo = $stmt->errorInfo();
            return "Error creating course: " . $errorInfo[2];
        }
    }

    public static function getAllWithUniversity($conn) {
        $sql = "SELECT c.id, c.name, c.description, u.long_name, u.short_name as university_short_name
                FROM courses c 
                LEFT JOIN universities u ON c.university_id = u.id";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addUnit($conn, $course_id, $unit_name, $scorm_file) {
        if (empty($unit_name)) {
            return ['message' => 'Unit name is required'];
        }
        if (empty($scorm_file)) {
            return ['message' => 'SCORM package file is required'];
        }
        if ($scorm_file['error'] != 0) {
            return ['message' => 'Error uploading SCORM package file: ' . $scorm_file['error']];
        }
    
        $sql = "SELECT * FROM courses WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$course) {
            return ['message' => 'Course not found'];
        }
    
        $course_content = json_decode($course['course_book'], true);
        if (!is_array($course_content)) {
            $course_content = [];
        }
    
        $scorm_dir = "uploads/course-$course_id/course_book" . time() . '-' . basename($scorm_file['name'], '.zip');
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
            'materials' => [['scormDir' => $scorm_dir, 'indexPath' => $index_path]]
        ];
        $course_content = [$new_unit];
    
        $sql = "UPDATE courses SET course_book = :course_book WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $content_json = json_encode($course_content);
        $stmt->execute(['course_book' => $content_json, 'id' => $course_id]);
    
        return ['message' => 'Unit added successfully with SCORM content', 'indexPath' => $index_path];
    }

    public static function getCoursesByIds($conn, $course_ids) {
        if (empty($course_ids)) {
            return [];
        }
    
        $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
        $sql = "SELECT * FROM courses WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($course_ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function archiveCourse($conn, $course_id) {
        $query = 'UPDATE courses SET status = :status WHERE id = :course_id';
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':status' => 'archived',
            ':course_id' => $course_id
        ]);
    }
    public static function unarchiveCourse($conn, $course_id) {
        $query = 'UPDATE courses SET status = :status WHERE id = :course_id';
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':status' => 'ongoing',
            ':course_id' => $course_id
        ]);
    }

    public static function getOngoingCoursesByIds($conn, $course_ids) {
        if (empty($course_ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
        $sql = "SELECT * FROM courses WHERE id IN ($placeholders) AND status = 'ongoing'";
        $stmt = $conn->prepare($sql);
        $stmt->execute($course_ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addAssignmentToCourse($conn, $course_id, $assignment_id) {
        $sql = "SELECT assignments FROM courses WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $course_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $assignments = $result ? json_decode($result['assignments'], true) : [];
        $assignments[] = $assignment_id;

        $sql = "UPDATE courses SET assignments = :assignments WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':assignments' => json_encode($assignments),
            ':id' => $course_id
        ]);
    }

    public static function hasFeedback($conn, $course_id, $student_id) {
        $query = 'SELECT feedback FROM courses WHERE id = :course_id';
        $stmt = $conn->prepare($query);
        $stmt->execute(['course_id' => $course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($course && !empty($course['feedback'])) {
            $feedbacks = json_decode($course['feedback'], true);
            if (is_array($feedbacks)) {
                foreach ($feedbacks as $feedback) {
                    if (isset($feedback['student_id']) && $feedback['student_id'] == $student_id) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static function getAssignedFaculty($conn, $course_id) {
        $sql = "SELECT assigned_faculty FROM courses WHERE id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':course_id' => $course_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && !empty($result['assigned_faculty'])) {
            $assignedFaculty = json_decode($result['assigned_faculty'], true);
            if (!empty($assignedFaculty)) {
                $placeholders = implode(',', array_fill(0, count($assignedFaculty), '?'));
                $sql = "SELECT id, name, email FROM faculty WHERE id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->execute($assignedFaculty);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return [];
    }

    public static function getVirtualClassIds($conn, $courseIds) {
        if (empty($courseIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
        $sql = "SELECT virtual_class_id FROM courses WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($courseIds);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $virtualClassIds = [];
        foreach ($results as $result) {
            $ids = !empty($result['virtual_class_id']) ? json_decode($result['virtual_class_id'], true) : [];
            if (is_array($ids)) {
                $virtualClassIds = array_merge($virtualClassIds, $ids);
            }
        }

        return array_unique($virtualClassIds);
    }

    public static function getVirtualClassIdsForCourses($conn, $courseIds) {
        if (empty($courseIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
        $sql = "SELECT virtual_class_id FROM courses WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($courseIds);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $virtualClassIds = [];
        foreach ($results as $result) {
            $ids = json_decode($result['virtual_class_id'], true);
            if (is_array($ids)) {
                $virtualClassIds = array_merge($virtualClassIds, $ids);
            }
        }

        return array_unique($virtualClassIds);
    }

    public static function getAssignedStudents($conn, $course_id) {
        $sql = "SELECT assigned_students FROM courses WHERE id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':course_id' => $course_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && !empty($result['assigned_students'])) {
            $assignedStudents = json_decode($result['assigned_students'], true);
            if (!empty($assignedStudents)) {
                $placeholders = implode(',', array_fill(0, count($assignedStudents), '?'));
                $sql = "SELECT id, name, email, completed_books FROM students WHERE id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->execute($assignedStudents);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return [];
    }

    public static function getAssignedStudentIds($conn, $courseIds) {
        if (empty($courseIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
        $sql = "SELECT assigned_students FROM courses WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($courseIds);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $studentIds = [];
        foreach ($results as $result) {
            $ids = !empty($result['assigned_students']) ? json_decode($result['assigned_students'], true) : [];
            if (is_array($ids)) {
                $studentIds = array_merge($studentIds, $ids);
            }
        }

        return array_unique($studentIds);
    }

    public static function assignFaculty($conn, $course_id, $faculty_id) {
        $course = self::getById($conn, $course_id);
        $assigned_faculty = $course['assigned_faculty'] ? json_decode($course['assigned_faculty'], true) : [];
        if (!in_array($faculty_id, $assigned_faculty)) {
            $assigned_faculty[] = $faculty_id;
            $sql = "UPDATE courses SET assigned_faculty = :assigned_faculty WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':assigned_faculty' => json_encode($assigned_faculty),
                ':id' => $course_id
            ]);
        }
    }

    public static function assignStudents($conn, $course_id, $student_ids) {
        $course = self::getById($conn, $course_id);
        $assigned_students = $course['assigned_students'] ? json_decode($course['assigned_students'], true) : [];
        foreach ($student_ids as $student_id) {
            if (!in_array($student_id, $assigned_students)) {
                $assigned_students[] = $student_id;
            }
        }
        $sql = "UPDATE courses SET assigned_students = :assigned_students WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':assigned_students' => json_encode($assigned_students),
            ':id' => $course_id
        ]);
    }

    public static function unassignFaculty($conn, $course_id, $faculty_ids) {
        $course = self::getById($conn, $course_id);
        $assigned_faculty = $course['assigned_faculty'] ? json_decode($course['assigned_faculty'], true) : [];
        if (!empty($faculty_ids)) {
            $assigned_faculty = array_diff($assigned_faculty, $faculty_ids);
        }
        $sql = "UPDATE courses SET assigned_faculty = :assigned_faculty WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':assigned_faculty' => json_encode(array_values($assigned_faculty)), // Ensure the array is re-indexed
            ':id' => $course_id
        ]);
    }

    public static function unassignStudents($conn, $course_id, $student_ids) {
        $course = self::getById($conn, $course_id);
        $assigned_students = $course['assigned_students'] ? json_decode($course['assigned_students'], true) : [];
        if (!empty($student_ids)) {
            $assigned_students = array_diff($assigned_students, $student_ids);
        }
        $sql = "UPDATE courses SET assigned_students = :assigned_students WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':assigned_students' => json_encode(array_values($assigned_students)), // Ensure the array is re-indexed
            ':id' => $course_id
        ]);
    }

    public static function assignCourseToUniversity($conn, $course_id, $university_id, $confirm) {
        $sql = "SELECT * FROM courses WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$course) {
            return ['message' => 'Course not found'];
        }
    
        $current_university_id = $course['university_id'];
        if ($current_university_id && $current_university_id != $university_id) {
            if (!$confirm) {
                return ['message' => 'Course is already assigned to another university. Do you want to reassign it?', 'confirm' => true];
            }
        }
    
        $sql = "UPDATE courses SET university_id = :university_id WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['university_id' => $university_id, 'id' => $course_id]);
    
        return ['message' => 'Course assigned to university successfully'];
    }

    public static function unassignCourseFromUniversity($conn, $course_id, $university_id) {
        $sql = "SELECT university_id FROM courses WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$course) {
            return ['message' => 'Course not found'];
        }
    
        $assigned_universities = $course['university_id'] ? json_decode($course['university_id'], true) : [];
        if (!is_array($assigned_universities)) {
            $assigned_universities = [];
        }
        if (in_array($university_id, $assigned_universities)) {
            $assigned_universities = array_diff($assigned_universities, [$university_id]);
            $sql = "UPDATE courses SET university_id = :university_id WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':university_id' => json_encode(array_values($assigned_universities)),
                ':id' => $course_id
            ]);
        }
    
        return ['message' => 'Course unassigned from university successfully'];
    }

    public static function getAssignedUniversities($conn, $course_id) {
        $sql = "SELECT university_id FROM courses WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($course && !empty($course['university_id'])) {
            $university_ids = json_decode($course['university_id'], true);
            if (!is_array($university_ids)) {
                $university_ids = [];
            }
            if (!empty($university_ids)) {
                $placeholders = implode(',', array_fill(0, count($university_ids), '?'));
                $sql = "SELECT id, long_name FROM universities WHERE id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->execute($university_ids);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    
        return [];
    }

    public static function getCountByUniversityId($conn, $university_id) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM courses WHERE university_id = :university_id");
        $stmt->execute([':university_id' => $university_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public static function getAllByUniversity($conn, $university_id) {
        $stmt = $conn->prepare("SELECT * FROM courses WHERE university_id = :university_id");
        $stmt->execute([':university_id' => $university_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>