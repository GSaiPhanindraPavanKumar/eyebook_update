<?php
namespace Models;

use PDO;

class Spoc {
    public static function getCount($conn) {
        $sql = "SELECT COUNT(*) as spoc_count FROM spocs";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['spoc_count'] ?? 0;
    }

    public static function getAll($conn) {
        $sql = "SELECT * FROM spocs";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function existsByEmail($conn, $email) {
        $sql = "SELECT COUNT(*) as count FROM spocs WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function login($username, $password) {
        $conn = Database::getConnection();
        $sql = "SELECT * FROM spocs WHERE email = :username";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':username' => $username]);
        $spoc = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($spoc && password_verify($password, $spoc['password'])) {
            return $spoc;
        }

        return false;
    }

    public function getUserProfile($conn) {
        $query = 'SELECT * FROM spocs';
        $stmt = $conn->prepare($query);
        // $stmt->execute(['id' => $_SESSION['spoc']['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user;
    }

        public static function updatePassword($conn, $spoc_id, $new_password) {
            $sql = "UPDATE spocs SET password = :new_password WHERE id = :spoc_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':new_password' => $new_password,
                ':spoc_id' => $spoc_id
            ]);
        }

        
}