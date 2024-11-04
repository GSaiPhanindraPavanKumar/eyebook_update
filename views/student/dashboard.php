<?php
include("sidebar.php");
// include '../../src/functions.php';

$email = $_SESSION['email'];



// Fetch upcoming tasks, meetings, assessments, and assignment submissions
$upcomingTasks = getUpcomingTasks($email); // Define this function in your functions.php
$upcomingMeetings = getUpcomingMeetings($email); // Define this function in your functions.php
$upcomingAssessments = getUpcomingAssessments($email); // Define this function in your functions.php
$assignmentSubmissions = getAssignmentSubmissions($email); // Define this function in your functions.php
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
            <!-- Upcoming Tasks -->
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Upcoming Tasks</p><br>
                        <ul class="list-group">
                            <?php foreach ($upcomingTasks as $task): ?>
                                <li class="list-group-item">
                                    <?php echo htmlspecialchars($task['description']); ?>
                                    <span class="badge badge-primary"><?php echo htmlspecialchars($task['due_date']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Upcoming Meetings -->
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Upcoming Meetings</p><br>
                        <ul class="list-group">
                            <?php foreach ($upcomingMeetings as $meeting): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($meeting['title']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($meeting['date']); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Upcoming Assessments -->
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Upcoming Assessments</p><br>
                        <ul class="list-group">
                            <?php foreach ($upcomingAssessments as $assessment): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($assessment['title']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($assessment['due_date']); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Assignment Submissions -->
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Assignment Submissions</p><br>
                        <ul class="list-group">
                            <?php foreach ($assignmentSubmissions as $submission): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($submission['title']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($submission['due_date']); ?></small>
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

function getUserDataByEmail($email) {
    // Example data, replace with actual database query
    return [
        'name' => 'John Doe',
        'email' => $email,
    ];
}

function getUpcomingTasks($email) {
    // Example data, replace with actual database query
    return [
        ['description' => 'Complete assignment', 'due_date' => '2024-10-15'],
        ['description' => 'Prepare presentation', 'due_date' => '2024-10-20'],
    ];
}

function getUpcomingMeetings($email) {
    // Example data, replace with actual database query
    return [
        ['title' => 'Team Meeting', 'date' => '2024-10-18'],
        ['title' => 'Project Kickoff', 'date' => '2024-10-25'],
    ];
}

function getUpcomingAssessments($email) {
    // Example data, replace with actual database query
    return [
        ['title' => 'Math Assessment', 'due_date' => '2024-10-22'],
        ['title' => 'Science Quiz', 'due_date' => '2024-10-28'],
    ];
}

function getAssignmentSubmissions($email) {
    // Example data, replace with actual database query
    return [
        ['title' => 'History Assignment', 'due_date' => '2024-10-30'],
        ['title' => 'Literature Essay', 'due_date' => '2024-11-05'],
    ];
}
?>