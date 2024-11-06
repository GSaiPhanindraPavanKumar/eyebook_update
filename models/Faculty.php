<?php
namespace Models;

use PDO;

class Faculty {
    public static function getCount($conn) {
        $sql = "SELECT COUNT(*) as Faculty_count FROM faculty";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['Faculty_count'] ?? 0;
    }

    public static function getAll($conn) {
        $sql = "SELECT * FROM faculty";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function existsByEmail($conn, $email) {
        $sql = "SELECT COUNT(*) as count FROM faculty WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function login($username, $password) {
        $conn = Database::getConnection();
        $sql = "SELECT * FROM faculty WHERE email = :username";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':username' => $username]);
        $Faculty = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($Faculty && password_verify($password, $Faculty['password'])) {
            return $Faculty;
        }

        return false;
    }

    public function getUserProfile($conn) {
        $query = 'SELECT * FROM faculty where email = :email';
        $stmt = $conn->prepare($query);
        $stmt->execute(['email' => $_SESSION['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user;
    }

        public static function updatePassword($conn, $Faculty_id, $new_password) {
            $sql = "UPDATE faculty SET password = :new_password WHERE id = :Faculty_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':new_password' => $new_password,
                ':Faculty_id' => $Faculty_id
            ]);
        }

        
}