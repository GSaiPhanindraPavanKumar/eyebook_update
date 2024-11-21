<?php
// Load configuration
require_once __DIR__ . '/../../models/config.php';
require_once __DIR__ . '/../../models/Database.php';

// Include Zoom integration
require_once __DIR__ . '/../../models/zoom_integration.php';
use Models\Database;

$conn = Database::getConnection();

// Initialize ZoomAPI
$zoom = new ZoomAPI(ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET, ZOOM_ACCOUNT_ID, $conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $topic = $_POST['topic'];
    $start_time_local = $_POST['start_time'];
    $duration = $_POST['duration'];

    // Convert local time to UTC and then to ISO 8601 format
    $start_time = new DateTime($start_time_local, new DateTimeZone('Asia/Kolkata')); // Set the local time zone
    $start_time_utc = clone $start_time;
    $start_time_utc->setTimezone(new DateTimeZone('UTC')); // Convert to UTC
    $start_time_iso8601 = $start_time_utc->format(DateTime::ATOM);

    $classroom = $zoom->createVirtualClassroom($topic, $start_time_iso8601, $duration);

    if (isset($classroom['id'])) {
        // Save the start time with the classroom
        $stmt = $conn->prepare("UPDATE virtual_classrooms SET start_time = ? WHERE classroom_id = ?");
        $stmt->execute([$start_time->format('Y-m-d H:i:s'), $classroom['id']]);

        // Redirect to the faculty dashboard
        header('Location: virtual_classroom');
        exit();
    } else {
        echo "Error creating virtual classroom.";
    }
}
?>