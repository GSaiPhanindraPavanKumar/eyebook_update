<?php

namespace Models;

use PDO;

class Company {
    // Get all companies
    public static function getAll($conn) {
        $sql = "SELECT * FROM company";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a company by ID
    public static function getById($conn, $id) {
        $sql = "SELECT * FROM company WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create a new company
    public static function create($conn, $name, $description) {
        $sql = "INSERT INTO company (name, description) VALUES (:name, :description)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':description', $description);
        return $stmt->execute();
    }

    // Update an existing company
    public static function update($conn, $id, $name, $description) {
        $sql = "UPDATE company SET name = :name, description = :description WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Update university_ids for a company
    public static function updateUniversityIds($conn, $id, $university_ids) {
        $sql = "UPDATE company SET university_ids = :university_ids WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':university_ids', $university_ids);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Delete a company
    public static function delete($conn, $id) {
        $sql = "DELETE FROM company WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}