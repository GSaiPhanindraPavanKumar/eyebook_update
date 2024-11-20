<?php
use Models\Database;

$conn = Database::getConnection();

$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

if ($course_id == 0) {
    die("Invalid course ID.");
}

if (isset($_FILES['course_plan_file']) && $_FILES['course_plan_file']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = "uploads/course-$course_id/courseplan/"; // Adjust the path to your uploads directory
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create the uploads directory if it doesn't exist
    }
    $file_name = basename($_FILES['course_plan_file']['name']);
    $target_file = $upload_dir . time() . '-' . $file_name;

    if (move_uploaded_file($_FILES['course_plan_file']['tmp_name'], $target_file)) {
        $course_plan_url = "uploads/course-$course_id/courseplan/" . time() . '-' . $file_name;

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
    } else {
        echo "Error uploading file.";
    }
} else {
    echo "No file uploaded or upload error.";
}

$conn = null;
?>