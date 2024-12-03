<?php
include('sidebar.php');

// Load configuration
require_once __DIR__ . '/../../models/config.php';

// Establish database connection using the Database class
use Models\Database;

$conn = Database::getConnection();

// Fetch faculty ID from session
$facultyId = $_SESSION['faculty_id'];

// Step 1: Get the assigned course IDs from the faculty table
$sql = "SELECT assigned_courses FROM faculty WHERE id = :faculty_id";
$stmt = $conn->prepare($sql);
$stmt->execute(['faculty_id' => $facultyId]);
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

// Step 3: Fetch the virtual class details from the virtual classes table using the id column
$facultyClassrooms = [];
if (!empty($virtualClassIds)) {
    $placeholders = implode(',', array_fill(0, count($virtualClassIds), '?'));
    $sql = "SELECT * FROM virtual_classrooms WHERE id IN ($placeholders) ORDER BY start_time DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($virtualClassIds);
    $facultyClassrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch course names for each virtual classroom
foreach ($facultyClassrooms as &$classroom) {
    $courseIds = json_decode($classroom['course_id'], true);
    $courseNames = [];
    foreach ($courseIds as $courseId) {
        $sql = "SELECT name FROM courses WHERE id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['course_id' => $courseId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($course) {
            $courseNames[] = $course['name'];
        }
    }
    $classroom['course_names'] = implode(', ', $courseNames);

    // Check if attendance has been recorded for the class
    $sql = "SELECT attendance FROM virtual_classrooms WHERE id = :classroom_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['classroom_id' => $classroom['id']]);
    $attendance = $stmt->fetchColumn();
    $classroom['attendance_taken'] = !empty($attendance);
}

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
    <style>
        .table th, .table td {
            width: 150px;
            word-wrap: break-word;
            white-space: normal;
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
                                <h2 class="font-weight-bold mb-4">Faculty Dashboard</h2>
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
                                                <th>Course Name</th>
                                                <th>Start Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Join URL</th>
                                                <th>Actions</th>
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
                                                    <td><?php echo htmlspecialchars($classroom['course_names']); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('Y-m-d')); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('H:i:s')); ?></td>
                                                    <td><?php echo htmlspecialchars($end_time->format('H:i:s')); ?></td>
                                                    <td><a href="<?php echo htmlspecialchars($classroom['join_url']); ?>" target="_blank" class="btn btn-primary">Join</a></td>
                                                    <td>
                                                        <?php if ($classroom['attendance_taken']): ?>
                                                            <a href="/faculty/download_attendance?classroom_id=<?php echo htmlspecialchars($classroom['id']); ?>" class="btn btn-primary">Download</a>
                                                        <?php else: ?>
                                                            <a href="/faculty/take_attendance?classroom_id=<?php echo htmlspecialchars($classroom['id']); ?>" class="btn btn-warning">Take Attendance</a>
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
                                                <th>Course Name</th>
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
                                                    <td><?php echo htmlspecialchars($classroom['course_names']); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('Y-m-d')); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('H:i:s')); ?></td>
                                                    <td><?php echo htmlspecialchars($end_time->format('H:i:s')); ?></td>
                                                    <td>
                                                        <?php if ($classroom['attendance_taken']): ?>
                                                            <a href="/faculty/download_attendance?classroom_id=<?php echo htmlspecialchars($classroom['id']); ?>" class="btn btn-primary">Download</a>
                                                        <?php else: ?>
                                                            <a href="/faculty/take_attendance?classroom_id=<?php echo htmlspecialchars($classroom['id']); ?>" class="btn btn-warning">Take Attendance</a>
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
</body>
</html>