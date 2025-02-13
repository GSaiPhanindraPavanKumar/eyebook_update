<?php
include("sidebar.php");
use Models\Database;
use Models\VirtualClassroom;
use Models\Assignment;
use Models\Contest;

// Fetch all virtual classes
$virtualClassroomModel = new VirtualClassroom(Database::getConnection());
$virtualClasses = $virtualClassroomModel->getAll();
$upcomingClasses = array_filter($virtualClasses, function($class) {
    return strtotime($class['start_time']) > time();
});

// Fetch all assignments
$assignmentModel = new Assignment();
$assignments = $assignmentModel->getAll(Database::getConnection());
$upcomingAssignments = array_filter($assignments, function($assignment) {
    return strtotime($assignment['due_date']) > time();
});

$assignmentModel = new Assignment();
$assignments = $assignmentModel->getAll($conn);
$upcomingAssignments = array_filter($assignments, function($assignment) {
    return strtotime($assignment['start_time']) > time();
});

// Fetch all contests
$contestModel = new Contest();
$contests = $contestModel->getAll($conn);
$upcomingContests = array_filter($contests, function($contest) {
    return strtotime($contest['start_date']) > time();
});

$contestModel = new Contest();
$contests = $contestModel->getAll($conn);
$upcomingContests = array_filter($contests, function($contest) {
    return strtotime($contest['end_date']) > time();
});
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($user['name']); ?></em></h3>
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
                                    <?php if (!empty($upcomingClasses) || !empty($upcomingAssignments) || !empty($upcomingContests)): ?>
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
                                                Start: <?php echo htmlspecialchars($assignment['start_time']); ?><br>
                                                Due: <?php echo htmlspecialchars($assignment['due_date']); ?><br>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php foreach ($upcomingContests as $contest): ?>
                                            <li class="list-group-item">
                                                <strong><?php echo htmlspecialchars($contest['title']); ?></strong><br>
                                                Start: <?php echo htmlspecialchars($contest['start_date']); ?><br>
                                                End: <?php echo htmlspecialchars($contest['end_date']); ?><br>
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
                <div class="card tale-bg">
                    <canvas id="myPieChart" width="200" height="100" style="width: 200px; height: 100px;"></canvas>
                </div>
            </div>
            <div class="col-md-6 grid-margin transparent">
                <div class="row">
                    <div class="col-md-6 mb-4 stretch-card transparent">
                        <div class="card card-tale" onclick="window.location='/admin/manage_university'" style="cursor: pointer;">
                            <div class="card-body">
                                <div class="ripple-2"></div>
                                <p class="mb-4">Total Universities</p>
                                <p class="fs-30 mb-2"><?php echo $university_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4 stretch-card transparent">
                        <div class="card card-dark-blue" onclick="window.location='/admin/manage_students'" style="cursor: pointer;">
                            <div class="card-body">
                            <div class="ripple-2"></div>

                                <p class="mb-4">Total Students</p>
                                <p class="fs-30 mb-2"><?php echo $student_count; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-4 mb-lg-0 stretch-card transparent">
                        <div class="card card-light-blue">
                            <div class="card-body">
                                <p class="mb-4">SPOCs</p>
                                <p class="fs-30 mb-2"><?php echo $spoc_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 stretch-card transparent">
                        <div class="card card-light-danger" onclick="window.location='/admin/manage_courses'" style="cursor: pointer;">
                            <div class="card-body">
                            <div class="ripple-2"></div>

                                <p class="mb-4">Courses</p>
                                <p class="fs-30 mb-2"><?php echo $course_count; ?></p>
                            </div>
                        </div>
                    </div>
                </div><br>
                <div class="row">
                    <div class="col-md-6 mb-4 mb-lg-0 stretch-card transparent">
                        <div class="card card-light-blue" onclick="window.location='/admin/transactions'" style="cursor: pointer;">
                            <div class="card-body">
                            <div class="ripple-2"></div>

                                <p class="mb-4">Transactions</p>
                                <p class="fs-30 mb-2"><?php echo $transactions_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 stretch-card transparent">
                        <div class="card card-light-danger" onclick="window.location='/admin/manage_public_courses'" style="cursor: pointer;">
                            <div class="card-body">
                            <div class="ripple-2"></div>

                                <p class="mb-4">Public Course</p>
                                <p class="fs-30 mb-2"><?php echo $publiccourse_count; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0">Universities</p>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th class="pl-0 pb-2 border-bottom">University Name</th>
                                        <th class="border-bottom pb-2">Short Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($universities)) {
                                        foreach ($universities as $row) {
                                            echo "<tr>";
                                            echo "<td class='pl-0'>" . htmlspecialchars($row['long_name']) . "</td>";
                                            echo "<td><p class='mb-0'><span class='font-weight-bold mr-2'>" . htmlspecialchars($row['short_name']) . "</span></p></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='2'>No data available</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 stretch-card grid-margin">
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <p class="card-title">User Usage Chart</p>
                                <select id="timeRange" class="form-control mb-3">
                                    <option value="today">Today</option>
                                    <option value="month">This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                </select>
                                <canvas id="userUsageChart" width="350" height="200"></canvas>
                                <a href="/admin/download_usage_report" class="btn btn-secondary mt-3">Download Detailed Report</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 stretch-card grid-margin grid-margin-md-0">
                        <div class="card data-icon-card-primary">
                            <div class="card-body">
                                <p class="card-title text-white">Number of Meetings</p>
                                <div class="row">
                                    <div class="col-8 text-white">
                                        <h3>
                                            <?php echo $meeting_count; ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title">Courses</p>
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th class="pl-0 pb-2 border-bottom">Course Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($courses)) {
                                    foreach ($courses as $row) {
                                        echo "<tr>";
                                        echo "<td class='pl-0'>" . htmlspecialchars($row['name']) . "</td>";
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
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('myPieChart').getContext('2d');
    var myPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Universities', 'Students', 'SPOCs', 'Courses'],
            datasets: [{
                data: [<?php echo $university_count; ?>, <?php echo $student_count; ?>, <?php echo $spoc_count; ?>, <?php echo $course_count; ?>],
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
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
                    text: 'Distribution of Entities'
                }
            }
        }
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('userUsageChart').getContext('2d');
    var userUsageChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($usageData)); ?>, // e.g., ['Admin', 'SPOC', 'Faculty', 'Student']
            datasets: [{
                label: 'User Usage',
                data: <?php echo json_encode(array_values($usageData)); ?>, // e.g., [10, 20, 30, 40]
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                borderColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                borderWidth: 1
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
                    text: 'User Usage'
                }
            }
        }
    });

    document.getElementById('timeRange').addEventListener('change', function() {
        var timeRange = this.value;
        fetch('/admin/getUsageData?timeRange=' + timeRange)
            .then(response => response.json())
            .then(data => {
                userUsageChart.data.labels = data.labels;
                userUsageChart.data.datasets[0].data = data.data;
                userUsageChart.update();
            });
    });
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
                    'url' => $class['join_url'],
                    'type' => 'virtual_classroom'
                ];
            }, $virtualClasses),
            array_map(function($assignment) {
                return [
                    'title' => $assignment['title'],
                    'start' => $assignment['start_time'],
                    'end' => $assignment['due_date'],
                    'color' => 'red',
                    'url' => '/admin/view_assignment/' . $assignment['id'],
                    'type' => 'assignment'
                ];
            }, $assignments),
            array_map(function($contest) {
                return [
                    'title' => $contest['title'],
                    'start' => $contest['start_date'],
                    'end' => $contest['end_date'],
                    'color' => 'green',
                    'url' => '/admin/view_contest/' . $contest['id'],
                    'type' => 'contest'
                ];
            }, $contests)
        )); ?>,
        eventDisplay: 'block',
        eventClick: function(info) {
            if (info.event.extendedProps.type === 'virtual_classroom') {
                window.open(info.event.url, '_blank');
            } else {
                window.location.href = info.event.url;
            }
            info.jsEvent.preventDefault(); // Prevent the default action
        }
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

