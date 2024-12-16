<?php include "sidebar.php";
use Models\Course;
use models\Assignment;
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="font-weight-bold mb-4">Course Management: <?php echo htmlspecialchars($course['name'] ?? ''); ?></h2>
                    <span class="badge bg-primary text-white">Course ID: <?php echo htmlspecialchars($course['id'] ?? ''); ?></span>
                </div>
            </div>
        </div>

        <!-- Overview and Course Control Panel -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Course Overview</h4>
                        
                        <!-- Course Plan Section -->
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Course Plan</h5>
                            <?php if (!empty($course['course_plan'])) : ?>
                                <?php
                                $hashedId = base64_encode($course['id']);
                                $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                                ?>
                                <a href="/student/view_course_plan/<?php echo $hashedId; ?>" class="btn btn-primary">View</a>
                            <?php endif; ?>
                        </div>

                        <!-- Course Book Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5>Course Book</h5>
                            <?php if (!empty($course['course_book'])) : ?>
                                <?php
                                $hashedId = base64_encode($course['id']);
                                $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                                ?>
                            <?php endif; ?>
                        </div>

                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">S. No.</th>
                                    <th scope="col">Unit Title</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $studentCompletedBooks = json_decode($student['completed_books'] ?? '[]', true)[$course['id']] ?? [];
                                if (!empty($course['course_book'])) {
                                    $serialNumber = 1; 
                                    foreach ($course['course_book'] as $unit) {
                                        $isCompleted = in_array($unit['scorm_url'], $studentCompletedBooks);
                                        echo "<tr>";
                                        echo "<td>" . $serialNumber++ . "</td>"; // Increment the serial number
                                        echo "<td>" . htmlspecialchars($unit['unit_name']) . "</td>";
                                        $full_url = $unit['scorm_url'];
                                        echo "<td><a href='/student/view_book/" . $hashedId . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View Course Book</a></td>";
                                        echo "<td class='status-cell'>" . ($isCompleted ? "Completed" : "Not Completed") . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No course books available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        
                        <!-- Course Materials Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5>Course Materials</h5>
                        </div>
                        <table class="table table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Unit Number</th>
                                    <th scope="col">Topic</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($course['course_materials'])) {
                                    foreach ($course['course_materials'] as $unit) {
                                        if (isset($unit['materials'])) {
                                            foreach ($unit['materials'] as $material) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($unit['unitNumber']) . "</td>";
                                                echo "<td>" . htmlspecialchars($unit['topic']) . "</td>";
                                                $full_url = $material['indexPath'];
                                                echo "<td><a href='/student/view_material/" . $hashedId . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View Material</a></td>";
                                                echo "</tr>";
                                            }
                                        }
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No course materials available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Virtual Classroom Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5 class="d-flex justify-content-between align-items-center">
                                Virtual Classrooms
                            </h5>
                        </div>
                        <h6 class="d-flex justify-content-between align-items-center">Today's and Upcoming Classes</h6>
                        <?php if (!empty($virtualClassrooms)): ?>
                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Topic</th>
                                    <th scope="col">Start Date</th>
                                    <th scope="col">Start Time</th>
                                    <th scope="col">End Time</th>
                                    <th scope="col">Join URL</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $current_time = new DateTime('now', new DateTimeZone('UTC'));
                                foreach ($virtualClassrooms as $classroom):
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
                                        <td>
                                            <?php if (isset($classroom['attendance_taken']) && $classroom['attendance_taken']): ?>
                                                <a href="/faculty/download_attendance?classroom_id=<?php echo htmlspecialchars($classroom['id']); ?>" class="btn btn-primary">Download</a>
                                            <?php else: ?>
                                                <a href="/faculty/take_attendance?classroom_id=<?php echo htmlspecialchars($classroom['id']); ?>" class="btn btn-warning">Take Attendance</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <p>No available classes.</p>
                        <?php endif; ?>

                        <h6 class="d-flex justify-content-between align-items-center mt-4">Past Classes</h6>
                        <?php if (!empty($virtualClassrooms)): ?>
                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Topic</th>
                                    <th scope="col">Start Date</th>
                                    <th scope="col">Start Time</th>
                                    <th scope="col">End Time</th>
                                    <th scope="col">Attendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($virtualClassrooms as $classroom):
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
                                        <td>
                                            <?php if (isset($classroom['attendance_taken']) && $classroom['attendance_taken']): ?>
                                                <a href="/faculty/download_attendance?classroom_id=<?php echo htmlspecialchars($classroom['id']); ?>" class="btn btn-primary">Download</a>
                                            <?php else: ?>
                                                <a href="/faculty/take_attendance?classroom_id=<?php echo htmlspecialchars($classroom['id']); ?>" class="btn btn-warning">Take Attendance</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <p>No available classes.</p>
                        <?php endif; ?>

                        <!-- Assignments Section -->
                        <!-- Assignments Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5 class="d-flex justify-content-between align-items-center">
                                Assignments
                            </h5>
                        </div>
                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Title</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Due Date</th>
                                    <th>Submitted</th>
                                    <th>Grade</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($assignments)) {
                                    foreach ($assignments as $assignment) {
                                        $isSubmitted = Assignment::isSubmitted($conn, $assignment['id'], $student['id']);
                                        $grade = 'Not Graded';
                                        $submissions = Assignment::getSubmissions($conn, $assignment['id']);
                                        foreach ($submissions as $submission) {
                                            if ($submission['student_id'] == $student['id']) {
                                                $grade = $submission['grade'] ?? 'Not Graded';
                                                break;
                                            }
                                        }
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($assignment['title']) . "</td>";
                                        echo "<td>" . htmlspecialchars($assignment['description']) . "</td>";
                                        echo "<td>" . htmlspecialchars($assignment['due_date']) . "</td>";
                                        echo "<td>" . ($isSubmitted ? 'Submitted' : 'Not Submitted') . "</td>";
                                        echo "<td>" . htmlspecialchars($grade) . "</td>";
                                        echo "<td><a href='/student/view_assignment/" . $assignment['id'] . "' class='btn btn-primary'>View</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>No assignments available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Feedback Section for Archived Courses -->
                        <?php if ($course['status'] === 'archived'): ?>
                            <div class="mt-4">
                                <h5>Feedback</h5>
                                <?php if (Course::hasFeedback($conn, $course['id'], $student['id'])): ?>
                                    <p>Thank you for providing the feedback. Your Feedback is already recorded.</p>
                                <?php else: ?>
                                    <form method="post" action="/student/submit_feedback">
                                        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <div class="form-group">
                                            <label for="feedback">Your Feedback</label>
                                            <textarea class="form-control" id="feedback" name="feedback" rows="4" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-2">Submit Feedback</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include 'footer.html'; ?>
</div>

<script>
function viewAndMarkAsCompleted(indexPath, button) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/student/mark_as_completed", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            window.open(indexPath, '_blank');
            var statusCell = button.closest('tr').querySelector('.status-cell');
            statusCell.innerHTML = 'Completed';
        }
    };
    xhr.send("indexPath=" + encodeURIComponent(indexPath) + "&course_id=<?php echo $course['id']; ?>");
}
</script>