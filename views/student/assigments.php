<?php include('sidebar.php');
use Models\Database;
$conn = Database::getConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Assignment</title>
</head>
<body>
    <h1>Submit Assignment</h1>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $assignment = $_POST['assignment'];
        $file = $_FILES['file'];

        // Check if file was uploaded without errors
        if ($file['error'] == 0) {
            $upload_dir = 'uploads/';
            $upload_file = $upload_dir . basename($file['name']);

            // Move the uploaded file to the desired directory
            if (move_uploaded_file($file['tmp_name'], $upload_file)) {
                try {
                    // Insert assignment details into the database
                    $stmt = $conn->prepare("INSERT INTO assignments (assignment_name, file_path) VALUES (:assignment_name, :file_path)");
                    $stmt->bindParam(':assignment_name', $assignment);
                    $stmt->bindParam(':file_path', $upload_file);
                    $stmt->execute();

                    echo "<p>Assignment '$assignment' submitted successfully!</p>";
                    echo "<p>File uploaded to: $upload_file</p>";
                } catch (PDOException $e) {
                    echo "<p>Error saving assignment to database: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p>Error uploading file.</p>";
            }
        } else {
            echo "<p>Error: " . $file['error'] . "</p>";
        }
    }
    ?>

    <form action="assigments.php" method="post" enctype="multipart/form-data">
        <label for="assignment">Choose an assignment:</label>
        <select name="assignment" id="assignment">
            <option value="assignment1">Assignment 1</option>
            <option value="assignment2">Assignment 2</option>
            <option value="assignment3">Assignment 3</option>
        </select>
        <br><br>
        <label for="file">Upload your file:</label>
        <input type="file" name="file" id="file">
        <br><br>
        <input type="submit" value="Submit">
    </form>
</body>
</html>