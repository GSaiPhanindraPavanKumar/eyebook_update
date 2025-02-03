<?php
namespace Models;

use PDO;
use PDOException;

class Discussion {
    public static function getDiscussionsByUniversity($conn, $university_id) {
        try {
            $sql = "SELECT d.*, s.level, s.xp 
                    FROM discussion d 
                    LEFT JOIN students s ON d.name = s.name 
                    WHERE d.university_id = :university_id 
                    AND (d.parent_post_id = '0' OR d.parent_post_id IS NULL) 
                    ORDER BY d.id DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':university_id' => $university_id]);
            
            // Debug output
            error_log("Fetching discussions for university_id: " . $university_id);
            error_log("SQL: " . $sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Results: " . print_r($results, true));
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getDiscussionsByUniversity: " . $e->getMessage());
            throw $e;
        }
    }

    public static function addDiscussion($conn, $name, $post, $university_id, $type = 'question', $parent_post_id = null) {
        try {
            $sql = "INSERT INTO discussion (parent_post_id, name, post, university_id, type) 
                    VALUES (:parent_post_id, :name, :post, :university_id, :type)";
            $stmt = $conn->prepare($sql);
            
            $params = [
                ':parent_post_id' => $parent_post_id,
                ':name' => $name,
                ':post' => $post,
                ':university_id' => $university_id,
                ':type' => $type
            ];
            
            // Debug output
            error_log("Adding discussion with params: " . print_r($params, true));
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                error_log("Error executing query: " . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error in addDiscussion: " . $e->getMessage());
            throw $e;
        }
    }

    public static function addReply($conn, $name, $post, $parent_post_id, $type = 'answer') {
        // Get the university_id from the parent post
        $sql = "SELECT university_id FROM discussion WHERE id = :parent_post_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':parent_post_id' => $parent_post_id]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($parent) {
            $sql = "INSERT INTO discussion (parent_post_id, name, post, university_id, type) 
                    VALUES (:parent_post_id, :name, :post, :university_id, :type)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':parent_post_id' => $parent_post_id,
                ':name' => $name,
                ':post' => $post,
                ':university_id' => $parent['university_id'],
                ':type' => $type
            ]);
        }
    }

    public static function getDiscussionById($conn, $id) {
        $sql = "SELECT * FROM discussion WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getReplies($conn, $parent_post_id) {
        try {
            $sql = "SELECT d.*, s.level, s.xp 
                    FROM discussion d 
                    LEFT JOIN students s ON d.name = s.name 
                    WHERE d.parent_post_id = :parent_post_id 
                    ORDER BY d.id ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':parent_post_id' => $parent_post_id]);
            
            // Debug output
            error_log("Fetching replies for parent_post_id: " . $parent_post_id);
            error_log("SQL: " . $sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Results: " . print_r($results, true));
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getReplies: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getReplyCount($conn, $parent_post_id) {
        try {
            $sql = "SELECT COUNT(*) as count FROM discussion WHERE parent_post_id = :parent_post_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':parent_post_id' => $parent_post_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (PDOException $e) {
            error_log("Error in getReplyCount: " . $e->getMessage());
            throw $e;
        }
    }

    public static function toggleLike($conn, $discussionId, $studentId) {
        try {
            // Check if already liked
            $sql = "SELECT id FROM discussion_likes 
                    WHERE discussion_id = :discussion_id AND student_id = :student_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':discussion_id' => $discussionId,
                ':student_id' => $studentId
            ]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Unlike
                $sql = "DELETE FROM discussion_likes 
                        WHERE discussion_id = :discussion_id AND student_id = :student_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':discussion_id' => $discussionId,
                    ':student_id' => $studentId
                ]);
                return ['action' => 'unliked'];
            } else {
                // Like
                $sql = "INSERT INTO discussion_likes (discussion_id, student_id) 
                        VALUES (:discussion_id, :student_id)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':discussion_id' => $discussionId,
                    ':student_id' => $studentId
                ]);
                return ['action' => 'liked'];
            }
        } catch (PDOException $e) {
            error_log("Error in toggleLike: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getLikeCount($conn, $discussionId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM discussion_likes WHERE discussion_id = :discussion_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':discussion_id' => $discussionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (PDOException $e) {
            error_log("Error in getLikeCount: " . $e->getMessage());
            throw $e;
        }
    }

    public static function hasUserLiked($conn, $discussionId, $studentId) {
        try {
            $sql = "SELECT id FROM discussion_likes 
                    WHERE discussion_id = :discussion_id AND student_id = :student_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':discussion_id' => $discussionId,
                ':student_id' => $studentId
            ]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Error in hasUserLiked: " . $e->getMessage());
            throw $e;
        }
    }
}