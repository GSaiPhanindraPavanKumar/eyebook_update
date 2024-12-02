<?php
use Models\Database;
use Models\Notification;
use Models\Student;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

require_once 'vendor/autoload.php';
require_once __DIR__ . '/../../aws_config.php';

// Increase file upload size limit
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('memory_limit', '256M'); // Increase memory limit if needed

$bucketName = AWS_BUCKET_NAME;
$region = AWS_REGION;
$accessKey = AWS_ACCESS_KEY_ID;
$secretKey = AWS_SECRET_ACCESS_KEY;

// Debugging: Log the values of the configuration variables
error_log('AWS_BUCKET_NAME: ' . $bucketName);
error_log('AWS_REGION: ' . $region);
error_log('AWS_ACCESS_KEY_ID: ' . $accessKey);
error_log('AWS_SECRET_ACCESS_KEY: ' . $secretKey);

if (!$bucketName || !$region || !$accessKey || !$secretKey) {
    throw new Exception('Missing AWS configuration in aws_config.php file');
}

$s3Client = new S3Client([
    'region' => 'us-east-1',
    'version' => 'latest',
    'credentials' => [
        'key' => $accessKey,
        'secret' => $secretKey,
    ],
]);

$conn = Database::getConnection();

$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

if ($course_id == 0) {
    die("Invalid course ID.");
}

// Fetch the university ID from the course
$sql = "SELECT university_id FROM courses WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
$university_id = $course['university_id'];

if (isset($_FILES['course_plan_file'])) {
    if ($_FILES['course_plan_file']['error'] == UPLOAD_ERR_OK) {
        // AWS S3 configuration
        $bucketName = AWS_BUCKET_NAME;
        $region = AWS_REGION;
        $accessKey = AWS_ACCESS_KEY_ID;
        $secretKey = AWS_SECRET_ACCESS_KEY;

        // Initialize S3 client
        $s3Client = new S3Client([
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key' => $accessKey,
                'secret' => $secretKey,
            ],
        ]);

        // Upload course plan file to S3
        $filePath = $_FILES['course_plan_file']['tmp_name'];
        $fileName = basename($_FILES['course_plan_file']['name']);
        $timestamp = time();
        $key = "course_documents/{$course_id}/course_plan/{$timestamp}-{$fileName}";

        try {
            $result = $s3Client->putObject([
                'Bucket' => $bucketName,
                'Key' => $key,
                'SourceFile' => $filePath,
                'ContentType' => 'application/pdf',
            ]);

            // Get the URL of the uploaded file
            $course_plan_url = $result['ObjectURL'];

            // Update the course_plan column in the database
            $sql = "UPDATE courses SET course_plan = JSON_OBJECT('url', ?) WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$course_plan_url, $course_id])) {
                // Create notifications for students
                $students = Student::getByUniversityId($conn, $university_id);
                foreach ($students as $student) {
                    Notification::create($conn, $student['id'], "A new course plan has been uploaded for your course.");
                }

                $hashedId = base64_encode($course_id);
                $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                header("Location: /faculty/view_course/$hashedId");
                exit;
            } else {
                echo "Error updating record: " . $stmt->errorInfo()[2];
            }
        } catch (AwsException $e) {
            echo "Error uploading file to S3: " . $e->getMessage();
        }
    } else {
        echo "File upload error: " . $_FILES['course_plan_file']['error'];
    }
} else {
    echo "No file uploaded.";
}

$conn = null;
?>