<?php
// Include sidebar
include('sidebar.php');

// Load configuration
require_once __DIR__ . '/../../models/config.php';

// Establish database connection using the Database class
use Models\Database;
use Models\Course;

$conn = Database::getConnection();

// Include Zoom integration
require_once __DIR__ . '/../../models/zoom_integration.php';

// Initialize ZoomAPI
$zoom = new ZoomAPI(ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET, ZOOM_ACCOUNT_ID, $conn);

// Fetch all classrooms
$adminClassrooms = $zoom->getAllClassrooms();

// Fetch all courses with university short name
$courses = Course::getAllWithUniversity($conn);

// Sort classrooms by start_time in descending order
usort($adminClassrooms, function($a, $b) {
    return strtotime($b['start_time']) - strtotime($a['start_time']);
});

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $topic = $_POST['topic'];
    $start_time_local = $_POST['start_time'];
    $duration = $_POST['duration'];
    $selectedCourses = $_POST['courses'];

    // Convert local time to UTC and then to ISO 8601 format
    $start_time = new DateTime($start_time_local, new DateTimeZone('Asia/Kolkata')); // Set the local time zone
    $start_time_utc = clone $start_time;
    $start_time_utc->setTimezone(new DateTimeZone('UTC')); // Convert to UTC
    $start_time_iso8601 = $start_time_utc->format(DateTime::ATOM);

    $classroom = $zoom->createVirtualClassroom($topic, $start_time_iso8601, $duration);

    if (isset($classroom['id'])) {
        // Save the start time and course IDs with the classroom in the correct format
        $stmt = $conn->prepare("INSERT INTO virtual_classrooms (classroom_id, topic, start_time, duration, join_url, course_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$classroom['id'], $topic, $start_time->format('Y-m-d H:i:s'), $duration, $classroom['join_url'], json_encode($selectedCourses)]);

        // Update the courses with the virtual class ID
        foreach ($selectedCourses as $courseId) {
            $course = Course::getById($conn, $courseId);
            $virtualClassIds = !empty($course['virtual_class_id']) ? json_decode($course['virtual_class_id'], true) : [];
            $virtualClassIds[] = $classroom['id'];
            $stmt = $conn->prepare("UPDATE courses SET virtual_class_id = ? WHERE id = ?");
            $stmt->execute([json_encode($virtualClassIds), $courseId]);
        }

        // Redirect to the admin virtual classroom dashboard
        header('Location: /admin/virtual_classroom');
        exit();
    } else {
        echo "Error creating virtual classroom.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Virtual Classroom Dashboard</title>
    
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-md-12 grid-margin">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="font-weight-bold mb-4">Admin Dashboard</h2>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Create Virtual Classroom</h4>
                                    <form method="POST" action="/admin/create_virtual_classroom">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="topic" placeholder="Classroom Topic" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="datetime-local" class="form-control" name="start_time" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="number" class="form-control" name="duration" placeholder="Duration (minutes)" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="courses">Select Courses</label>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Select</th>
                                                        <th>Course Name</th>
                                                        <th>University Short Name</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($courses as $course): ?>
                                                        <tr>
                                                            <td><input type="checkbox" name="courses[]" value="<?php echo $course['id']; ?>"></td>
                                                            <td><?php echo htmlspecialchars($course['name'] ?? 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($course['university'] ?? 'N/A'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
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
                                    <h4 class="card-title">Today's and Upcoming Classes</h4>
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
                                            foreach ($adminClassrooms as $classroom):
                                                $start_time = new DateTime($classroom['start_time'], new DateTimeZone('UTC'));
                                                $end_time = clone $start_time;
                                                $end_time->modify('+' . $classroom['duration'] . ' minutes');
                                                if ($current_time <= $end_time):
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($classroom['topic'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('Y-m-d') ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('H:i:s') ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($end_time->format('H:i:s') ?? 'N/A'); ?></td>
                                                    <td><a href="<?php echo htmlspecialchars($classroom['join_url'] ?? '#'); ?>" target="_blank" class="btn btn-primary">Join</a></td>
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
                                                <th>Start Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($adminClassrooms as $classroom):
                                                $start_time = new DateTime($classroom['start_time'], new DateTimeZone('UTC'));
                                                $end_time = clone $start_time;
                                                $end_time->modify('+' . $classroom['duration'] . ' minutes');
                                                if ($current_time > $end_time):
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($classroom['topic'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('Y-m-d') ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('H:i:s') ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($end_time->format('H:i:s') ?? 'N/A'); ?></td>
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