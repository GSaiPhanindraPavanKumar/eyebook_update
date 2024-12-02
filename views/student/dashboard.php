<?php
include("sidebar.php");
use Models\Database;
use Models\Student;
use Models\Course;

$email = $_SESSION['email'];

// Fetch user data
$conn = Database::getConnection();
$userData = Student::getUserDataByEmail($conn, $email);

// Fetch today's classes
$todaysClasses = getTodaysClasses();

// Fetch courses and progress
$studentId = $_SESSION['student_id'];
$courses = getCoursesWithProgress($studentId, $userData['university_id']);
$ongoingCourses = array_filter($courses, function($course) {
    return $course['status'] === 'ongoing';
});
$leastProgressCourses = array_filter($ongoingCourses, function($course) {
    return !empty($course['course_book']) && $course['progress'] < 100;
});
$leastProgressCourses = array_slice($leastProgressCourses, 0, 5); // Get the least 5 progress courses
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($userData['name']); ?></em></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Student Details -->
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Student Details</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($userData['name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
                                <p><strong>Registration Number:</strong> <?php echo htmlspecialchars($userData['regd_no']); ?></p>
                                <p><strong>Section:</strong> <?php echo htmlspecialchars($userData['section']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Stream:</strong> <?php echo htmlspecialchars($userData['stream']); ?></p>
                                <p><strong>Year:</strong> <?php echo htmlspecialchars($userData['year']); ?></p>
                                <p><strong>Department:</strong> <?php echo htmlspecialchars($userData['dept']); ?></p>
                                <p><strong>University:</strong> <?php echo htmlspecialchars($userData['university']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Today's Classes -->
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Today's Classes</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Topic</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Join URL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($todaysClasses)): ?>
                                        <?php foreach ($todaysClasses as $class): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($class['topic']); ?></td>
                                                <td><?php echo htmlspecialchars($class['start_time']); ?></td>
                                                <td><?php echo htmlspecialchars($class['end_time']); ?></td>
                                                <td><a href="<?php echo htmlspecialchars($class['join_url']); ?>" target="_blank" class="btn btn-primary">Join</a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4">No classes scheduled for today.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Least Progress Courses -->
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title text-center">Least Progress Courses</h4>
                        <div class="d-flex justify-content-center">
                            <?php foreach ($leastProgressCourses as $course): ?>
                                <div class="col-md-2">
                                    <div class="card mb-4" style="height: 200px;">
                                        <div class="card-body text-center">
                                            <h5 class="card-title"><?php echo htmlspecialchars($course['name']); ?></h5>
                                            <canvas id="progressChart<?php echo $course['id']; ?>" width="60" height="60"></canvas>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    console.log("Course ID: <?php echo $course['id']; ?>, Progress: <?php echo $course['progress']; ?>");
                                                    var ctx = document.getElementById('progressChart<?php echo $course['id']; ?>').getContext('2d');
                                                    var progressChart = new Chart(ctx, {
                                                        type: 'doughnut',
                                                        data: {
                                                            datasets: [{
                                                                data: [<?php echo $course['progress']; ?>, <?php echo 100 - $course['progress']; ?>],
                                                                backgroundColor: ['#36a2eb', '#ff6384']
                                                            }],
                                                            labels: ['Completed', 'Remaining']
                                                        },
                                                        options: {
                                                            responsive: true,
                                                            maintainAspectRatio: false,
                                                            cutoutPercentage: 70,
                                                            legend: {
                                                                display: false
                                                            },
                                                            tooltips: {
                                                                callbacks: {
                                                                    label: function(tooltipItem, data) {
                                                                        var dataset = data.datasets[tooltipItem.datasetIndex];
                                                                        var total = dataset.data.reduce(function(previousValue, currentValue) {
                                                                            return previousValue + currentValue;
                                                                        });
                                                                        var currentValue = dataset.data[tooltipItem.index];
                                                                        var percentage = Math.floor(((currentValue / total) * 100) + 0.5);
                                                                        return percentage + "%";
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    });
                                                });
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<?php
// Define the function to fetch user data from the database
function getUserDataByEmail($email) {
    $conn = Database::getConnection();
    $stmt = $conn->prepare("SELECT students.*, universities.long_name as university FROM students JOIN universities ON students.university_id = universities.id WHERE students.email = :email");
    $stmt->execute(['email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Define the function to fetch today's classes
function getTodaysClasses() {
    $conn = Database::getConnection();
    $stmt = $conn->prepare("
        SELECT topic, start_time, duration, join_url,
               DATE_ADD(start_time, INTERVAL duration MINUTE) as end_time
        FROM virtual_classrooms
        WHERE DATE(start_time) = CURDATE()
        ORDER BY start_time ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Define the function to fetch courses with progress
function getCoursesWithProgress($studentId, $universityId) {
    $conn = Database::getConnection();
    $stmt = $conn->prepare("SELECT * FROM courses WHERE status = 'ongoing'");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT completed_books FROM students WHERE id = :student_id");
    $stmt->execute(['student_id' => $studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    $completedBooks = !empty($student['completed_books']) ? json_decode($student['completed_books'], true) : [];

    $filteredCourses = [];
    foreach ($courses as $course) {
        $courseUniversities = !empty($course['universities']) ? json_decode($course['universities'], true) : [];
        if (is_array($courseUniversities) && in_array($universityId, $courseUniversities)) {
            $courseId = $course['id'];
            $courseBooks = !empty($course['course_book']) ? json_decode($course['course_book'], true) : [];
            $totalBooks = is_array($courseBooks) ? count($courseBooks) : 0;
            $completedBooksCount = is_array($completedBooks[$courseId] ?? []) ? count($completedBooks[$courseId] ?? []) : 0;
            $course['progress'] = ($totalBooks > 0) ? ($completedBooksCount / $totalBooks) * 100 : 0;
            $filteredCourses[] = $course;
        }
    }

    usort($filteredCourses, function($a, $b) {
        return $a['progress'] <=> $b['progress'];
    });

    return $filteredCourses;
}
?>
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>