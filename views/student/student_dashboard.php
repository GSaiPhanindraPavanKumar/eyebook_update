<?php
include "sidebar.php";
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../models/zoom_integration.php';

use Models\Database;
use Models\Student;
use Models\Course;
use Models\VirtualClassroom;

$conn = Database::getConnection();

// Initialize ZoomAPI
$zoom = new ZoomAPI(ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET, ZOOM_ACCOUNT_ID, $conn);

// Fetch student ID from session
$studentId = $_SESSION['student_id'];

// Step 1: Get the assigned course IDs from the students table
$sql = "SELECT assigned_courses FROM students WHERE id = :student_id";
$stmt = $conn->prepare($sql);
$stmt->execute(['student_id' => $studentId]);
$assignedCourses = $stmt->fetchColumn();
$assignedCourses = $assignedCourses ? json_decode($assignedCourses, true) : [];

if (empty($assignedCourses)) {
    $assignedCourses = [];
}

// Step 2: Get the virtual class IDs from the courses table
$virtualClassIds = [];
if (!empty($assignedCourses)) {
    $placeholders = implode(',', array_fill(0, count($assignedCourses), '?'));
    $sql = "SELECT virtual_class_id FROM courses WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($assignedCourses);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids = json_decode($row['virtual_class_id'], true);
        if (is_array($ids)) {
            $virtualClassIds = array_merge($virtualClassIds, $ids);
        }
    }
    $virtualClassIds = array_unique($virtualClassIds);
}

// Step 3: Fetch the virtual class details from the virtual classrooms table using the id column
$allClassrooms = [];
if (!empty($virtualClassIds)) {
    $placeholders = implode(',', array_fill(0, count($virtualClassIds), '?'));
    $sql = "SELECT * FROM virtual_classrooms WHERE id IN ($placeholders) ORDER BY start_time DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($virtualClassIds);
    $allClassrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch attendance data for the student from virtual_classrooms table
$attendanceStatus = [];
$totalClasses = 0;
$attendedClasses = 0;

foreach ($allClassrooms as $classroom) {
    if (!empty($classroom['attendance'])) {
        $attendance = json_decode($classroom['attendance'], true);
        if (isset($attendance[$studentId])) {
            $attendanceStatus[$classroom['id']] = $attendance[$studentId];
            if ($attendance[$studentId] === 'present') {
                $attendedClasses++;
            }
        } else {
            $attendanceStatus[$classroom['id']] = 'Absent';
        }
        $totalClasses++;
    }
}

// Calculate attendance percentage based on assigned classes
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
                                                    $attendance = $attendanceStatus[$classroom['id']] ?? null;
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
                                                    $attendance = $attendanceStatus[$classroom['id']] ?? null;
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