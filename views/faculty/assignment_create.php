<?php
include('sidebar.php');
use Models\Database;
$conn = Database::getConnection();

// Add file_path column to assignments table if it doesn't exist
$sql = "ALTER TABLE assignments ADD COLUMN IF NOT EXISTS file_path VARCHAR(255)";
$conn->exec($sql);

// Fetch all courses
$sql = "SELECT id, name FROM courses";
$stmt = $conn->prepare($sql);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$messages = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = $_POST["assignment_title"];
    $instructions = $_POST["assignment_instructions"];
    $due_date = $_POST["due_date"];
    $course_id = $_POST["course_id"];
    $file = $_FILES["assignment_file"];

    // Insert assignment details into the database
    $sql = "INSERT INTO assignments (title, instructions, deadline, course_id, file_path) VALUES (:title, :instructions, :deadline, :course_id, :file_path)";
    $stmt = $conn->prepare($sql);

    // Handle file upload and store the file path
    $target_dir = "uploads/assignments/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Generate a unique file name with the current date and time
    $date = new DateTime();
    $timestamp = $date->format('YmdHis');
    $original_file_name = pathinfo($file["name"], PATHINFO_FILENAME);
    $file_extension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $new_file_name = $original_file_name . '_' . $timestamp . '.' . $file_extension;
    $target_file = $target_dir . $new_file_name;

    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        $messages .= "<div class='alert alert-danger'>Sorry, file already exists.</div>";
        $uploadOk = 0;
    }

    // Check file size
    if ($file["size"] > 500000) {
        $messages .= "<div class='alert alert-danger'>Sorry, your file is too large.</div>";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($fileType != "pdf" && $fileType != "doc" && $fileType != "docx" && $fileType != "txt" && $fileType != "zip" && $fileType != "jpg") {
        $messages .= "<div class='alert alert-danger'>Sorry, only PDF, DOC, DOCX, TXT, ZIP, and JPG files are allowed.</div>";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $messages .= "<div class='alert alert-danger'>Sorry, your file was not uploaded.</div>";
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $file_path = $target_file;
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':instructions', $instructions);
            $stmt->bindParam(':deadline', $due_date);
            $stmt->bindParam(':course_id', $course_id);
            $stmt->bindParam(':file_path', $file_path);
            $stmt->execute();
            $messages .= "<div class='alert alert-success'>The file ". htmlspecialchars(basename($file["name"])). " has been uploaded and assignment created.</div>";
        } else {
            $messages .= "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
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
                        <?php echo $messages; ?>
                        <form action="assignment_create.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="assignment_title">Assignment Title:</label>
                                <input type="text" class="form-control" id="assignment_title" name="assignment_title" required>
                            </div>
                            <div class="form-group">
                                <label for="assignment_instructions">Assignment Instructions:</label>
                                <textarea class="form-control" id="assignment_instructions" name="assignment_instructions" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="due_date">Due Date:</label>
                                <input type="datetime-local" class="form-control" id="due_date" name="due_date" required>
                            </div>
                            <div class="form-group">
                                <label for="course_id">Course ID:</label>
                                <select class="form-control" id="course_id" name="course_id" required>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="assignment_file">Upload Assignment File:</label>
                                <input type="file" class="form-control" id="assignment_file" name="assignment_file">
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



