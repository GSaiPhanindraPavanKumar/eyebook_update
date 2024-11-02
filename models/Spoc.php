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
}
?>