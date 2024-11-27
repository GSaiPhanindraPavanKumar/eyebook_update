<?php
use Models\Database;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$conn = Database::getConnection();

$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

if ($course_id == 0) {
    die("Invalid course ID.");
}

if (isset($_FILES['course_plan_file']) && $_FILES['course_plan_file']['error'] == UPLOAD_ERR_OK) {
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
    echo "No file uploaded or upload error.";
}

$conn = null;
?>