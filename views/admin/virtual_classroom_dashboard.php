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
    $action = $_POST['action'] ?? null;
    $topic = $_POST['topic'] ?? null;
    $start_time_local = $_POST['start_time'] ?? null;
    $duration = $_POST['duration'] ?? null;
    $selectedCourses = $_POST['courses'] ?? [];
    $join_url = $_POST['join_url'] ?? null;
    $classroom_id = $_POST['classroom_id'] ?? null;

    // Convert local time to UTC and then to ISO 8601 format
    $start_time = new DateTime($start_time_local, new DateTimeZone('Asia/Kolkata')); // Set the local time zone
    $start_time_utc = clone $start_time;
    $start_time_utc->setTimezone(new DateTimeZone('UTC')); // Convert to UTC
    $start_time_iso8601 = $start_time_utc->format(DateTime::ATOM);

    if ($action === 'create') {
        $classroom = $zoom->createVirtualClassroom($topic, $start_time_iso8601, $duration);
        $join_url = $classroom['join_url'];
    } elseif ($action === 'save') {
        $classroom = [
            'id' => $classroom_id,
            'topic' => $topic,
            'start_time' => $start_time->format('Y-m-d H:i:s'),
            'duration' => $duration,
            'join_url' => $join_url
        ];
        $classroom['id'] = $zoom->saveVirtualClassroom($topic, $start_time->format('Y-m-d H:i:s'), $duration, $join_url, $selectedCourses);
    }

    if ($join_url) {
        // Save the start time and course IDs with the classroom in the correct format
        $stmt = $conn->prepare("INSERT INTO virtual_classrooms (classroom_id, topic, start_time, duration, join_url, course_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$classroom['id'] ?? null, $topic, $start_time->format('Y-m-d H:i:s'), $duration, $join_url, json_encode($selectedCourses)]);

        // Update the courses with the virtual class ID
        foreach ($selectedCourses as $courseId) {
            $course = Course::getById($conn, $courseId);
            $virtualClassIds = !empty($course['virtual_class_id']) ? json_decode($course['virtual_class_id'], true) : [];
            $virtualClassIds[] = $classroom['id'] ?? null;
            $stmt = $conn->prepare("UPDATE courses SET virtual_class_id = ? WHERE id = ?");
            $stmt->execute([json_encode($virtualClassIds), $courseId]);
        }

        // Redirect to the admin virtual classroom dashboard
        header('Location: /admin/virtual_classroom');
        exit();
    } else {
        echo "Error creating or saving virtual classroom.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Virtual Classroom Dashboard</title>
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
                                <h2 class="font-weight-bold mb-4">Admin Dashboard</h2>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Create or Save Virtual Classroom</h4>
                                    <div class="form-group">
                                        <label for="action">Action</label>
                                        <select class="form-control" id="action" name="action" onchange="toggleAction(this.value)">
                                            <option value="create">Create</option>
                                            <option value="save">Save</option>
                                        </select>
                                    </div>
                                    <form method="POST" action="/admin/virtual_classroom_dashboard">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="topic" placeholder="Classroom Topic" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="datetime-local" class="form-control" name="start_time" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="number" class="form-control" name="duration" placeholder="Duration (minutes)" required>
                                        </div>
                                        <div class="form-group" id="joinUrlGroup" style="display: none;">
                                            <input type="url" class="form-control" name="join_url" placeholder="Join URL">
                                        </div>
                                        <div class="form-group" id="classroomIdGroup" style="display: none;">
                                            <input type="text" class="form-control" name="classroom_id" placeholder="Zoom Classroom ID">
                                        </div>
                                        <div class="form-group">
                                            <label for="courses">Select Courses</label>
                                            <div class="table-responsive">
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
                                        </div>
                                        <button type="submit" class="btn btn-success">Submit</button>
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
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th class="col-topic">Topic</th>
                                                    <th class="col-start-date">Start Date</th>
                                                    <th class="col-start-time">Start Time</th>
                                                    <th class="col-end-time">End Time</th>
                                                    <th class="col-join-url">Join URL</th>
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
                    </div>

                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Past Classes</h4>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th class="col-topic">Topic</th>
                                                    <th class="col-start-date">Start Date</th>
                                                    <th class="col-start-time">Start Time</th>
                                                    <th class="col-end-time">End Time</th>
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

                </div>
                <?php include('footer.html'); ?>
            </div>
        </div>
    </div>
</body>
</html>