/* Add hover effect for clickable cards */
.card[onclick] {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    overflow: hidden;
}

.card[onclick]:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Add clickable indicator dot */
.card[onclick]::before {
    content: '';
    position: absolute;
    top: 8px;
    right: 8px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    animation: none;  /* Remove pulse animation from main dot */
}

/* Add ripple effect container */
.card[onclick]::after {
    content: '';
    position: absolute;
    top: 8px;
    right: 8px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    animation: ripple 1.5s linear infinite;
}

/* Card-specific dot colors */
.card.card-tale[onclick]::before {
    background-color: #466ccf;  /* Darker blue matching theme */
}
.card.card-tale[onclick]::after {
    border: 2px solid #4B49AC;
    box-shadow: 0 0 10px rgba(75, 73, 172, 0.5);
}

/* Second ripple for universities card */
.card.card-tale[onclick] .ripple-2 {
    border: 2px solid #4B49AC;
    box-shadow: 0 0 10px rgba(75, 73, 172, 0.3);
}

.card.card-dark-blue[onclick]::before {
    background-color: #7676d9;
}
.card.card-dark-blue[onclick]::after {
    border: 2px solid #7676d9;
}

.card.card-light-blue[onclick]::before {
    background-color: #b4caff;
}
.card.card-light-blue[onclick]::after {
    border: 2px solid #b4caff;
}

.card.card-light-danger[onclick]::before {
    background-color: #ffb3b7;
}
.card.card-light-danger[onclick]::after {
    border: 2px solid #ffb3b7;
}

@keyframes ripple {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(3);
        opacity: 0;
    }
}

/* Add multiple ripples */
.card[onclick]::before {
    z-index: 2;  /* Keep dot on top */
}

.card[onclick]::after {
    z-index: 1;
}

/* Second ripple with delay */
.card[onclick] .ripple-2 {
    content: '';
    position: absolute;
    top: 8px;
    right: 8px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    animation: ripple 1.5s linear infinite;
    animation-delay: 0.5s;  /* Delay second ripple */
}
</style>
