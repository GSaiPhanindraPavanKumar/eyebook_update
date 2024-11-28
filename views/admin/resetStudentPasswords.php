<?php
use Models\Database;
use Models\Student;

$conn = Database::getConnection();

if (isset($_POST['bulk_reset_password'])) {
    $selectedStudents = $_POST['selected'] ?? [];

    foreach ($selectedStudents as $studentId) {
        $student = Student::getById($conn, $studentId);
        if ($student) {
            $newPassword = $student['email']; // Reset password to email
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            $sql = "UPDATE students SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$hashedPassword, $studentId]);
        }
    }

    header('Location: manageStudents.php');
    exit();
}
?>