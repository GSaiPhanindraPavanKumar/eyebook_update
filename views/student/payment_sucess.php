<?php
// include 'config/connection.php';
use Models\Database;

$data = json_decode(file_get_contents('php://input'), true);
$payment_id = $data['payment_id'];
$course_id = $data['course_id'];
$student_id = $_SESSION['student_id']; // Assuming student_id is stored in session

$conn = Database::getConnection();

// Insert subscription record
$sql = "INSERT INTO subscriptions (student_id, course_id, payment_id) VALUES (:student_id, :course_id, :payment_id)";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':student_id' => $student_id,
    ':course_id' => $course_id,
    ':payment_id' => $payment_id
]);

echo json_encode(['success' => true]);
?>