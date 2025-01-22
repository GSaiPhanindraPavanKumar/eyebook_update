<?php include 'sidebar.php'; ?>
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
        <!-- Calendar and Weekly Agenda -->
        <div class="row">
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
                                    <?php
                                    $currentDateTime = new DateTime();
                                    $upcomingClasses = array_filter($virtualClasses, function($class) use ($currentDateTime) {
                                        $classStartTime = new DateTime($class['start_time']);
                                        return $classStartTime >= $currentDateTime;
                                    });
                                    $upcomingAssignments = array_filter($assignments, function($assignment) use ($currentDateTime) {
                                        $assignmentDueDate = new DateTime($assignment['due_date']);
                                        return $assignmentDueDate >= $currentDateTime;
                                    });
                                    ?>
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
                                                Due: <?php echo htmlspecialchars($assignment['due_date']); ?><br>
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
        <!-- Existing content -->
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <canvas id="facultyStudentChart" width="400" height="400"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <canvas id="courseFacultyChart" width="400" height="400"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0">Faculties</p>
                        <div class="table-responsive">
                            <table class="table table-striped table-borderless">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($faculties)) {
                                        foreach ($faculties as $row) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='3'>No data available</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0">Courses</p>
                        <div class="table-responsive">
                            <table class="table table-striped table-borderless">
                                <thead>
                                    <tr>
                                        <th>Course Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($courses)) {
                                        foreach ($courses as $row) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='1'>No courses available</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx1 = document.getElementById('facultyStudentChart').getContext('2d');
    var facultyStudentChart = new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: ['Faculty', 'Students'],
            datasets: [{
                data: [<?php echo $faculty_count; ?>, <?php echo $student_count; ?>],
                backgroundColor: ['#FF6384', '#36A2EB'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Faculty vs Students'
                }
            }
        }
    });

    var ctx2 = document.getElementById('courseFacultyChart').getContext('2d');
    var courseFacultyChart = new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: ['Courses', 'Faculties'],
            datasets: [{
                data: [<?php echo $course_count; ?>, <?php echo $faculty_count; ?>],
                backgroundColor: ['#FFCE56', '#4BC0C0'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Courses vs Faculties'
                }
            }
        }
    });
</script>

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
                    'url' => '/spoc/view_assignment/' . $assignment['id'] // URL to view the assignment
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