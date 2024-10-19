<?php
require __DIR__ . '/../vendor/autoload.php';

class Database {
    private $conn;

    public function __construct() {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $servername = $_ENV['DB_SERVERNAME'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
        $dbname = $_ENV['DB_NAME'];

        $this->conn = new mysqli($servername, $username, $password);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
        if ($this->conn->query($sql) === TRUE) {
            // echo "Database created successfully or already exists<br>";
        } else {
            // echo "Error creating database: " . $this->conn->error . "<br>";
        }

        $this->conn->select_db($dbname);
    }


    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn->close();
    }
}