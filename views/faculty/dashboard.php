<?php
include("sidebar.php");
use Models\Database;
use Models\Faculty;
use Models\Course;
use Models\Assignment;

$email = $_SESSION['email'];

// Fetch user data
$conn = Database::getConnection();
$userData = Faculty::getUserDataByEmail($conn, $email);

// Fetch today's classes
$todaysClasses = getTodaysClasses($userData['id']);

// Fetch ongoing courses and progress
$courses = getCoursesWithProgress($userData['id']);
$ongoingCourses = array_filter($courses, function($course) {
    return $course['status'] === 'ongoing';
});
$leastProgressCourses = array_filter($ongoingCourses, function($course) {
    return !empty($course['course_book']) && $course['progress'] < 100;
});
$leastProgressCourses = array_slice($leastProgressCourses, 0, 5); // Get the least 5 progress courses

// Fetch all virtual classes for the faculty
$virtualClasses = getAllVirtualClasses($userData['id']);
$upcomingClasses = array_filter($virtualClasses, function($class) {
    return strtotime($class['start_time']) > time();
});
$assignments = Assignment::getAssignmentsByfacultyId($conn, $email);

// Ensure university_short_name is set
$universityShortName = isset($userData['university_short_name']) ? htmlspecialchars($userData['university_short_name']) : '';
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <?php if ($userData): ?>
                            <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($userData['name']); ?> - <?php echo $universityShortName; ?></em></h3>
                        <?php else: ?>
                            <h3 class="font-weight-bold">Hello, <em>User</em></h3>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add this section -->
        <div class="row">
            <!-- Calendar and Weekly Agenda -->
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="card-title">Calendar</h4>
                                <div id="calendar" style="max-width: 100%; height: 400px;"></div>
                            </div>
                            <div class="col-md-6">
                                <h4 class="card-title">Weekly Agenda</h4>
                                <ul class="list-group">
                                    <?php if (!empty($upcomingClasses)): ?>
                                        <?php foreach ($upcomingClasses as $class): ?>
                                            <?php
                                            $endTime = date('Y-m-d H:i:s', strtotime($class['start_time'] . ' + ' . $class['duration'] . ' minutes'));
                                            ?>
                                            <li class="list-group-item">
                                                <strong><?php echo htmlspecialchars($class['topic']); ?></strong><br>
                                                <?php echo htmlspecialchars($class['start_time']); ?> - <?php echo htmlspecialchars($endTime); ?><br>
                                                <a href="<?php echo htmlspecialchars($class['join_url']); ?>" target="_blank">Join</a>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="list-group-item">No upcoming weekly agenda.</li>
                                    <?php endif; ?>
                                </ul>
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
    $stmt = $conn->prepare("SELECT faculty.*, universities.long_name as university_name, universities.short_name as university_short_name FROM faculty JOIN universities ON faculty.university_id = universities.id WHERE faculty.email = :email");
    $stmt->execute(['email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Define the function to fetch today's classes
function getTodaysClasses($facultyId) {
    $conn = Database::getConnection();

    // Get the assigned course IDs for the faculty
    $assignedCourses = Faculty::getAssignedCourses($conn, $facultyId);

    if (empty($assignedCourses)) {
        return [];
    }

    // Get the virtual class IDs for the assigned courses
    $virtualClassIds = Course::getVirtualClassIds($conn, $assignedCourses);

    if (empty($virtualClassIds)) {
        return [];
    }

    // Fetch today's classes for the assigned virtual class IDs
    $placeholders = implode(',', array_fill(0, count($virtualClassIds), '?'));
    $stmt = $conn->prepare("
        SELECT topic, start_time, duration, join_url,
               DATE_ADD(start_time, INTERVAL duration MINUTE) as end_time
        FROM virtual_classrooms
        WHERE DATE(start_time) = CURDATE() AND id IN ($placeholders)
        ORDER BY start_time ASC
    ");
    $stmt->execute($virtualClassIds);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Define the function to fetch courses with progress
function getCoursesWithProgress($faculty_id) {
    $conn = Database::getConnection();
    $assignedCourses = Faculty::getAssignedCourses($conn, $faculty_id);

    if (empty($assignedCourses)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($assignedCourses), '?'));
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id IN ($placeholders)");
    $stmt->execute($assignedCourses);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($courses as &$course) {
        $courseId = $course['id'];
        $courseBooks = !empty($course['course_book']) ? json_decode($course['course_book'], true) : [];
        $totalBooks = is_array($courseBooks) ? count($courseBooks) : 0;
        $totalProgress = 0;
        $studentCount = 0;

        // Fetch assigned students for the course
        $assignedStudents = Course::getAssignedStudents($conn, $courseId);

        foreach ($assignedStudents as $student) {
            $completedBooks = !empty($student['completed_books']) ? json_decode($student['completed_books'], true) : [];
            $studentCompletedBooks = $completedBooks[$courseId] ?? [];
            $progress = $totalBooks > 0 ? (count($studentCompletedBooks) / $totalBooks) * 100 : 0;
            $totalProgress += $progress;
            $studentCount++;
        }

        $course['progress'] = ($studentCount > 0) ? ($totalProgress / $studentCount) : 0;
    }

    usort($courses, function($a, $b) {
        return $a['progress'] <=> $b['progress'];
    });

    return $courses;
}

// Define the function to fetch all virtual classes for the faculty
function getAllVirtualClasses($facultyId) {
    $conn = Database::getConnection();

    // Get the assigned course IDs for the faculty
    $assignedCourses = Faculty::getAssignedCourses($conn, $facultyId);

    if (empty($assignedCourses)) {
        return [];
    }

    // Get the virtual class IDs for the assigned courses
    $virtualClassIds = Course::getVirtualClassIds($conn, $assignedCourses);

    if (empty($virtualClassIds)) {
        return [];
    }

    // Fetch all virtual classes for the assigned virtual class IDs
    $placeholders = implode(',', array_fill(0, count($virtualClassIds), '?'));
    $stmt = $conn->prepare("
        SELECT topic, start_time, duration, join_url
        FROM virtual_classrooms
        WHERE id IN ($placeholders)
        ORDER BY start_time ASC
    ");
    $stmt->execute($virtualClassIds);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!-- Include FullCalendar -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        // headerToolbar: {
        //     left: 'prev,next today',
        //     center: 'title',
        //     right: 'dayGridMonth,timeGridWeek,timeGridDay'
        // },
        headerToolbar: {
            left: 'title',
            right: 'today prev,next'
        },
        events: <?php echo json_encode(array_merge(
            array_map(function($class) {
                return [
                    'title' => $class['topic'],
                    'start' => $class['start_time'],
                    'end' => date('Y-m-d\TH:i:s', strtotime($class['start_time'] . ' + ' . $class['duration'] . ' minutes')),
                    'url' => $class['join_url']
                ];
            }, $virtualClasses),
            array_map(function($assignment) {
                return [
                    'title' => $assignment['title'],
                    'start' => $assignment['start_time'],
                    'end' => $assignment['due_date'],
                    'color' => 'red',
                    'url' => '/admin/view_assignment/' . $assignment['id'] // URL to view the assignment
                ];
            }, $assignments),
            array_map(function($contest) {
                return [
                    'title' => $contest['title'],
                    'start' => $contest['start_date'],
                    'end' => $contest['end_date'],
                    'color' => 'green',
                    'url' => '/admin/view_contest/' . $contest['id'] // URL to view the contest
                ];
            }, $contests)
        )); ?>,
        eventDisplay: 'block' // Ensure event titles are always visible
    });
    calendar.render();
});
</script>
<style>
/* Ensure FullCalendar navigation buttons are always visible */
.fc .fc-toolbar-title {
    font-size: 1.5em;
    font-weight: bold;
}

.fc .fc-button {
    background-color: transparent;
    border: 1px solid #007bff;
    color: #007bff;
    transition: background-color 0.3s, color 0.3s;
}

.fc .fc-button:hover {
    background-color: #007bff;
    color: #fff;
}

.fc .fc-button:focus {
    box-shadow: none;
}

.fc .fc-button-group .fc-button {
    margin-right: 5px;
}

.fc .fc-toolbar-chunk {
    display: flex;
    align-items: center;
}

.fc .fc-toolbar-chunk:first-child {
    justify-content: flex-start;
}

.fc .fc-toolbar-chunk:last-child {
    justify-content: flex-end;
}

.fc .fc-toolbar-chunk .fc-button-group {
    display: flex;
    align-items: center;
}

.fc .fc-toolbar-chunk .fc-button-group .fc-button {
    margin-right: 5px;
}

.fc .fc-toolbar-chunk .fc-button-group .fc-button:last-child {
    margin-right: 0;
}

/* Ensure navigation buttons are always visible */
.fc .fc-toolbar .fc-toolbar-chunk:first-child .fc-button-group .fc-button {
    opacity: 1 !important;
    visibility: visible !important;
}

/* Ensure the entire toolbar is always visible */
.fc .fc-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>