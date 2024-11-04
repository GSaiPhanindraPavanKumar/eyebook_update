<?php 
namespace Models;

use PDO;
use PDOException;

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // $this->host = getenv('DB_HOST') ?: 'localhost';
        // $this->db_name = getenv('DB_NAME') ?: 'eyebook';
        // $this->username = getenv('DB_USERNAME') ?: 'eyebook_user';
        // $this->password = getenv('DB_PASSWORD') ?: '#*Admin123*#';
        $this->host = 'localhost';
        $this->db_name = 'eyebook';
        // $this->username = 'root';
        $this->username = 'user';
        $this->password = '#*Eyebook@123*#';
        // $this->password = '';

    }

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->db_name", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }
        return $this->conn;
    }

    public static function getConnection() {
        $database = new self();
        return $database->connect();
    }
}
?>