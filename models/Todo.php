<?php
namespace Models;

class Todo {
    public static function getAll($conn) {
        $stmt = $conn->prepare("SELECT * FROM todos ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function add($conn, $title) {
        $stmt = $conn->prepare("INSERT INTO todos (title) VALUES (:title)");
        $stmt->bindParam(':title', $title);
        $stmt->execute();
    }

    public static function update($conn, $id, $is_completed) {
        $stmt = $conn->prepare("UPDATE todos SET is_completed = :is_completed WHERE id = :id");
        $stmt->bindParam(':is_completed', $is_completed);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public static function delete($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM todos WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}
?>