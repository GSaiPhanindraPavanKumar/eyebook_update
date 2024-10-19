<?php
// Database connection and include sidebar
include "sidebar.php";
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id == 0) {
    die("Invalid course ID.");
}

// Database connection

// Fetch course details
$sql = "SELECT * FROM courses WHERE id = $course_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $course = $result->fetch_assoc();
} else {
    die("Course not found.");
}

// Close the DB connection
$conn->close();

// Decode JSON data
$course_data = json_decode($course['content'], true);

// Base URL for the uploads directory
$base_url = "";
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="font-weight-bold mb-4">Course Management: <?php echo htmlspecialchars($course_data[0]['unitTitle']); ?></h2>
                    <span class="badge bg-primary text-white">Course ID: <?php echo $course_id; ?></span>
                </div>
            </div>
        </div>

        <!-- Overview and Course Control Panel -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Course Overview</h4>
                        <table class="table table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Course Attribute</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter = 1;
                                foreach ($course_data as $unit) {
                                    echo "<tr>";
                                    echo "<td>" . $counter++ . "</td>";
                                    echo "<td>Unit Title</td>";
                                    echo "<td>" . htmlspecialchars($unit['unitTitle']) . "</td>";
                                    echo "</tr>";
                                    foreach ($unit['materials'] as $material) {
                                        echo "<tr>";
                                        echo "<td>" . $counter++ . "</td>";
                                        echo "<td>SCORM Directory</td>";
                                        echo "<td>" . htmlspecialchars($material['scormDir']) . "</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>" . $counter++ . "</td>";
                                        echo "<td>Index Path</td>";
                                        $full_url = $base_url . htmlspecialchars($material['indexPath']);
                                        echo "<td><iframe src='" . $full_url . "' width='100%' height='600px' frameborder='0' sandbox='allow-scripts allow-same-origin'></iframe></td>";
                                        
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>

                        <h4 class="card-title mt-4">Course Control Panel</h4>
                        <div class="list-group">
                            <!-- View Students' Progress -->
                            <a href="view_students_progress.php?course_id=<?php echo $course_id; ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-chart-line"></i> View Students' Progress
                            </a>
                            <!-- View and Manage Assessments -->
                            <a href="view_assessments.php?course_id=<?php echo $course_id; ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-file-alt"></i> View Assessments
                            </a>
                            <!-- View and Manage Assignments -->
                            <a href="view_assignments.php?course_id=<?php echo $course_id; ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-tasks"></i> View Assignments
                            </a>
                            <!-- View Virtual Meetings -->
                            <a href="view_virtual_meetings.php?course_id=<?php echo $course_id; ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-video"></i> View Virtual Meetings
                            </a>
                            <!-- View Attendance for Virtual Meetings -->
                            <a href="view_attendance.php?course_id=<?php echo $course_id; ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-check-circle"></i> View Attendance
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include 'footer.html'; ?>
</div>