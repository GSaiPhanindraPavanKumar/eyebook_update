<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Define the DAILY_API_KEY constant
define('DAILY_API_KEY', 'your_actual_api_key_here');
require_once __DIR__ . '/../db_connect.php';

use Ramakrishnareddy\MeetingLms\MeetingManager;

$meetingManager = new MeetingManager(DAILY_API_KEY);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $section = $_POST['section'] ?? '';
    $facultyId = $_POST['faculty_id'] ?? '';
    $facultyName = $_POST['faculty_name'] ?? '';

    if ($title && $section && $facultyId && $facultyName) {
        $result = $meetingManager->createMeeting($title, $section, $facultyId, $facultyName);
        $message = $result['success'] ? "Meeting created successfully!" : "Error: " . $result['error'];
    } else {
        $message = "All fields are required.";
    }
}

// Fetch faculty meetings
$facultyId = $_GET['faculty_id'] ?? '';
$facultyMeetings = [];
if ($facultyId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, title, section, room_url FROM meetings WHERE faculty_id = ?");
    $stmt->bind_param("s", $facultyId);
    $stmt->execute();
    $result = $stmt->get_result();
    $facultyMeetings = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Portal - Meeting LMS</title>
</head>
<body>
    <h1>Faculty Portal</h1>
    
    <?php if (isset($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <h2>Create a New Meeting</h2>
    <form method="post">
        <input type="text" name="title" placeholder="Meeting Title" required>
        <input type="text" name="section" placeholder="Section" required>
        <input type="text" name="faculty_id" placeholder="Faculty ID" required>
        <input type="text" name="faculty_name" placeholder="Faculty Name" required>
        <button type="submit">Create Meeting</button>
    </form>

    <h2>Your Meetings</h2>
    <form method="get">
        <input type="text" name="faculty_id" placeholder="Enter your Faculty ID" value="<?php echo htmlspecialchars($facultyId); ?>" required>
        <button type="submit">View My Meetings</button>
    </form>

    <?php if (!empty($facultyMeetings)): ?>
        <ul>
        <?php foreach ($facultyMeetings as $meeting): ?>
            <li>
                <?php echo htmlspecialchars($meeting['title']); ?> 
                (Section: <?php echo htmlspecialchars($meeting['section']); ?>) - 
                <a href="<?php echo htmlspecialchars($meeting['room_url']); ?>" target="_blank">Join Meeting</a>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php elseif ($facultyId): ?>
        <p>No meetings found for this faculty ID.</p>
    <?php endif; ?>

    <p><a href="index.php">Back to Home</a></p>
</body>
</html>
