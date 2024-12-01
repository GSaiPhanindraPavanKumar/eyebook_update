<?php
use Models\Database;
use Models\Notification;
use Models\Student;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

require_once 'vendor/autoload.php';
require_once __DIR__ . '/../../aws_config.php';

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    set_time_limit(600); // Set the maximum execution time to 600 seconds

    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $upload_type = isset($_POST['upload_type']) ? $_POST['upload_type'] : 'single';

    // Fetch the university ID from the course
    $sql = "SELECT university_id FROM courses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    $university_id = $course['university_id'];

    if ($upload_type == 'single') {
        $unit_number = isset($_POST['unit_number']) ? intval($_POST['unit_number']) : 0;
        $topic = isset($_POST['topic']) ? $_POST['topic'] : '';

        if ($course_id == 0 || $unit_number == 0 || empty($topic)) {
            echo json_encode(['message' => 'Invalid input']);
            exit;
        }

        if (isset($_FILES['course_materials_file']) && $_FILES['course_materials_file']['error'] == UPLOAD_ERR_OK) {
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

            // Upload course materials file to S3
            $filePath = $_FILES['course_materials_file']['tmp_name'];
            $fileName = basename($_FILES['course_materials_file']['name']);
            $timestamp = time();
            $key = "course_documents/{$course_id}/unit-{$unit_number}/{$timestamp}-{$fileName}";

            try {
                $result = $s3Client->putObject([
                    'Bucket' => $bucketName,
                    'Key' => $key,
                    'SourceFile' => $filePath,
                    'ContentType' => 'application/pdf',
                ]);

                // Construct the full URL of the uploaded file
                $course_materials_url = "https://{$bucketName}.s3.{$region}.amazonaws.com/{$key}";

                // Fetch existing course materials
                $sql = "SELECT course_materials FROM courses WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$course_id]);
                $course = $stmt->fetch(PDO::FETCH_ASSOC);
                $course_materials = json_decode($course['course_materials'] ?? '[]', true);

                if (!is_array($course_materials)) {
                    $course_materials = [];
                }

                // Add new material to the course materials
                $new_material = [
                    'unitNumber' => $unit_number,
                    'topic' => $topic,
                    'materials' => [
                        [
                            'title' => $fileName,
                            'indexPath' => $course_materials_url
                        ]
                    ]
                ];
                $course_materials[] = $new_material;

                // Update the course_materials column in the database
                $course_materials_json = json_encode($course_materials);
                $sql = "UPDATE courses SET course_materials = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$course_materials_json, $course_id])) {
                    // Create notifications for students
                    $students = Student::getByUniversityId($conn, $university_id);
                    foreach ($students as $student) {
                        Notification::create($conn, $student['id'], "New course materials have been uploaded for your course.");
                    }

                    echo json_encode(['message' => 'Course materials uploaded successfully', 'url' => $course_materials_url]);
                    $hashedId = base64_encode($course_id);
                    $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                    header("Location: /faculty/view_course/$hashedId");
                    exit;
                } else {
                    echo json_encode(['message' => 'Error updating record: ' . $stmt->errorInfo()[2]]);
                }
            } catch (AwsException $e) {
                echo json_encode(['message' => 'Error uploading file to S3: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['message' => 'No file uploaded or upload error']);
        }
    } elseif ($upload_type == 'bulk') {
        $unit_number = isset($_POST['bulk_unit_number']) ? intval($_POST['bulk_unit_number']) : 0;

        if ($course_id == 0 || $unit_number == 0) {
            echo json_encode(['message' => 'Invalid input']);
            exit;
        }

        if (isset($_FILES['bulk_course_materials_file']) && $_FILES['bulk_course_materials_file']['error'] == UPLOAD_ERR_OK) {
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

            // Upload bulk course materials file to S3
            $filePath = $_FILES['bulk_course_materials_file']['tmp_name'];
            $fileName = basename($_FILES['bulk_course_materials_file']['name']);
            $timestamp = time();
            $key = "course_documents/{$course_id}/unit-{$unit_number}/{$timestamp}-{$fileName}";

            try {
                $result = $s3Client->putObject([
                    'Bucket' => $bucketName,
                    'Key' => $key,
                    'SourceFile' => $filePath,
                    'ContentType' => 'application/zip',
                ]);

                // Construct the full URL of the uploaded file
                $bulk_materials_url = "https://{$bucketName}.s3.{$region}.amazonaws.com/{$key}";

                // Download the zip file to a temporary location
                $tempFile = tempnam(sys_get_temp_dir(), 'scorm');
                $s3Client->getObject([
                    'Bucket' => $bucketName,
                    'Key' => $key,
                    'SaveAs' => $tempFile,
                ]);

                // Unzip the file
                $zip = new ZipArchive;
                if ($zip->open($tempFile) === TRUE) {
                    $unzipKey = "course_documents/{$course_id}/unit-{$unit_number}/{$timestamp}-{$fileName}/";
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $filename = $zip->getNameIndex($i);
                        $fileinfo = pathinfo($filename);

                        // Extract the file to a temporary location
                        $extractTo = tempnam(sys_get_temp_dir(), 'scorm');
                        file_put_contents($extractTo, $zip->getFromIndex($i));

                        // Determine correct Content-Type
                        $mimeType = mime_content_type($extractTo);
                        if (!$mimeType) {
                            $mimeType = 'application/octet-stream'; // Fallback
                        }

                        // Upload the extracted file to S3
                        $s3Client->putObject([
                            'Bucket' => $bucketName,
                            'Key' => $unzipKey . $filename,
                            'SourceFile' => $extractTo,
                            'ContentType' => $mimeType,
                        ]);

                        // Delete the temporary file
                        unlink($extractTo);
                    }
                    $zip->close();

                    // Delete the temporary zip file
                    unlink($tempFile);

                    // Fetch existing course materials
                    $sql = "SELECT course_materials FROM courses WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$course_id]);
                    $course = $stmt->fetch(PDO::FETCH_ASSOC);
                    $course_materials = json_decode($course['course_materials'] ?? '[]', true);

                    if (!is_array($course_materials)) {
                        $course_materials = [];
                    }

                    // Add new materials to the course materials
                    $objects = $s3Client->listObjectsV2([
                        'Bucket' => $bucketName,
                        'Prefix' => $unzipKey,
                    ]);

                    foreach ($objects['Contents'] as $object) {
                        $file = basename($object['Key']);
                        $fileUrl = "https://{$bucketName}.s3.{$region}.amazonaws.com/{$object['Key']}";
                        $course_materials[] = [
                            'unitNumber' => $unit_number,
                            'topic' => pathinfo($file, PATHINFO_FILENAME),
                            'materials' => [
                                [
                                    'title' => $file,
                                    'indexPath' => $fileUrl
                                ]
                            ]
                        ];
                    }

                    // Update the course_materials column in the database
                    $course_materials_json = json_encode($course_materials);
                    $sql = "UPDATE courses SET course_materials = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    if ($stmt->execute([$course_materials_json, $course_id])) {
                        // Create notifications for students
                        $students = Student::getByUniversityId($conn, $university_id);
                        foreach ($students as $student) {
                            Notification::create($conn, $student['id'], "New bulk course materials have been uploaded for your course.");
                        }

                        echo json_encode(['message' => 'Course materials uploaded successfully']);
                        $hashedId = base64_encode($course_id);
                        $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                        header("Location: /faculty/view_course/$hashedId");
                        exit;
                    } else {
                        echo json_encode(['message' => 'Error updating record: ' . $stmt->errorInfo()[2]]);
                    }
                } else {
                    echo json_encode(['message' => 'Failed to unzip file']);
                }
            } catch (AwsException $e) {
                echo json_encode(['message' => 'Error uploading file to S3: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['message' => 'No file uploaded or upload error']);
        }
    }
}
?>