<?php
namespace Models;
use PDO;
use PDOException;

class Admin {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function login($username, $password) {
        // Assuming the correct column name is 'username' instead of 'email'
        $query = 'SELECT * FROM admins WHERE username = :username';
        $stmt = $this->db->connect()->prepare($query);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        } else {
            return false;
        }
    }

    public function getUserProfile($conn) {
        $query = 'SELECT * FROM admins WHERE id = :id';
        $stmt = $conn->prepare($query);
        $stmt->execute(['id' => $_SESSION['admin']['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debugging: Check if last_login is retrieved
        error_log('Last login: ' . $user['last_login']);
        
        return $user;
    }

    public static function getAll($conn) {
        $sql = "SELECT id, name, email, last_login FROM admins";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function getById($conn, $id) {
        $sql = "SELECT * FROM admins WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updatePassword($conn, $id, $password) {
        $sql = "UPDATE admins SET password = :password WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':password', $password);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function updateLastLogin($admin_id) {
        $conn = Database::getConnection();
        $sql = "UPDATE admins SET last_login = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$admin_id]);
    }

    // public function userprofile() {
    //     // Assuming the correct column name is 'username' instead of 'email'
    //     $query = 'SELECT * FROM admins WHERE username = :username';
    //     $stmt = $this->db->connect()->prepare($query);
    //     $stmt->execute(['username' => $_SESSION['admin']]);
    //     $user = $stmt->fetch(PDO::FETCH_ASSOC);
    //     return $user;
    // }
}
?>