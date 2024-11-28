<?php
include "sidebar.php";
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../models/zoom_integration.php';

use Models\Database;

$conn = Database::getConnection();

// Initialize ZoomAPI
$zoom = new ZoomAPI(ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET, ZOOM_ACCOUNT_ID, $conn);
$allClassrooms = $zoom->getAllClassrooms();

// Fetch student ID from session
$studentId = $_SESSION['student_id'];

// Fetch attendance data for the student from virtual_classrooms table
$stmt = $conn->prepare("SELECT classroom_id, attendance FROM virtual_classrooms");
$stmt->execute();
$classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create an associative array for quick lookup
$attendanceStatus = [];
$totalClasses = 0;
$attendedClasses = 0;

foreach ($classrooms as $classroom) {
    if (!empty($classroom['attendance'])) {
        $attendance = json_decode($classroom['attendance'], true);
        if (isset($attendance[$studentId])) {
            $attendanceStatus[$classroom['classroom_id']] = $attendance[$studentId];
            if ($attendance[$studentId] === 'present') {
                $attendedClasses++;
            }
        } else {
            $attendanceStatus[$classroom['classroom_id']] = 'Absent';
        }
        $totalClasses++;
    }
}

// Calculate attendance percentage
$attendancePercentage = $totalClasses > 0 ? ($attendedClasses / $totalClasses) * 100 : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .attendance-present {
            color: green;
            font-weight: bold;
        }
        .attendance-absent {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-md-12 grid-margin">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="font-weight-bold mb-4">Student Virtual Classroom</h2>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="card-title">Attendance Percentage</h4>
                                        <p class="card-text mb-0"><?php echo round($attendancePercentage, 2); ?>%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Today's and Upcoming Classes</h4>
                                    <table class="table table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Topic</th>
                                                <th>Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Join URL</th>
                                                <th>Attendance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $current_time = new DateTime('now', new DateTimeZone('UTC'));
                                            foreach ($allClassrooms as $classroom):
                                                $start_time = new DateTime($classroom['start_time'], new DateTimeZone('UTC'));
                                                $end_time = clone $start_time;
                                                $end_time->modify('+' . $classroom['duration'] . ' minutes');
                                                if ($current_time <= $end_time):
                                                    $attendance = $attendanceStatus[$classroom['classroom_id']] ?? null;
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($classroom['topic']); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('Y-m-d')); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('H:i:s')); ?></td>
                                                    <td><?php echo htmlspecialchars($end_time->format('H:i:s')); ?></td>
                                                    <td><a href="<?php echo htmlspecialchars($classroom['join_url']); ?>" target="_blank" class="btn btn-primary">Join</a></td>
                                                    <td>
                                                        <?php if ($attendance === 'present'): ?>
                                                            <span class="attendance-present">Present</span>
                                                        <?php elseif ($attendance === 'absent'): ?>
                                                            <span class="attendance-absent">Absent</span>
                                                        <?php else: ?>
                                                            <span>Not Uploaded</span>
                                                        <?php endif; ?>
                                                    </td>
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
                                    <h4 class="card-title">Past Classes</h4>
                                    <table class="table table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Topic</th>
                                                <th>Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Attendance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($allClassrooms as $classroom):
                                                $start_time = new DateTime($classroom['start_time'], new DateTimeZone('UTC'));
                                                $end_time = clone $start_time;
                                                $end_time->modify('+' . $classroom['duration'] . ' minutes');
                                                if ($current_time > $end_time):
                                                    $attendance = $attendanceStatus[$classroom['classroom_id']] ?? null;
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($classroom['topic']); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('Y-m-d')); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('H:i:s')); ?></td>
                                                    <td><?php echo htmlspecialchars($end_time->format('H:i:s')); ?></td>
                                                    <td>
                                                        <?php if ($attendance === 'present'): ?>
                                                            <span class="attendance-present">Present</span>
                                                        <?php elseif ($attendance === 'absent'): ?>
                                                            <span class="attendance-absent">Absent</span>
                                                        <?php else: ?>
                                                            <span>Not Uploaded</span>
                                                        <?php endif; ?>
                                                    </td>
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

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>