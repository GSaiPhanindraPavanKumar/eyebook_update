<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../db_connect.php';

function getAvailableMeetings($section) {
    try {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id, title, faculty_name, room_url FROM meetings WHERE section = ?");
        $stmt->bind_param("s", $section);
        $stmt->execute();
        $result = $stmt->get_result();
        $meetings = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        return ['success' => true, 'meetings' => $meetings];
    } catch (Exception $e) {
        error_log("Error fetching available meetings: " . $e->getMessage());
        return ['success' => false, 'error' => "Failed to fetch meetings: " . $e->getMessage()];
    }
}

$section = $_GET['section'] ?? '';
$result = getAvailableMeetings($section);
$meetings = $result['success'] ? $result['meetings'] : [];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Meeting LMS</title>
</head>
<body>
    <h1>Student Portal</h1>

    <h2>Find Available Meetings</h2>
    <form method="get">
        <input type="text" name="section" placeholder="Enter your section" value="<?php echo htmlspecialchars($section); ?>" required>
        <button type="submit">Find Meetings</button>
    </form>

    <?php if (!empty($meetings)): ?>
        <h2>Available Meetings</h2>
        <ul>
        <?php foreach ($meetings as $meeting): ?>
            <li>
                <?php echo htmlspecialchars($meeting['title']); ?> 
                by <?php echo htmlspecialchars($meeting['faculty_name']); ?> - 
                <a href="<?php echo htmlspecialchars($meeting['room_url']); ?>" target="_blank">Join Meeting</a>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php elseif ($section): ?>
        <p>No meetings available for this section.</p>
    <?php endif; ?>

    <p><a href="index.php">Back to Home</a></p>
</body>
</html>

