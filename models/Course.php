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

    // public static function getById($conn, $id) {
    //     $query = 'SELECT * FROM courses WHERE id = :id';
    //     $stmt = $conn->prepare($query);
    //     $stmt->execute(['id' => $id]);
    //     $course = $stmt->fetch(PDO::FETCH_ASSOC);

    //     // Ensure universities field is an array
    //     $course['universities'] = isset($course['universities']) ? json_decode($course['universities'], true) : [];

    //     return $course;
    // }

    public static function getUniversitiesByCourseId($conn, $course) {
        $universities = [];
        if (!empty($course['universities'])) {
            $universityIds = implode(',', array_map('intval', $course['universities']));
            $query = "SELECT * FROM universities WHERE id IN ($universityIds)";
            $stmt = $conn->query($query);
            $universities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $universities;
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
                $course['universities'] = isset($course['universities']) ? json_decode($course['universities'], true) : [];
                if ($course['universities'] === null) {
                    $course['universities'] = [];
                }
                // Ensure course_materials is an array
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
        public static function getCoursesByFaculty($conn) {
            $sql = "SELECT * FROM courses";
            $stmt = $conn->query($sql); // Use query instead of prepare and execute
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            return $courses;
        }

        public static function create($conn, $name, $description, $is_paid, $price) {
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
            $sql = "SELECT c.id, c.name, c.description, u.long_name 
                    FROM courses c 
                    LEFT JOIN universities u ON c.university_id = u.id";
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    
        public static function addUnit($conn, $course_id, $unit_name, $scorm_file) {
            // Validate input
            if (empty($unit_name) || empty($scorm_file) || $scorm_file['error'] != 0) {
                return ['message' => 'Unit name and SCORM package file are required'];
            }
        
            // Fetch the course
            $sql = "SELECT * FROM courses WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $course_id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if (!$course) {
                return ['message' => 'Course not found'];
            }
        
            // Decode the existing content or initialize an empty array
            $course_content = json_decode($course['course_book'], true);
            if (!is_array($course_content)) {
                $course_content = [];
            }
        
            // Create a directory for the SCORM package
            $scorm_dir = "uploads/course-$course_id/course_book" . time() . '-' . basename($scorm_file['name'], '.zip');
            mkdir($scorm_dir, 0777, true);
        
            // Unzip the SCORM package directly to the created directory
            $zip = new ZipArchive;
            if ($zip->open($scorm_file['tmp_name']) === TRUE) {
                $zip->extractTo($scorm_dir);
                $zip->close();
            } else {
                return ['message' => 'Failed to unzip SCORM package'];
            }
        
            // Verify that the index.html file exists
            $index_path = $scorm_dir . '/index.html';
            if (!file_exists($index_path)) {
                return ['message' => 'index.html file not found'];
            }
        
            // Replace the existing course book path if it exists
            $new_unit = [
                'unitTitle' => $unit_name,
                'materials' => [['scormDir' => $scorm_dir, 'indexPath' => $index_path]]
            ];
            $course_content = [$new_unit]; // Replace the entire course book content with the new unit
        
            // Update the course in the database
            $sql = "UPDATE courses SET course_book = :course_book WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $content_json = json_encode($course_content);
            $stmt->execute(['course_book' => $content_json, 'id' => $course_id]);
        
            return ['message' => 'Unit added successfully with SCORM content', 'indexPath' => $index_path];
        }
    
        public static function assignCourseToUniversity($conn, $course_id, $university_id) {
            // Fetch the course
            $sql = "SELECT * FROM courses WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $course_id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$course) {
                return ['message' => 'Course not found'];
            }
    
            // Decode the existing universities or initialize an empty array
            $universities = isset($course['universities']) ? json_decode($course['universities'], true) : [];
            if (!is_array($universities)) {
                $universities = [];
            }
    
            // Add the new university to the course
            if (!in_array($university_id, $universities)) {
                $universities[] = $university_id;
            }
    
            // Update the course in the database
            $sql = "UPDATE courses SET universities = :universities WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $universities_json = json_encode($universities);
            $stmt->execute(['universities' => $universities_json, 'id' => $course_id]);
    
            return ['message' => 'Course assigned to university successfully'];
        }
    
        // public static function getCoursesByFaculty($conn) {
        //     $sql = "SELECT * FROM courses";
        //     $stmt = $conn->query($sql); // Use query instead of prepare and execute
        //     $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        //     return $courses;
        // }
    }
    ?>