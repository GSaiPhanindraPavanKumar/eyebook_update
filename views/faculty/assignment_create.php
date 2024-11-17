<?php
include('sidebar.php');
use Models\Database;
$conn = Database::getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = $_POST["assignment_title"];
    $description = $_POST["assignment_description"];
    $due_date = $_POST["due_date"];
    $file = $_FILES["assignment_file"];

    // Insert assignment details into the database
    $sql = "INSERT INTO assignments (title, description, due_date, file_path) VALUES (:title, :description, :due_date, :file_path)";
    $stmt = $conn->prepare($sql);

    // Handle file upload and store the file path
    $target_dir = "uploads/assignments/";
    $target_file = $target_dir . basename($_FILES["assignment_file"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "<div class='alert alert-danger'>Sorry, file already exists.</div>";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["assignment_file"]["size"] > 500000) {
        echo "<div class='alert alert-danger'>Sorry, your file is too large.</div>";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($fileType != "pdf" && $fileType != "doc" && $fileType != "docx"
        && $fileType != "txt" && $fileType != "zip") {
        echo "<div class='alert alert-danger'>Sorry, only PDF, DOC, DOCX, TXT, and ZIP files are allowed.</div>";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "<div class='alert alert-danger'>Sorry, your file was not uploaded.</div>";
    } else {
        if (move_uploaded_file($_FILES["assignment_file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':due_date', $due_date);
            $stmt->bindParam(':file_path', $file_path);
            $stmt->execute();
            echo "<div class='alert alert-success'>The file ". htmlspecialchars(basename($_FILES["assignment_file"]["name"])). " has been uploaded.</div>";
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
                        <!-- <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($userData['name']); ?></em></h3> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Create Assignment</h5>

                        <form action="assignment_create.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="assignment_title">Assignment Title:</label>
                    <input type="text" class="form-control" id="assignment_title" name="assignment_title" required>
                </div>
                <div class="form-group">
                    <label for="assignment_description">Assignment Description:</label>
                    <textarea class="form-control" id="assignment_description" name="assignment_description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="due_date">Due Date:</label>
                    <input type="date" class="form-control" id="due_date" name="due_date" required>
                </div>
                <div class="form-group">
                    <label for="assignment_file">Upload Assignment File:</label>
                    <input type="file" class="form-control-file" id="assignment_file" name="assignment_file">
                </div>
                <button type="submit" class="btn btn-primary">Create Assignment</button>
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

