<?php
include 'sidebar.php';
use Models\Database;
use Models\Student;

$conn = Database::getConnection();
if (!isset($_SESSION['student_id'])) {
    die('Student ID not set in session.');
}
$studentId = $_SESSION['student_id']; // Assuming student ID is stored in session

// Fetch courses from the database
$sql = "SELECT id, name, description, course_book FROM courses";
$stmt = $conn->query($sql);
$courses = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $courses[] = $row;
}

// Fetch the student's completed books
$student = Student::getById($conn, $studentId);
$completedBooks = json_decode($student['completed_books'], true) ?? [];

// Calculate progress data for each course
$progressData = [];
foreach ($courses as $course) {
    $courseId = $course['id'];
    $studentCompletedBooks = $completedBooks[$courseId] ?? [];
    $totalBooks = !empty($course['course_book']) ? count(json_decode($course['course_book'], true) ?? []) : 0;
    $progress = $totalBooks > 0 ? (count($studentCompletedBooks) / $totalBooks) * 100 : 0;
    $progressData[$courseId] = $progress;
}

// No need to close the connection explicitly in PDO
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
            <div class="col-md-12 grid-margin">
                <p class="card-title mb-0" style="font-size:x-large">My Courses</p><br>
                <div class="row">
                    <?php foreach ($courses as $course): ?>
                        <div class="col-md-4 d-flex align-items-stretch">
                            <div class="card mb-4" style="width: 100%;">
                                <!-- <img class="card-img-top" src="../views\public\assets\images\book.jpeg" alt="Card image cap" height="60%"> -->
                                <div class="card-body">
                                    <h5 class="card-title" style="font-family:cursive"><?php echo htmlspecialchars($course['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $progressData[$course['id']]; ?>%;" aria-valuenow="<?php echo $progressData[$course['id']]; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo round($progressData[$course['id']], 2); ?>%</div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php 
                                    $hashedId = base64_encode($course['id']);
                                    $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                                    ?>
                                    <a href="view_course/<?php echo $hashedId; ?>" class="card-link">View Course</a>
                                    <a href="/faculty/discussion_forum/<?php echo $course['id']; ?>" class="card-link">Chat Room</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">