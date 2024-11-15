<?php
// Include sidebar
include('sidebar.php');

// Load configuration
require_once 'config.php';

// Establish database connection using the Database class
require_once __DIR__ . '/../../models/database.php';
use Models\Database;

$conn = Database::getConnection();

// Include Zoom integration
require_once 'zoom_integration.php';

// Initialize ZoomAPI
$zoom = new ZoomAPI(ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET, ZOOM_ACCOUNT_ID, $conn);

// Fetch all classrooms
$facultyClassrooms = $zoom->getAllClassrooms();

// Sort classrooms by start_time in descending order
usort($facultyClassrooms, function($a, $b) {
    return strtotime($b['start_time']) - strtotime($a['start_time']);
});
?>

<!DOCTYPE html>
<html>
<head>
    <title>Virtual Classroom Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-md-12 grid-margin">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="font-weight-bold mb-4">Faculty Dashboard</h2>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Create Virtual Classroom</h4>
                                    <form method="POST" action="/faculty/create_virtual_classroom">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="topic" placeholder="Classroom Topic" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="datetime-local" class="form-control" name="start_time" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="number" class="form-control" name="duration" placeholder="Duration (minutes)" required>
                                        </div>
                                        <button type="submit" class="btn btn-success">Create Virtual Classroom</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Ongoing and Upcoming Classes</h4>
                                    <table class="table table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Topic</th>
                                                <th>Start Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Join URL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $current_time = new DateTime('now', new DateTimeZone('UTC'));
                                            foreach ($facultyClassrooms as $classroom):
                                                $start_time = new DateTime($classroom['start_time'], new DateTimeZone('UTC'));
                                                $end_time = clone $start_time;
                                                $end_time->modify('+' . $classroom['duration'] . ' minutes');
                                                if ($current_time <= $end_time):
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($classroom['topic']); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('Y-m-d')); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('H:i:s')); ?></td>
                                                    <td><?php echo htmlspecialchars($end_time->format('H:i:s')); ?></td>
                                                    <td><a href="<?php echo htmlspecialchars($classroom['join_url']); ?>" target="_blank" class="btn btn-primary">Join</a></td>
                                                </tr>
                                            <?php endif; endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Completed Classes</h4>
                                    <table class="table table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Topic</th>
                                                <th>Start Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Attendance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($facultyClassrooms as $classroom):
                                                $start_time = new DateTime($classroom['start_time'], new DateTimeZone('UTC'));
                                                $end_time = clone $start_time;
                                                $end_time->modify('+' . $classroom['duration'] . ' minutes');
                                                if ($current_time > $end_time):
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($classroom['topic']); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('Y-m-d')); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('H:i:s')); ?></td>
                                                    <td><?php echo htmlspecialchars($end_time->format('H:i:s')); ?></td>
                                                    <td><a href="/faculty/download_attendance?classroom_id=<?php echo htmlspecialchars($classroom['classroom_id']); ?>" class="btn btn-primary">Download</a></td>
                                                </tr>
                                            <?php endif; endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <?php include('footer.html'); ?>
            </div>
        </div>
    </div>
</body>
</html>