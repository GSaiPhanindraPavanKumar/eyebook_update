<?php
// Load environment variables
require 'vendor/autoload.php';

// Daily API base URL and API key
const DAILY_API_BASE_URL = 'https://api.daily.co/v1/rooms';
const DAILY_API_KEY = '6f2a4a94436d1834971a1a0e0c12e862f92c08d147ba1f48b82e8e84492881d7';

include 'sidebar.php';

// Function to generate a meeting link
function createMeetingLink($topic, $date) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => DAILY_API_BASE_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            "name" => $topic,
            "properties" => [
                "enable_chat" => true,
                "start_audio_off" => true,
                "start_video_off" => true,
                "exp" => strtotime($date)
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . DAILY_API_KEY
        ]
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
        curl_close($curl);
        return "cURL error: $error_msg";
    }

    curl_close($curl);

    $data = json_decode($response, true);

    if (isset($data['url'])) {
        return $data['url']; // Return the meeting URL
    } else {
        return false;
    }
}

// Function to record attendance
function recordAttendance($conn, $studentId, $meetingUrl) {
    $stmt = $conn->prepare("INSERT INTO attendance (student_id, meeting_url) VALUES (?, ?)");
    $stmt->bind_param("is", $studentId, $meetingUrl);

    if ($stmt->execute()) {
        $stmt->close();
        return "Attendance recorded successfully.";
    } else {
        $stmt->close();
        return "Error recording attendance: {$conn->error}";
    }
}

// Function to retrieve attendance
function getAttendance($conn, $studentId) {
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE student_id = ?");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    $attendanceData = [];
    while ($row = $result->fetch_assoc()) {
        $attendanceData[] = $row;
    }

    return $attendanceData;
}

// Example usage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['topic'], $_POST['date'])) {
        $topic = htmlspecialchars($_POST['topic']);
        $date = htmlspecialchars($_POST['date']);
        $attendance = isset($_POST['attendance']) ? 1 : 0; // Checkbox handling

        // Step 1: Generate the meeting link
        $meetingUrl = createMeetingLink($topic, $date);

        if ($meetingUrl) {
            // Step 2: Record attendance if checkbox is checked
            if ($attendance && isset($_POST['student_id'])) {
                $studentId = intval($_POST['student_id']);
                echo recordAttendance($conn, $studentId, $meetingUrl);
            }
            echo "<br>Meeting URL: <a href='$meetingUrl'>$meetingUrl</a>";
        } else {
            echo "Error creating meeting link.";
        }
    } else {
        echo "Missing required POST parameters.";
    }
}

// Example to get attendance for a student
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['student_id'])) {
    $studentId = intval($_GET['student_id']);
    $attendance = getAttendance($conn, $studentId);

    echo "<h2>Attendance Records for Student ID: $studentId</h2>";
    foreach ($attendance as $record) {
        echo "Meeting URL: <a href='" . $record['meeting_url'] . "'>" . $record['meeting_url'] . "</a><br>";
        echo "Timestamp: " . $record['timestamp'] . "<br><br>";
    }
}
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="font-weight-bold mb-4">Virtual Meetings</h2>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Create New Meeting</h4>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="topic" class="form-label">Meeting Topic</label>
                                <input type="text" class="form-control" id="topic" name="topic" required>
                            </div>
                            <div class="mb-3">
                                <label for="date" class="form-label">Meeting Date</label>
                                <input type="datetime-local" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="mb-3">
                                <label for="attendance" class="form-label">Track Attendance</label>
                                <input type="checkbox" class="form-check-input" id="attendance" name="attendance">
                            </div>
                            <button type="submit" class="btn btn-primary">Create Meeting</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>