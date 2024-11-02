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
        return $user;
    }


    public static function updatePassword($conn, $admin_id, $new_password) {
        $stmt = $conn->prepare("UPDATE admins SET password = :new_password WHERE id = :admin_id");
        $stmt->execute([
            ':new_password' => $new_password,
            ':admin_id' => $admin_id
        ]);
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