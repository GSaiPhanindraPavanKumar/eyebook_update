<?php
require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Models\Database;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$conn = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $unit_name = $_POST['unit_name'];
    $scorm_file = $_FILES['scorm_file'];

    if (!$unit_name || !$scorm_file) {
        echo json_encode(['message' => 'Unit name and SCORM package file are required']);
        exit;
    }

    // Fetch the course
    $sql = "SELECT * FROM courses WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        echo json_encode(['message' => 'Course not found']);
        exit;
    }

    // AWS S3 configuration
    $bucketName = 'mobileappliaction';
    $region = 'us-east-1';
    $accessKey = 'AKIAUNJHJGMDLG4ZWEWS';
    $secretKey = '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';

    // Initialize S3 client
    $s3Client = new S3Client([
        'region' => $region,
        'version' => 'latest',
        'credentials' => [
            'key' => $accessKey,
            'secret' => $secretKey,
        ],
    ]);

    // Upload SCORM file to S3
    $filePath = $scorm_file['tmp_name'];
    $fileName = basename($scorm_file['name']);
    $key = "scorm_packages/{$course_id}/{$fileName}";

    try {
        $result = $s3Client->putObject([
            'Bucket' => $bucketName,
            'Key' => $key,
            'SourceFile' => $filePath,
            'ACL' => 'public-read', // Optional: Set the ACL to public-read if you want the file to be publicly accessible
        ]);

        // Get the URL of the uploaded file
        $fileUrl = $result['ObjectURL'];

        // Save the unit details to the database
        $sql = "INSERT INTO units (course_id, name, scorm_url) VALUES (:course_id, :name, :scorm_url)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'course_id' => $course_id,
            'name' => $unit_name,
            'scorm_url' => $fileUrl,
        ]);

        echo json_encode(['message' => 'Unit added successfully', 'scorm_url' => $fileUrl]);
    } catch (AwsException $e) {
        echo json_encode(['message' => 'Error uploading file to S3: ' . $e->getMessage()]);
    }
}