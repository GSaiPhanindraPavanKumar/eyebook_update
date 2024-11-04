<?php
// Load configuration
require_once 'config.php';

// Establish database connection
$conn = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

// Get current time in UTC
$current_time = new DateTime('now', new DateTimeZone('UTC'));

// Update status to 'ongoing' for classes that have started but not yet ended
$updateOngoing = $conn->prepare("
    UPDATE virtual_classrooms
    SET status = 'ongoing'
    WHERE status = 'scheduled' AND start_time <= :current_time AND DATE_ADD(start_time, INTERVAL duration MINUTE) > :current_time
");
$updateOngoing->execute([':current_time' => $current_time->format('Y-m-d H:i:s')]);

// Update status to 'completed' for classes that have ended
$updateCompleted = $conn->prepare("
    UPDATE virtual_classrooms
    SET status = 'completed'
    WHERE status IN ('scheduled', 'ongoing') AND DATE_ADD(start_time, INTERVAL duration MINUTE) <= :current_time
");
$updateCompleted->execute([':current_time' => $current_time->format('Y-m-d H:i:s')]);

echo "Classroom statuses updated successfully.";
?>