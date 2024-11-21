<?php
include 'sidebar.php';
use Models\Database;
use Models\Assignment;

$conn = Database::getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submission_id = $_POST["submission_id"];
    $grade = $_POST["grade"];
    $feedback = $_POST["feedback"];

    $sql = "UPDATE submissions SET grade = :grade, feedback = :feedback WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':grade', $grade);
    $stmt->bindParam(':feedback', $feedback);
    $stmt->bindParam(':id', $submission_id);
    $stmt->execute();

    echo "<div class='alert alert-success'>Grade and feedback have been updated.</div>";
}

$assignment_id = $_GET['assignment_id'];
$student_id = $_GET['student_id'];

$sql = "SELECT * FROM submissions WHERE assignment_id = :assignment_id AND student_id = :student_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':assignment_id', $assignment_id);
$stmt->bindParam(':student_id', $student_id);
$stmt->execute();
$submission = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Grade Assignment</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Grade Assignment</h5>
                        <form action="grade_assignment.php" method="post">
                            <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                            <div class="form-group">
                                <label for="grade">Grade:</label>
                                <input type="text" class="form-control" id="grade" name="grade" value="<?= htmlspecialchars($submission['grade']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="feedback">Feedback:</label>
                                <textarea class="form-control" id="feedback" name="feedback" required><?= htmlspecialchars($submission['feedback']) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Grade</button>
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