<?php 
namespace Models;

use PDO;
use PDOException;
use Models\Database;

class Meetings {
    public static function getCount($conn) {
        $sql = "SELECT COUNT(*) as count FROM virtual_classrooms";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

}