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
                    'url' => '/spoc/view_assignment/' . $assignment['id']
                ];
            }, $assignments)
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

/* Replace the existing event-counters-container and type-counter styles with these updated versions */

.event-counters-container {
    position: absolute;
    bottom: 2px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 3px;
    justify-content: center;
    align-items: center;
    z-index: 5;
    pointer-events: none;
    width: 100%;
    padding: 2px 0;
}

.type-counter {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: bold;
    color: white;
    cursor: pointer;
    pointer-events: auto;
    position: relative;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

/* Update the calendar day cell to ensure proper positioning */
.fc .fc-daygrid-day {
    position: relative !important;
    min-height: 85px !important;
}

/* Ensure the day cell content doesn't overlap with counters */
.fc .fc-daygrid-day-frame {
    min-height: 100%;
    position: relative;
    padding-bottom: 20px !important;
}

/* Update specific counter colors with improved visibility */
.meeting-counter {
    background-color: #4B49AC;
    border: 1px solid rgba(255,255,255,0.3);
}

.assignment-counter {
    background-color: #FF4747;
    border: 1px solid rgba(255,255,255,0.3);
}

/* Add hover effect for better interaction */
.type-counter:hover {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

/* Hide default event displays */
.fc-daygrid-event-harness,
.fc-daygrid-event,
.fc-daygrid-dot-event,
.fc-daygrid-more-link {
    display: none !important;
}

/* Keep the date number styling */
.fc .fc-daygrid-day-top {
    display: flex !important;
    justify-content: flex-start !important;
}

.fc .fc-daygrid-day-number {
    position: absolute !important;
    top: 4px !important;
    left: 4px !important;
    padding: 4px !important;
    font-size: 0.9em;
    color: #333;
}

/* Update list view event styling */
.fc-list-event {
    cursor: pointer;
    padding: 12px 16px !important;
    border: none !important;
    margin: 8px !important;
    border-radius: 6px;
    background-color: var(--card-bg) !important;
    transition: all 0.2s ease;
}

.fc-list-event:hover {
    background-color: var(--hover-bg) !important;
}

/* Style event title and label */
.fc-list-event-title {
    color: var(--text-color) !important;
    font-weight: 500 !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.event-type-label {
    color: var(--text-muted) !important;
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

/* Add colored dots before labels */
.list-meeting-event .event-type-label::before {
    background-color: #4B49AC;
}

.list-assignment-event .event-type-label::before {
    background-color: #FF4747;
}

/* Hide time */
.fc-list-event-time {
    display: none !important;
}

/* Style the list day header */
.fc-list-day-cushion {
    background-color: var(--card-bg) !important;
    padding: 16px !important;
}

.fc-list-day-text,
.fc-list-day-side-text {
    color: var(--text-color) !important;
    font-weight: 600 !important;
}

/* Remove old dot styling */
.event-dots-container,
.event-dot {
    display: none !important;
}
</style>