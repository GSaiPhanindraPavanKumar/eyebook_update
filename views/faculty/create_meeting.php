<?php
include __DIR__ . '/../../config/connection.php';
require __DIR__ . '/../../config.php'; // Ensure this file loads the environment variables
require __DIR__ . '/../../vendor/autoload.php'; // For PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$meetingTitle = $_POST['meetingTitle'];
$section = $_POST['section'];
$facultyId = $_POST['facultyId'];
$facultyName = $_POST['facultyName'];

if ($course_id == 0) {
    die("Invalid course ID.");
}

// Fetch students based on the section
$sql = "SELECT email FROM students WHERE section = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $section);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("No students found for section: $section");
}

$students = $result->fetch_all(MYSQLI_ASSOC);

// Create the meeting using Daily.co API
$api_key = $_ENV['DAILY_API_KEY'];
$url = 'https://api.daily.co/v1/rooms';
$data = array(
    'properties' => array(
        'enable_chat' => true,
        'enable_knocking' => true,
    )
);

$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\nAuthorization: Bearer $api_key\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ),
);

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    die('Error creating meeting');
}

$response = json_decode($result, true);
$meetingUrl = $response['url'];

// Send email notifications to all students in the section
$mail = new PHPMailer(true);
try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['EMAIL_USER'];
    $mail->Password   = $_ENV['EMAIL_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom($_ENV['EMAIL_USER'], 'Faculty');
    foreach ($students as $student) {
        $mail->addAddress($student['email']);
    }

    // Content
    $mail->isHTML(true);
    $mail->Subject = "Meeting Invitation: $meetingTitle";
    $mail->Body    = "Dear Student,<br><br>You are invited to a meeting.<br>Join here: <a href='$meetingUrl'>$meetingUrl</a><br><br>Best regards,<br>Faculty";

    $mail->send();
    echo 'Emails have been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

// Store the meeting details in the database
$stmt = $conn->prepare("INSERT INTO virtual_meetings (title, url, section, facultyId, facultyName) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $meetingTitle, $meetingUrl, $section, $facultyId, $facultyName);

if ($stmt->execute() === TRUE) {
    echo "New meeting created successfully";
} else {
    echo "Error: " . $stmt->error;
}

$conn->close();
?>