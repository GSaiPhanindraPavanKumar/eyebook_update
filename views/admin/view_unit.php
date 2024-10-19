<?php
include("sidebar.php");

$course_id = $_GET['course_id'];
$unit_id = $_GET['unit_id'];

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

// Fetch the unit
$unit = isset($course['content'][$unit_id]) ? $course['content'][$unit_id] : null;
if (!$unit) {
    echo json_encode(['message' => 'Unit not found']);
    exit;
}

// Fetch the material
$material = isset($unit['materials'][0]) ? $unit['materials'][0] : null;
if (!$material) {
    echo json_encode(['message' => 'Material not found']);
    exit;
}

// Verify that the file exists
$file_path = $material['indexPath'];
if (!file_exists($file_path)) {
    echo json_encode(['message' => 'File not found']);
    exit;
}

// Serve the file
header('Content-Type: text/html');
readfile($file_path);
?>