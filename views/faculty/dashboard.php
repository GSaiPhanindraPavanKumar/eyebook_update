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
        headerToolbar: {
            left: 'title backButton',
            right: 'today prev,next'
        },
        customButtons: {
            backButton: {
                text: '← Back',
                click: function() {
                    calendar.changeView('dayGridMonth');
                    document.querySelector('.fc-backButton-button').style.display = 'none';
                }
            }
        },
        views: {
            dayGridMonth: {
                dayMaxEventRows: false,
                dayMaxEvents: false,
            },
            listDay: {
                eventDidMount: function(info) {
                    let eventEl = info.el;
                    let title = info.event.title;
                    let typeLabel = '';
                    
                    // Determine event type based on URL
                    if (info.event.url && (info.event.url.includes('zoom') || info.event.url.includes('join_url'))) {
                        typeLabel = 'Meeting';
                        eventEl.classList.add('list-meeting-event');
                    } else if (info.event.url && info.event.url.includes('view_assignment')) {
                        typeLabel = 'Assignment';
                        eventEl.classList.add('list-assignment-event');
                    } else if (info.event.url && info.event.url.includes('view_contest')) {
                        typeLabel = 'Contest';
                        eventEl.classList.add('list-contest-event');
                    }
                    
                    // Hide time element
                    const timeEl = eventEl.querySelector('.fc-list-event-time');
                    if (timeEl) {
                        timeEl.style.display = 'none';
                    }
                    
                    // Update the title with type label
                    eventEl.querySelector('.fc-list-event-title').innerHTML = 
                        `<span class="event-type-label">${typeLabel}:</span> ${title}`;

                    // Add click handler
                    eventEl.style.cursor = 'pointer';
                    eventEl.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (info.event.url && (info.event.url.includes('zoom') || info.event.url.includes('join_url'))) {
                            window.open(info.event.url, '_blank');
                        } else {
                            window.location.href = info.event.url;
                        }
                    });
                }
            }
        },
        dayCellDidMount: function(info) {
            const date = info.date;
            const cellDate = new Date(date.setHours(0,0,0,0));
            
            // Get all events that are active on this day
            const dayEvents = calendar.getEvents().filter(event => {
                const eventStart = new Date(event.start);
                eventStart.setHours(0,0,0,0);
                let eventEnd;
                
                if (event.end) {
                    eventEnd = new Date(event.end);
                    eventEnd.setHours(23,59,59,999);
                } else {
                    eventEnd = new Date(eventStart);
                    eventEnd.setHours(23,59,59,999);
                }

                // Check if current date falls within event range (inclusive)
                const cellTime = cellDate.getTime();
                const startTime = eventStart.getTime();
                const endTime = eventEnd.getTime();
                
                return cellTime >= startTime && cellTime <= endTime;
            });

            if (dayEvents.length > 0) {
                // Count events by URL pattern
                const meetings = dayEvents.filter(event => 
                    event.url && (event.url.includes('zoom') || event.url.includes('join_url'))
                ).length;
                const assignments = dayEvents.filter(event => 
                    event.url && event.url.includes('view_assignment')
                ).length;
                const contests = dayEvents.filter(event => 
                    event.url && event.url.includes('view_contest')
                ).length;

                // Create counters container
                const countersContainer = document.createElement('div');
                countersContainer.className = 'event-counters-container';

                // Add individual counters for each type if they exist
                if (meetings > 0) {
                    const meetingCounter = document.createElement('span');
                    meetingCounter.className = 'type-counter meeting-counter';
                    meetingCounter.innerHTML = meetings;
                    meetingCounter.title = `${meetings} Meeting${meetings > 1 ? 's' : ''}`;
                    countersContainer.appendChild(meetingCounter);
                }
                if (assignments > 0) {
                    const assignmentCounter = document.createElement('span');
                    assignmentCounter.className = 'type-counter assignment-counter';
                    assignmentCounter.innerHTML = assignments;
                    assignmentCounter.title = `${assignments} Assignment${assignments > 1 ? 's' : ''}`;
                    countersContainer.appendChild(assignmentCounter);
                }
                if (contests > 0) {
                    const contestCounter = document.createElement('span');
                    contestCounter.className = 'type-counter contest-counter';
                    contestCounter.innerHTML = contests;
                    contestCounter.title = `${contests} Contest${contests > 1 ? 's' : ''}`;
                    countersContainer.appendChild(contestCounter);
                }

                // Only append if there are counters
                if (countersContainer.children.length > 0) {
                    info.el.appendChild(countersContainer);
                }
            }
        },
        dateClick: function(info) {
            calendar.changeView('listDay', info.date);
            document.querySelector('.fc-backButton-button').style.display = 'flex';
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
                    'url' => '/faculty/view_assignment/' . $assignment['id']
                ];
            }, $assignments),
            array_map(function($contest) {
                return [
                    'title' => $contest['title'],
                    'start' => $contest['start_date'],
                    'end' => $contest['end_date'],
                    'url' => '/faculty/view_contest/' . $contest['id']
                ];
            }, $contests)
        )); ?>
    });
    
    calendar.render();
    document.querySelector('.fc-backButton-button').style.display = 'none';
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

/* Add new counter styling */
.event-counters-container {
    position: absolute;
    bottom: 4px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 4px;
    justify-content: center;
    align-items: center;
    z-index: 2;
}

.type-counter {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7em;
    font-weight: bold;
    color: white;
    cursor: pointer;
}

.meeting-counter {
    background-color: #4B49AC;
}

.assignment-counter {
    background-color: #FF4747;
}

/* Add contest counter style */
.contest-counter {
    background-color: #28A745;
}

/* List view styling */
.fc-list-event {
    cursor: pointer;
    padding: 12px 16px !important;
    border: none !important;
    margin: 8px !important;
    border-radius: 6px;
    background-color: #f8f9fa !important;
    transition: all 0.2s ease;
}

.fc-list-event-title {
    color: #333 !important;
    font-weight: 500 !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.event-type-label {
    color: #666 !important;
    font-weight: 500;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    min-width: 100px !important;
}

.event-type-label::before {
    content: '';
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 4px;
}

/* Colored dots for list view */
.list-meeting-event .event-type-label::before {
    background-color: #4B49AC;
}

.list-assignment-event .event-type-label::before {
    background-color: #FF4747;
}

/* Add contest dot style */
.list-contest-event .event-type-label::before {
    background-color: #28A745;
}

/* Hide default event displays */
.fc-daygrid-event-harness,
.fc-daygrid-event,
.fc-daygrid-dot-event,
.fc-daygrid-more-link {
    display: none !important;
}

/* Back button styling */
.fc-backButton-button {
    background-color: #4B49AC !important;
    border-color: #4B49AC !important;
    color: white !important;
    font-size: 0.85em !important;
    padding: 4px 12px !important;
    border-radius: 4px !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    width: 85px !important;
    height: 32px !important;
    cursor: pointer !important;
}

/* Show back button in list view */
.fc-listDay-view ~ .fc-toolbar .fc-backButton-button,
.fc-backButton-button[style*="display: flex"],
.fc-backButton-button[style*="display: block"] {
    display: flex !important;
}

/* Position the back button */
.fc-toolbar-chunk {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}
</style>