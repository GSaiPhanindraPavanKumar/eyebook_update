<?php
namespace Models;

use PDO;
use PDOException;
use Exception;

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

    
    
    public static function addUniversity($conn, $long_name, $short_name, $location, $country, $company_id, $spoc_name, $spoc_email, $spoc_phone, $spoc_password, $logo_url = null) {
        try {
            // Begin transaction
            $conn->beginTransaction();

            // Check if the university short name already exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM universities WHERE short_name = :short_name");
            $stmt->execute([':short_name' => $short_name]);
            if ($stmt->fetchColumn() > 0) {
                return ['message' => 'University short name already exists.', 'message_type' => 'error'];
            }

            // Insert into universities table
            $stmt = $conn->prepare("INSERT INTO universities (long_name, short_name, location, country, company_id, logo_url) VALUES (:long_name, :short_name, :location, :country, :company_id, :logo_url)");
            $stmt->execute([
                ':long_name' => $long_name,
                ':short_name' => $short_name,
                ':location' => $location,
                ':country' => $country,
                ':company_id' => $company_id,
                ':logo_url' => $logo_url
            ]);

            // Get the last inserted university ID
            $university_id = $conn->lastInsertId();

            // Insert into spocs table if email and password are provided
            if (!empty($spoc_email) && !empty($spoc_password)) {
                // Check if the SPOC email already exists
                $stmt = $conn->prepare("SELECT COUNT(*) FROM spocs WHERE email = :email");
                $stmt->execute([':email' => $spoc_email]);
                if ($stmt->fetchColumn() > 0) {
                    return ['message' => 'SPOC email already exists.', 'message_type' => 'error'];
                }

                $stmt = $conn->prepare("INSERT INTO spocs (name, email, phone, password, university_id) VALUES (:name, :email, :phone, :password, :university_id)");
                $stmt->execute([
                    ':name' => $spoc_name,
                    ':email' => $spoc_email,
                    ':phone' => $spoc_phone,
                    ':password' => $spoc_password,
                    ':university_id' => $university_id
                ]);
            }

            // Commit transaction
            $conn->commit();

            return ['message' => 'University and SPOC added successfully.', 'message_type' => 'success', 'university_id' => $university_id];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            return ['message' => 'Failed to add university: ' . $e->getMessage(), 'message_type' => 'error'];
        }
    }

    public static function delete($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM universities WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function getById($conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM universities WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function getByUniversityId($conn, $university_id) {
        $stmt = $conn->prepare("SELECT * FROM spocs WHERE university_id = :university_id");
        $stmt->execute([':university_id' => $university_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function getCountByUniversityId($conn, $university_id) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE university_id = :university_id");
        $stmt->execute([':university_id' => $university_id]);
        return $stmt->fetchColumn();
    }

    public static function getCountfacultyByUniversityId($conn, $university_id) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM faculty WHERE university_id = :university_id");
        $stmt->execute([':university_id' => $university_id]);
        return $stmt->fetchColumn();
    }

    public static function updateCompanyId($conn, $university_id, $company_id) {
        $sql = "UPDATE universities SET company_id = :company_id WHERE id = :university_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);
        $stmt->bindValue(':university_id', $university_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    // public static function getCountByUniversityId($conn, $university_id) {
    //     $stmt = $conn->prepare("SELECT COUNT(*) FROM courses WHERE university_id = :university_id");
    //     $stmt->execute([':university_id' => $university_id]);
    //     return $stmt->fetchColumn();
    // }
}
?>