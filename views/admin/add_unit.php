<?php
require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Models\Database;

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

    // Validate SCORM package
    $zip = new ZipArchive();
    if ($zip->open($scorm_file['tmp_name']) === TRUE) {
        $scormVersion = 'Unknown';
        if ($zip->locateName('imsmanifest.xml') !== false) {
            $manifest = $zip->getFromName('imsmanifest.xml');
            if (strpos($manifest, 'ADL SCORM') !== false) {
                if (strpos($manifest, '2004') !== false) {
                    $scormVersion = 'SCORM 2004';
                } elseif (strpos($manifest, '1.2') !== false) {
                    $scormVersion = 'SCORM 1.2';
                } else {
                    $scormVersion = 'Other SCORM Version';
                }
            } else {
                $scormVersion = 'Non-SCORM Package';
            }
        }
        $zip->close();

        if ($scormVersion === 'Unknown' || $scormVersion === 'Non-SCORM Package') {
            echo json_encode(['message' => 'Invalid SCORM package']);
            exit;
        }
    } else {
        echo json_encode(['message' => 'Failed to open SCORM package']);
        exit;
    }

    // Define the upload path
    $uploadPath = __DIR__ . '/../../uploads/courses/' . $course_id . '/' . $unit_name;
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    // Extract and save SCORM package locally
    if ($zip->open($scorm_file['tmp_name']) === TRUE) {
        $zip->extractTo($uploadPath);
        $zip->close();
    } else {
        echo json_encode(['message' => 'Failed to extract SCORM package']);
        exit;
    }

    // Get the base URL of the uploaded SCORM package
    $baseUrl = '/uploads/courses/' . $course_id . '/' . $unit_name . '/';

    // Update the SCORM details in the courses table
    $existingCourseBook = json_decode($course['course_book'], true);
    if (!is_array($existingCourseBook)) {
        $existingCourseBook = [];
    }
    $existingCourseBook[] = [
        'unit_name' => $unit_name,
        'scorm_url' => $baseUrl,
        'scorm_version' => $scormVersion
    ];
    $updatedCourseBook = json_encode($existingCourseBook);

    $sql = "UPDATE courses SET course_book = :course_book, scorm_version = :scorm_version WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'course_book' => $updatedCourseBook,
        'scorm_version' => $scormVersion,
        'id' => $course_id,
    ]);

    echo '<script>
            alert("Unit added successfully!");
            window.location.href = "/admin/view_course";
          </script>';
    exit;
    
}