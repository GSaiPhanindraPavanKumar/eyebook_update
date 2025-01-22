<?php
include("sidebar.php");
use Models\Database;
use Models\Student;
use Models\Course;
use Models\Assignment;
use Models\VirtualClassroom;

$email = $_SESSION['email'];

// Fetch user data
$conn = Database::getConnection();
$userData = Student::getUserDataByEmail($conn, $email);

// Ensure university_short_name is set
$universityShortName = isset($userData['university_short_name']) ? htmlspecialchars($userData['university_short_name']) : '';

// Fetch today's classes
$todaysClasses = getTodaysClasses($userData['id']);

// Fetch courses and progress
$studentId = $_SESSION['student_id'];
$courses = getCoursesWithProgress($studentId);
$ongoingCourses = array_filter($courses, function($course) {
    return $course['status'] === 'ongoing';
});
$leastProgressCourses = array_filter($ongoingCourses, function($course) {
    return !empty($course['course_book']) && $course['progress'] < 100;
});
$leastProgressCourses = array_slice($leastProgressCourses, 0, 5); // Get the least 5 progress courses

// Fetch all virtual classes for the calendar
$virtualClasses = getAllVirtualClasses($studentId);

// Fetch assignments for the assigned courses
$assignments = Assignment::getAssignmentsByStudentId($conn, $email);

// Filter assignments to exclude those with passed due dates
$upcomingAssignments = array_filter($assignments, function($assignment) {
    return strtotime($assignment['due_date']) >= time();
});

// Filter virtual classes for the upcoming week
$upcomingClasses = array_filter($virtualClasses, function($class) {
    $startTime = strtotime($class['start_time']);
    $now = time();
    $oneWeekLater = strtotime('+1 week', $now);
    return $startTime >= $now && $startTime <= $oneWeekLater;
});

// Curated list of educational, inspiring, and motivational quotes
$quotes = [
    "Education is the most powerful weapon which you can use to change the world. - Nelson Mandela",
    "The beautiful thing about learning is that no one can take it away from you. - B.B. King",
    "The mind is not a vessel to be filled but a fire to be ignited. - Plutarch",
    "The only way to do great work is to love what you do. - Steve Jobs",
    "Success is not the key to happiness. Happiness is the key to success. - Albert Schweitzer",
    "Your time is limited, don't waste it living someone else's life. - Steve Jobs",
    "The best way to predict the future is to invent it. - Alan Kay",
    "Life is what happens when you're busy making other plans. - John Lennon",
    "The purpose of our lives is to be happy. - Dalai Lama",
    "Get busy living or get busy dying. - Stephen King",
    "You have within you right now, everything you need to deal with whatever the world can throw at you. - Brian Tracy",
    "Believe you can and you're halfway there. - Theodore Roosevelt",
    "The only limit to our realization of tomorrow is our doubts of today. - Franklin D. Roosevelt",
    "The future belongs to those who believe in the beauty of their dreams. - Eleanor Roosevelt",
    "The best way to find yourself is to lose yourself in the service of others. - Mahatma Gandhi",
    "The only person you are destined to become is the person you decide to be. - Ralph Waldo Emerson",
    "The only way to achieve the impossible is to believe it is possible. - Charles Kingsleigh",
    "The only limit to our realization of tomorrow is our doubts of today. - Franklin D. Roosevelt",
    "The only way to do great work is to love what you do. - Steve Jobs",
    "The only way to achieve the impossible is to believe it is possible. - Charles Kingsleigh"
];

// Select a random quote based on the current date
$thoughtOfTheDay = $quotes[date('z') % count($quotes)];
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($userData['name']); ?> - <?php echo $universityShortName; ?></em></h3>
                        <p class="mt-2"><?php echo htmlspecialchars($thoughtOfTheDay); ?></p>
                    </div>
                </div>
            </div>
        </div>
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
                                    <?php if (!empty($upcomingClasses) || !empty($upcomingAssignments)): ?>
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
                                        <?php foreach ($upcomingAssignments as $assignment): ?>
                                            <li class="list-group-item">
                                                <strong><?php echo htmlspecialchars($assignment['title']); ?></strong><br>
                                                Due Date: <?php echo htmlspecialchars($assignment['due_date']); ?><br>
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
            <!-- Least Progress Courses -->
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title text-center">Least Progress Courses</h4>
                        <div class="d-flex justify-content-center">
                            <?php if (!empty($leastProgressCourses)): ?>
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
                            <?php else: ?>
                                <p>No courses with progress available.</p>
                            <?php endif; ?>
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
    $stmt = $conn->prepare("SELECT students.*, universities.long_name as university, universities.short_name as university_short_name FROM students JOIN universities ON students.university_id = universities.id WHERE students.email = :email");
    $stmt->execute(['email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Define the function to fetch today's classes
function getTodaysClasses($studentId) {
    $conn = Database::getConnection();

    // Get the assigned course IDs for the student
    $assignedCourses = Student::getAssignedCourses($conn, $studentId);

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
function getCoursesWithProgress($studentId) {
    $conn = Database::getConnection();

    // Fetch the assigned courses for the student
    $stmt = $conn->prepare("SELECT assigned_courses, completed_books FROM students WHERE id = :student_id");
    $stmt->execute(['student_id' => $studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    $assignedCourses = !empty($student['assigned_courses']) ? json_decode($student['assigned_courses'], true) : [];
    $completedBooks = !empty($student['completed_books']) ? json_decode($student['completed_books'], true) : [];

    if (empty($assignedCourses)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($assignedCourses), '?'));
    $stmt = $conn->prepare("SELECT id, name, description, course_book, status FROM courses WHERE id IN ($placeholders)");
    $stmt->execute($assignedCourses);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($courses as &$course) {
        $courseId = $course['id'];
        $courseBooks = !empty($course['course_book']) ? json_decode($course['course_book'], true) : [];
        $totalBooks = is_array($courseBooks) ? count($courseBooks) : 0;
        $completedBooksCount = is_array($completedBooks[$courseId] ?? []) ? count($completedBooks[$courseId] ?? []) : 0;
        $course['progress'] = ($totalBooks > 0) ? ($completedBooksCount / $totalBooks) * 100 : 0;
    }

    usort($courses, function($a, $b) {
        return $a['progress'] <=> $b['progress'];
    });

    return $courses;
}

// Define the function to fetch all virtual classes for the student
function getAllVirtualClasses($studentId) {
    $conn = Database::getConnection();

    // Get the assigned course IDs for the student
    $assignedCourses = Student::getAssignedCourses($conn, $studentId);

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
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    'title' => $assignment['title'] . ' (Due)',
                    'start' => $assignment['due_date'],
                    'color' => 'red',
                    'url' => '/admin/view_assignment/' . $assignment['id'] // URL to view the assignment
                ];
            }, $assignments)
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