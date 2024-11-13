<?php
namespace Models;

use PDO;

class Discussion {
    public static function getDiscussionsByCourse($conn, $course_id) {
        $sql = "SELECT * FROM discussion WHERE course_id = :course_id ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':course_id' => $course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addDiscussion($conn, $username, $msg, $course_id) {
        $date = date("Y-m-d H:i:s");
        $json_data = json_encode(array("name" => $username, "message" => $msg, "date" => $date));
        $sql = "INSERT INTO discussion (student, post, json_data, course_id) 
                VALUES (:username, :msg, :json_data, :course_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':msg' => $msg,
            ':json_data' => $json_data,
            ':course_id' => $course_id
        ]);
    }
}