<?php
include("sidebar.php");
$email = $_SESSION['email'];

// Fetch top performers, tasks, and upcoming events
$topPerformers = getTopPerformers(); // Define this function in your functions.php
$tasks = getTasks($email); // Define this function in your functions.php
$upcomingEvents = getUpcomingEvents(); // Define this function in your functions.php
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
?>