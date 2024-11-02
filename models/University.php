<?php
namespace Models;

use PDO;

class University {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public static function getCount($conn) {
        $stmt = $conn->query("SELECT COUNT(*) FROM universities");
        return $stmt->fetchColumn();
    }

    public static function getAll($conn) {
        $stmt = $conn->query("SELECT * FROM universities");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function existsByShortName($conn, $short_name) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM universities WHERE short_name = :short_name");
        $stmt->execute([':short_name' => $short_name]);
        return $stmt->fetchColumn() > 0;
    }

    public static function addUniversity($conn, $long_name, $short_name, $location, $country, $spoc_name, $spoc_email, $spoc_phone, $spoc_password) {
        $stmt = $conn->prepare("INSERT INTO universities (long_name, short_name, location, country, spoc_name, spoc_email, spoc_phone, spoc_password) VALUES (:long_name, :short_name, :location, :country, :spoc_name, :spoc_email, :spoc_phone, :spoc_password)");
        $stmt->execute([
            ':long_name' => $long_name,
            ':short_name' => $short_name,
            ':location' => $location,
            ':country' => $country,
            ':spoc_name' => $spoc_name,
            ':spoc_email' => $spoc_email,
            ':spoc_phone' => $spoc_phone,
            ':spoc_password' => $spoc_password
        ]);
        return ['message' => 'University added successfully.', 'message_type' => 'success'];
    }

    public static function update($conn, $id, $long_name, $short_name, $location, $country) {
        $stmt = $conn->prepare("UPDATE universities SET long_name = :long_name, short_name = :short_name, location = :location, country = :country WHERE id = :id");
        $stmt->execute([
            ':long_name' => $long_name,
            ':short_name' => $short_name,
            ':location' => $location,
            ':country' => $country,
            ':id' => $id
        ]);
    }

    public static function delete($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM universities WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
?>