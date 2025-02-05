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
$courses = Course::getAllspocByUniversity($conn, $universityId);

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
                    <?php if (empty($ongoingCourses)): ?>
                        <div class="col-12 text-center">
                            <img src="https://i.ibb.co/0SpmPCg/empty-box.png" alt="No courses found" style="max-width: 200px; margin: 20px auto;">
                            <p class="text-muted" style="font-size: 1.1em;">No ongoing courses found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ongoingCourses as $course): ?>
                            <div class="col-md-4 d-flex align-items-stretch">
                                <div class="card mb-4" style="width: 100%;">
                                    <div class="card-body">
                                        <h5 class="card-title" style="font-family:cursive"><?php echo htmlspecialchars($course['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" 
                                                    style="width: <?php echo $progressData[$course['id']]; ?>%;" 
                                                    aria-valuenow="<?php echo $progressData[$course['id']]; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="ml-2"><?php echo round($progressData[$course['id']], 2); ?>%</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <?php 
                                        $hashedId = base64_encode($course['id']);
                                        $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                                        ?>
                                        <a href="view_course/<?php echo $hashedId; ?>" class="card-link">View Course</a>
                                        <a href="view_labs/<?php echo $hashedId; ?>" class="card-link">View Lab</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <p class="card-title mb-0" style="font-size:x-large">Archived Courses</p><br>
                <div class="row">
                    <?php if (empty($archivedCourses)): ?>
                        <div class="col-12 text-center">
                            <img src="https://i.ibb.co/0SpmPCg/empty-box.png" alt="No archived courses" style="max-width: 200px; margin: 20px auto;">
                            <p class="text-muted" style="font-size: 1.1em;">No archived courses found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($archivedCourses as $course): ?>
                            <div class="col-md-4 d-flex align-items-stretch">
                                <div class="card mb-4" style="width: 100%;">
                                    <div class="card-body">
                                        <h5 class="card-title" style="font-family:cursive"><?php echo htmlspecialchars($course['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" 
                                                    style="width: <?php echo $progressData[$course['id']]; ?>%;" 
                                                    aria-valuenow="<?php echo $progressData[$course['id']]; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="ml-2"><?php echo round($progressData[$course['id']], 2); ?>%</span>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS -->


<script>
function confirmArchive() {
    return confirm("Are you sure you want to archive this course? You will not be able to make any updates to this course.");
}
</script>

<style>
/* Add some animation for the empty state images */
.text-center img {
    transition: transform 0.3s ease;
}

.text-center img:hover {
    transform: scale(1.05);
}

/* Style for the empty state message */
.text-center .text-muted {
    margin-top: 15px;
    color: #6c757d;
    font-weight: 500;
}

/* Add some spacing between sections */
.card-title.mb-0 {
    margin-top: 30px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}
</style>