<?php
namespace Models;

use PDO;

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
    }

?>