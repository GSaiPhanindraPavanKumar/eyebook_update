<?php
include("sidebar.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $university_id = $_POST['university_id'];
    $confirm_replace = isset($_POST['confirm_replace']) ? $_POST['confirm_replace'] : 'no';

    // Fetch the course
    $sql = "SELECT * FROM courses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();

    if (!$course) {
        echo json_encode(['message' => 'Course not found']);
        exit;
    }

    // Ensure universities field is an array
    $course['universities'] = $course['universities'] ? json_decode($course['universities'], true) : [];

    // Check if the course is already assigned to another university
    if (!empty($course['universities']) && !in_array($university_id, $course['universities']) && $confirm_replace !== 'yes') {
        echo json_encode(['message' => 'Course is already assigned to another university. Do you want to replace the assignment?']);
        exit;
    }

    // Fetch the university
    $sql = "SELECT * FROM universities WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $university_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $university = $result->fetch_assoc();

    if (!$university) {
        echo json_encode(['message' => 'University not found']);
        exit;
    }

    // Assign the course to the new university
    $course['universities'] = [$university_id];

    // Update the course in the database
    $sql = "UPDATE courses SET universities = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $universities_json = json_encode($course['universities']);
    $stmt->bind_param("si", $universities_json, $course_id);
    $stmt->execute();

    echo json_encode(['message' => 'Course assigned to university successfully']);
}
?>