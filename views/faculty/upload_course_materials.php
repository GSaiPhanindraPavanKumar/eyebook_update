<?php
include "../../config/connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $upload_type = isset($_POST['upload_type']) ? $_POST['upload_type'] : 'single';

    if ($upload_type == 'single') {
        $unit_number = isset($_POST['unit_number']) ? intval($_POST['unit_number']) : 0;
        $topic = isset($_POST['topic']) ? $_POST['topic'] : '';

        if ($course_id == 0 || $unit_number == 0 || empty($topic)) {
            echo json_encode(['message' => 'Invalid input']);
            exit;
        }

        if (isset($_FILES['course_materials_file']) && $_FILES['course_materials_file']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = "../../uploads/course-$course_id/unit-$unit_number/"; // Dynamic directory based on course ID and unit number
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create the uploads directory if it doesn't exist
            }
            $file_name = basename($_FILES['course_materials_file']['name']);
            $target_file = $upload_dir . time() . '-' . $file_name;

            if (move_uploaded_file($_FILES['course_materials_file']['tmp_name'], $target_file)) {
                $course_materials_url = "uploads/course-$course_id/unit-$unit_number/" . time() . '-' . $file_name;

                // Fetch existing course materials
                $sql = "SELECT course_materials FROM courses WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $course_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $course = $result->fetch_assoc();
                $course_materials = json_decode($course['course_materials'], true);

                if (!is_array($course_materials)) {
                    $course_materials = [];
                }

                // Add new material to the course materials
                $new_material = [
                    'unitNumber' => $unit_number,
                    'topic' => $topic,
                    'materials' => [
                        [
                            'title' => $file_name,
                            'indexPath' => $course_materials_url
                        ]
                    ]
                ];
                $course_materials[] = $new_material;

                // Update the course_materials column in the database
                $course_materials_json = json_encode($course_materials);
                $sql = "UPDATE courses SET course_materials = ? WHERE id = ?";
                $stmt->prepare($sql);
                $stmt->bind_param("si", $course_materials_json, $course_id);
                if ($stmt->execute()) {
                    echo json_encode(['message' => 'Course materials uploaded successfully', 'url' => $course_materials_url]);
                    header("Location: view_course.php?id=$course_id");
                    exit;
                } else {
                    echo json_encode(['message' => 'Error updating record: ' . $stmt->error]);
                }
            } else {
                echo json_encode(['message' => 'Error uploading file']);
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
            $zip_name = time(). '-' .pathinfo($_FILES['bulk_course_materials_file']['name'], PATHINFO_FILENAME);
            $upload_dir = "../../uploads/course-$course_id/unit-$unit_number/$zip_name/"; // Dynamic directory based on course ID, unit number, and zip file name
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create the uploads directory if it doesn't exist
            }
            $file_name = basename($_FILES['bulk_course_materials_file']['name']);
            $target_file = $upload_dir . time() . '-' . $file_name;

            if (move_uploaded_file($_FILES['bulk_course_materials_file']['tmp_name'], $target_file)) {
                $zip = new ZipArchive;
                if ($zip->open($target_file) === TRUE) {
                    $zip->extractTo($upload_dir);
                    $zip->close();

                    // Remove the uploaded zip file
                    unlink($target_file);

                    // Fetch existing course materials
                    $sql = "SELECT course_materials FROM courses WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $course_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $course = $result->fetch_assoc();
                    $course_materials = json_decode($course['course_materials'], true);

                    if (!is_array($course_materials)) {
                        $course_materials = [];
                    }

                    // Add new materials to the course materials
                    $dir = opendir($upload_dir);
                    while (($file = readdir($dir)) !== false) {
                        if ($file != '.' && $file != '..' && is_file($upload_dir . $file)) {
                            $course_materials[] = [
                                'unitNumber' => $unit_number,
                                'topic' => pathinfo($file, PATHINFO_FILENAME),
                                'materials' => [
                                    [
                                        'title' => $file,
                                        'indexPath' => "uploads/course-$course_id/unit-$unit_number/$zip_name/" . $file
                                    ]
                                ]
                            ];
                        }
                    }
                    closedir($dir);

                    // Update the course_materials column in the database
                    $course_materials_json = json_encode($course_materials);
                    $sql = "UPDATE courses SET course_materials = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $course_materials_json, $course_id);
                    if ($stmt->execute()) {
                        echo json_encode(['message' => 'Course materials uploaded successfully']);
                        header("Location: view_course.php?id=$course_id");
                        exit;
                    } else {
                        echo json_encode(['message' => 'Error updating record: ' . $stmt->error]);
                    }
                } else {
                    echo json_encode(['message' => 'Failed to unzip file']);
                }
            } else {
                echo json_encode(['message' => 'Error uploading file']);
            }
        } else {
            echo json_encode(['message' => 'No file uploaded or upload error']);
        }
    }
}
?>