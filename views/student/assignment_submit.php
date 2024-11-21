<?php
include 'sidebar.php';
use Models\Database;
use Models\Student; // Add this line to include the Student class

$conn = Database::getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignment_id = $_POST["assignment_id"];
    $file = $_FILES["assignment_file"];
    $student_id = $_SESSION['student_id'];

    $target_dir = "uploads/assignments/submissions/";
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check file type
    if ($fileType != "pdf" && $fileType != "doc" && $fileType != "docx" && $fileType != "jpg") {
        echo "<div class='alert alert-danger'>Sorry, only PDF, DOC, DOCX, and JPG files are allowed.</div>";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "<div class='alert alert-danger'>Sorry, file already exists.</div>";
        $uploadOk = 0;
    }

    // Check file size (limit to 5MB)
    if ($file["size"] > 5000000) {
        echo "<div class='alert alert-danger'>Sorry, your file is too large.</div>";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "<div class='alert alert-danger'>Sorry, your file was not uploaded.</div>";
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $file_path = $target_file;
            if (Student::submitAssignment($conn, $student_id, $assignment_id, $file_path)) {
                echo "<div class='alert alert-success'>The file ". htmlspecialchars(basename($file["name"])). " has been uploaded.</div>";
            } else {
                echo "<div class='alert alert-danger'>Sorry, there was an error saving your submission.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
        }
    }
}
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Submit Assignment</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Submit Assignment</h5>
                        <form action="assignment_submit.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="assignment_id">Assignment ID:</label>
                                <input type="text" class="form-control" id="assignment_id" name="assignment_id" required>
                            </div>
                            <div class="form-group">
                                <label for="assignment_file">Upload Assignment File:</label>
                                <input type="file" class="form-control-file" id="assignment_file" name="assignment_file" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Assignment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">