<?php
include "../../config/connection.php"; // Adjust the path to your db_connection.php file

$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

if ($course_id == 0) {
    die("Invalid course ID.");
}

if (isset($_FILES['course_plan_file']) && $_FILES['course_plan_file']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = "../../uploads/course-$course_id/courseplan/"; // Adjust the path to your uploads directory
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create the uploads directory if it doesn't exist
    }
    $file_name = basename($_FILES['course_plan_file']['name']);
    $target_file = $upload_dir . time() . '-' . $file_name;

    if (move_uploaded_file($_FILES['course_plan_file']['tmp_name'], $target_file)) {
        $course_plan_url = "uploads/course-$course_id/courseplan/" . time() . '-' . $file_name;

        // Update the course_plan column in the database
        $sql = "UPDATE courses SET course_plan = JSON_OBJECT('url', '$course_plan_url') WHERE id = $course_id";
        if ($conn->query($sql) === TRUE) {
            header("Location: view_course.php?id=$course_id");
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        echo "Error uploading file.";
    }
} else {
    echo "No file uploaded or upload error.";
}

$conn->close();
?>