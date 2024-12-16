<?php
namespace Models;

use PDO;

class Discussion {
    public static function getDiscussionsByUniversity($conn, $university_id) {
        $sql = "SELECT * FROM discussion WHERE university_id = :university_id AND parent_post_id IS NULL ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':university_id' => $university_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addDiscussion($conn, $name, $post, $university_id, $parent_post_id = null) {
        $sql = "INSERT INTO discussion (parent_post_id, name, post, university_id) 
                VALUES (:parent_post_id, :name, :post, :university_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':parent_post_id' => $parent_post_id,
            ':name' => $name,
            ':post' => $post,
            ':university_id' => $university_id
        ]);
    }

    public static function getDiscussionById($conn, $id) {
        $sql = "SELECT * FROM discussion WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getReplies($conn, $parent_post_id) {
        $sql = "SELECT * FROM discussion WHERE parent_post_id = :parent_post_id ORDER BY created ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':parent_post_id' => $parent_post_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}