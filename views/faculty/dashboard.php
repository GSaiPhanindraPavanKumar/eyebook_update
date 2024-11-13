<?php
include("sidebar.php");
$email = $_SESSION['email'];

// Fetch top performers, tasks, upcoming events, and pending meetings
$topPerformers = getTopPerformers(); // Define this function in your functions.php
$tasks = getTasks($email); // Define this function in your functions.php
$upcomingEvents = getUpcomingEvents(); // Define this function in your functions.php
$pendingMeetings = getPendingMeetings($email); // Define this function in your functions.php
?>

<!-- HTML Content -->
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
            <!-- Top Performers -->
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Top Performers</p><br>
                        <div class="table-responsive">
                            <table class="table table-striped table-borderless">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topPerformers as $index => $performer): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($performer['name']); ?></td>
                                            <td><?php echo htmlspecialchars($performer['score']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks -->
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Tasks</p><br>
                        <ul class="list-group">
                            <?php foreach ($tasks as $task): ?>
                                <li class="list-group-item">
                                    <?php echo htmlspecialchars($task['description']); ?>
                                    <span class="badge badge-primary"><?php echo htmlspecialchars($task['status']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Upcoming Events</p><br>
                        <ul class="list-group">
                            <?php foreach ($upcomingEvents as $event): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($event['date']); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Pending Meetings -->
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Pending Meetings</p><br>
                        <ul class="list-group">
                            <?php foreach ($pendingMeetings as $meeting): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($meeting['title']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($meeting['date']); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Graphs -->
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Performance Graph</p><br>
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<?php
// Define the functions to fetch data in your functions.php or relevant file

function getTopPerformers() {
    // Example data, replace with actual database query
    return [
        ['name' => 'G Sumanth', 'score' => 95],
        ['name' => 'C Ravi Ram', 'score' => 90],
        // ['name' => 'Alice Johnson', 'score' => 85],
    ];
}

function getTasks($email) {
    // Example data, replace with actual database query
    return [
        ['description' => 'Complete assignment', 'status' => 'Pending'],
        ['description' => 'Attend meeting', 'status' => 'Completed'],
    ];
}

function getUpcomingEvents() {
    // Example data, replace with actual database query
    return [
        ['title' => 'Assessment 1', 'date' => '2024-10-15'],
        ['title' => 'Meeting', 'date' => '2023-10-20'],
    ];
}

function getPendingMeetings($email) {
    // Example data, replace with actual database query
    return [
        ['title' => 'Faculty Meeting', 'date' => '2023-11-01'],
        ['title' => 'Project Discussion', 'date' => '2023-11-05'],
    ];
}
?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('performanceChart').getContext('2d');
    var performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            datasets: [{
                label: 'Performance',
                data: [65, 59, 80, 81, 56, 55, 40],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>