<?php
namespace Controllers;

use Models\Discussion;
use Models\Database;
use Models\Student;
use \PDO;
use \Exception;

class DiscussionController {
    private function calculateXP($type, $message) {
        $baseXP = ($type === 'question') ? 6 : ($type === 'answer' ? 8 : 1);
        $wordCount = str_word_count($message);
        
        // Additional XP for longer posts
        if ($type === 'question' && $wordCount > 100) {
            $baseXP += 4;
        } elseif ($type === 'answer' && $wordCount > 150) {
            $baseXP += 4;
        }
        
        return $baseXP;
    }

    private function updateStudentXP($conn, $studentId, $xpGained) {
        $stmt = $conn->prepare("SELECT xp, level FROM students WHERE id = ?");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $currentXP = ($student['xp'] ?? 0) + $xpGained;
        $currentLevel = $student['level'] ?? 0;
        
        // Calculate new level
        $newLevel = floor($currentXP / 100);
        
        // Update student XP and level
        $stmt = $conn->prepare("UPDATE students SET xp = ?, level = ? WHERE id = ?");
        $stmt->execute([$currentXP, $newLevel, $studentId]);
        
        return [
            'levelUp' => $newLevel > $currentLevel,
            'newLevel' => $newLevel,
            'currentXP' => $currentXP
        ];
    }

    public function facultyForum($course_id) {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $msg = $_POST['msg'];
            $username = $_SESSION['email'];
            Discussion::addDiscussion($conn, $username, $msg, $course_id);
        }
        $discussions = Discussion::getDiscussionsByCourse($conn, $course_id);
        require 'views/faculty/discussion_forum.php';
    }

    public function studentForum($course_id = null) {
        $conn = Database::getConnection();
        $response = ['status' => 'success'];
        
        // Get student info
        $studentId = $_SESSION['student_id'];
        
        // Get student data in one query
        $stmt = $conn->prepare("SELECT name, university_id FROM students WHERE id = ?");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            $_SESSION['error'] = 'Student not found';
            header('Location: /student/dashboard');
            exit;
        }
        
        $username = $student['name'];
        $universityId = $student['university_id'];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $msg = filter_input(INPUT_POST, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $parent_post_id = filter_input(INPUT_POST, 'parent_post_id', FILTER_VALIDATE_INT);
            $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $type = filter_input(INPUT_POST, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'question';

            if ($csrf_token !== $_SESSION['csrf_token']) {
                $response['status'] = 'error';
                $response['message'] = 'CSRF token validation failed';
                echo json_encode($response);
                exit;
            }

            if (!empty($msg)) {
                try {
                    // Add discussion post using fetched username
                    Discussion::addDiscussion($conn, $username, $msg, $universityId, $type, $parent_post_id);
                    
                    // Calculate XP for the post
                    $xpGained = $this->calculateXP($type, $msg);
                    
                    // Update student's XP and check for level up
                    $xpUpdate = $this->updateStudentXP($conn, $studentId, $xpGained);
                    
                    if ($xpUpdate['levelUp']) {
                        $response['levelUp'] = true;
                        $response['newLevel'] = $xpUpdate['newLevel'];
                    }
                    
                    $response['xpGained'] = $xpGained;
                    $response['currentXP'] = $xpUpdate['currentXP'];
                    
                    // Return JSON response for AJAX requests
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode($response);
                        exit;
                    }
                    
                    // Redirect for non-AJAX requests
                    header('Location: /student/discussion_forum');
                    exit;
                } catch (Exception $e) {
                    $response['status'] = 'error';
                    $response['message'] = $e->getMessage();
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                        header('Content-Type: application/json');
                        echo json_encode($response);
                        exit;
                    }
                }
            }
        }
        
        // Fetch discussions based on context
        $discussions = $course_id ? 
            Discussion::getDiscussionsByCourse($conn, $course_id) :
            Discussion::getDiscussionsByUniversity($conn, $universityId);
        
        require 'views/student/discussion_forum.php';
    }

    public function studentReply() {
        $conn = Database::getConnection();
        $response = ['status' => 'success'];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $msg = $_POST['msg'];
            $type = 'answer'; // Replies are always answers
            $parent_post_id = $_POST['parent_post_id'];
            $studentId = $_SESSION['student_id'];
            
            // Get student name from database with error handling
            $stmt = $conn->prepare("SELECT name FROM students WHERE id = ?");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                $response['status'] = 'error';
                $response['message'] = 'Student not found';
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                $_SESSION['error'] = 'Student not found';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            $username = $student['name'];
            
            // Calculate XP for the reply
            $xpGained = $this->calculateXP($type, $msg);
            
            // Add the reply using fetched username
            Discussion::addReply($conn, $username, $msg, $parent_post_id);
            
            // Update student's XP and check for level up
            $xpUpdate = $this->updateStudentXP($conn, $studentId, $xpGained);
            
            if ($xpUpdate['levelUp']) {
                $response['levelUp'] = true;
                $response['newLevel'] = $xpUpdate['newLevel'];
            }
            
            $response['xpGained'] = $xpGained;
            $response['currentXP'] = $xpUpdate['currentXP'];
            
            // If it's an AJAX request, return JSON response
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            
            // Redirect back to discussion forum if not AJAX
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    public function toggleLike() {
        if (!isset($_SESSION['student_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
            exit;
        }

        $conn = Database::getConnection();
        $discussionId = filter_input(INPUT_POST, 'discussion_id', FILTER_VALIDATE_INT);
        $studentId = $_SESSION['student_id'];

        try {
            // Get the post author's ID
            $stmt = $conn->prepare("SELECT name FROM discussion WHERE id = ?");
            $stmt->execute([$discussionId]);
            $post = $stmt->fetch();
            
            if (!$post) {
                throw new Exception("Post not found");
            }

            // Get the author's student ID
            $stmt = $conn->prepare("SELECT id FROM students WHERE name = ?");
            $stmt->execute([$post['name']]);
            $author = $stmt->fetch();
            $authorId = $author['id'];

            // Toggle like and get result
            $result = Discussion::toggleLike($conn, $discussionId, $studentId);
            
            // Update XP based on action
            if ($result['action'] === 'liked') {
                // Add XP to post author
                $xpUpdate = $this->updateStudentXP($conn, $authorId, 3);
                $result['authorXpGained'] = 3;
                if ($xpUpdate['levelUp']) {
                    $result['authorLevelUp'] = true;
                    $result['authorNewLevel'] = $xpUpdate['newLevel'];
                }
            } else {
                // Remove XP from post author
                $xpUpdate = $this->updateStudentXP($conn, $authorId, -3);
                $result['authorXpLost'] = 3;
            }

            // Get updated like count
            $result['likeCount'] = Discussion::getLikeCount($conn, $discussionId);
            $result['status'] = 'success';

            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}