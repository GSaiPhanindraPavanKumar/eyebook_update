<?php
include 'sidebar.php';
use Models\Database;
use Models\Course;
use Models\Spoc;

$conn = Database::getConnection();
if (!isset($_SESSION['email'])) {
    die('Email not set in session.');
}

$email = $_SESSION['email'];
$spoc = Spoc::getByEmail($conn, $email);
$universityId = $spoc['university_id'];

// Fetch courses from the database
$courses = Course::getAllByUniversity($conn, $universityId);

// Fetch all students
$sql = "SELECT id, completed_books FROM students WHERE university_id = :university_id";
$stmt = $conn->prepare($sql);
$stmt->execute(['university_id' => $universityId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate progress data for each course
$progressData = [];
foreach ($courses as $course) {
    $courseId = $course['id'];
    $totalBooks = !empty($course['course_book']) ? count(json_decode($course['course_book'], true) ?? []) : 0;
    $totalProgress = 0;
    $studentCount = 0;

    foreach ($students as $student) {
        $completedBooks = !empty($student['completed_books']) ? json_decode($student['completed_books'], true) : [];
        $studentCompletedBooks = $completedBooks[$courseId] ?? [];
        $progress = $totalBooks > 0 ? (count($studentCompletedBooks) / $totalBooks) * 100 : 0;
        $totalProgress += $progress;
        $studentCount++;
    }

    $averageProgress = $studentCount > 0 ? $totalProgress / $studentCount : 0;
    $progressData[$courseId] = $averageProgress;
}

// Separate ongoing and archived courses
$ongoingCourses = array_filter($courses, function($course) {
    return $course['status'] === 'ongoing';
});
$archivedCourses = array_filter($courses, function($course) {
    return $course['status'] === 'archived';
});
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage Courses</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin">
                <p class="card-title mb-0" style="font-size:x-large">Ongoing Courses</p><br>
                <div class="row">
                    <?php foreach ($ongoingCourses as $course): ?>
                        <div class="col-md-4 d-flex align-items-stretch">
                            <div class="card mb-4" style="width: 100%;">
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
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="card-title mb-0" style="font-size:x-large">Archived Courses</p><br>
                <div class="row">
                    <?php foreach ($archivedCourses as $course): ?>
                        <div class="col-md-4 d-flex align-items-stretch">
                            <div class="card mb-4" style="width: 100%;">
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

<script>
function confirmArchive() {
    return confirm("Are you sure you want to archive this course? You will not be able to make any updates to this course.");
}
</script>