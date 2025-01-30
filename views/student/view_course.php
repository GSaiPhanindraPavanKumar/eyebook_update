<?php include "sidebar.php";
use Models\Course;
use models\Assignment;
use Models\feedback;

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
        $ids = !empty($row['virtual_class_id']) ? json_decode($row['virtual_class_id'], true) : [];
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
foreach ($allClassrooms as $classroom) {
    if (!empty($classroom['attendance'])) {
        $attendance = json_decode($classroom['attendance'], true);
        if (isset($attendance[$studentId])) {
            $attendanceStatus[$classroom['id']] = $attendance[$studentId];
        } else {
            $attendanceStatus[$classroom['id']] = 'Not Uploaded';
        }
    } else {
        $attendanceStatus[$classroom['id']] = 'Not Uploaded';
    }
}
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
                                        echo "<td><button onclick=\"viewAndMarkAsCompleted('" . htmlspecialchars($full_url) . "', this)\" class='btn btn-primary'>View Course Book</button></td>";
                                        echo "<td class='status-cell'>" . ($isCompleted ? "Completed" : "Not Completed") . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No course books available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <h5 class="mt-5">EC Content</h5>
                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">S. No.</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $ECContent = !empty($course['EC_content']) ? json_decode($course['EC_content'], true) : [];
                                if (!empty($ECContent)) {
                                    $serialNumber = 1;
                                    foreach ($ECContent as $content) {
                                        echo "<tr>";
                                        echo "<td>" . $serialNumber++ . "</td>";
                                        echo "<td>" . htmlspecialchars($content['unitTitle'] ?? 'N/A') . "</td>";
                                        $full_url = $content['indexPath'] ?? '#';
                                        echo "<td><a href='/student/view_ec_content/" . $hashedId . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View EC</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No EC content available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <h5 class="mt-5">Additional Content</h5>
                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">S. No.</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $additionalContent = !empty($course['additional_content']) ? json_decode($course['additional_content'], true) : [];
                                if (!empty($additionalContent)) {
                                    $serialNumber = 1;
                                    foreach ($additionalContent as $content) {
                                        echo "<tr>";
                                        echo "<td>" . $serialNumber++ . "</td>";
                                        echo "<td>" . htmlspecialchars($content['title'] ?? 'N/A') . "</td>";
                                        echo "<td><a href='" . htmlspecialchars($content['link'] ?? '#') . "' target='_blank' class='btn btn-primary'>View Content</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No additional content available.</td></tr>";
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
                        <?php if (!empty($allClassrooms)): ?>
                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Topic</th>
                                    <th scope="col">Start Date</th>
                                    <th scope="col">Start Time</th>
                                    <th scope="col">End Time</th>
                                    <th scope="col">Join URL</th>
                                    <th scope="col">Attendance Status</th>
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
                                        $attendance = $attendanceStatus[$classroom['id']] ?? 'Not Uploaded';
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
                                                <span><?php echo htmlspecialchars($attendance); ?></span>
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
                        <?php if (!empty($allClassrooms)): ?>
                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Topic</th>
                                    <th scope="col">Start Date</th>
                                    <th scope="col">Start Time</th>
                                    <th scope="col">End Time</th>
                                    <th scope="col">Attendance Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($allClassrooms as $classroom):
                                    $start_time = new DateTime($classroom['start_time'], new DateTimeZone('UTC'));
                                    $end_time = clone $start_time;
                                    $end_time->modify('+' . $classroom['duration'] . ' minutes');
                                    if ($current_time > $end_time):
                                        $attendance = $attendanceStatus[$classroom['id']] ?? 'Not Uploaded';
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
                                                <span><?php echo htmlspecialchars($attendance); ?></span>
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
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5 class="d-flex justify-content-between align-items-center">
                                Assignments
                            </h5>
                        </div>
                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Title</th>
                                    <th scope="col">Start Time</th>
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
                                        echo "<td class='start-time'>" . htmlspecialchars($assignment['start_time']) . "</td>";
                                        echo "<td>" . htmlspecialchars($assignment['due_date']) . "</td>";
                                        echo "<td>" . ($isSubmitted ? 'Submitted' : 'Not Submitted') . "</td>";
                                        echo "<td>" . htmlspecialchars($grade) . "</td>";
                                        echo "<td><a href='/student/view_assignment/" . $assignment['id'] . "' class='btn btn-primary view-assignment-button'>View</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>No assignments available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Feedback Section for Archived Courses -->
                        <?php if ($course['feedback_enabled'] == 1): ?>
                            <div class="mt-4">
                                <h5>Feedback</h5>
                                <?php if (isset($_SESSION['feedback_submitted']) && $_SESSION['feedback_submitted']): ?>
                                    <p>Thank you for providing the feedback. Your feedback has been recorded.</p>
                                    <?php unset($_SESSION['feedback_submitted']); ?>
                                <?php elseif (feedback::hasFeedback($conn, $course['id'], $student['id'])): ?>
                                    <p>Thank you for providing the feedback. Your feedback is already recorded.</p>
                                <?php else: ?>
                                    <form method="post" action="/student/submit_feedback">
                                        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <div class="form-group">
                                            <label for="depth_of_coverage">Depth of Coverage</label>
                                            <select class="form-control" id="depth_of_coverage" name="depth_of_coverage">
                                                <option>Basic Level</option>
                                                <option>Intermediate Level</option>
                                                <option>Advance Level</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="emphasis_on_fundamentals">Emphasis on fundamentals</label>
                                            <select class="form-control" id="emphasis_on_fundamentals" name="emphasis_on_fundamentals">
                                                <option>Poor</option>
                                                <option>Satisfactory</option>
                                                <option>Good</option>
                                                <option>Very Good</option>
                                                <option>Excellent</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="coverage_of_modern_topics">Coverage of modern/advanced topics</label>
                                            <select class="form-control" id="coverage_of_modern_topics" name="coverage_of_modern_topics">
                                                <option>Poor</option>
                                                <option>Satisfactory</option>
                                                <option>Good</option>
                                                <option>Very Good</option>
                                                <option>Excellent</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="overall_rating">Overall Rating of the Course</label>
                                            <select class="form-control" id="overall_rating" name="overall_rating">
                                                <option>Poor</option>
                                                <option>Satisfactory</option>
                                                <option>Good</option>
                                                <option>Very Good</option>
                                                <option>Excellent</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="benefits">What benefits you derived from the course? What areas would you like to see improved?</label>
                                            <textarea class="form-control" id="benefits" name="benefits" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>The instructor demonstrated willingness to assist students with course content/Lab exercises when needed.</label>
                                            <select class="form-control" name="instructor_assistance">
                                                <option>Strongly Agree</option>
                                                <option>Agree</option>
                                                <option>Neutral</option>
                                                <option>Disagree</option>
                                                <option>Strongly disagree</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>The instructor provided adequate and timely feedback when I had a question.</label>
                                            <select class="form-control" name="instructor_feedback">
                                                <option>Strongly Agree</option>
                                                <option>Agree</option>
                                                <option>Neutral</option>
                                                <option>Disagree</option>
                                                <option>Strongly disagree</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Motivated me to learn the subject.</label>
                                            <select class="form-control" name="motivation">
                                                <option>Strongly Agree</option>
                                                <option>Agree</option>
                                                <option>Neutral</option>
                                                <option>Disagree</option>
                                                <option>Strongly disagree</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>SME Helped me a lot.</label>
                                            <select class="form-control" name="sme_help">
                                                <option>Strongly Agree</option>
                                                <option>Agree</option>
                                                <option>Neutral</option>
                                                <option>Disagree</option>
                                                <option>Strongly disagree</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Overall Very Good</label>
                                            <select class="form-control" name="overall_very_good">
                                                <option>Strongly Agree</option>
                                                <option>Agree</option>
                                                <option>Neutral</option>
                                                <option>Disagree</option>
                                                <option>Strongly disagree</option>
                                            </select>
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
document.addEventListener('DOMContentLoaded', function() {
    const assignmentRows = document.querySelectorAll('table tbody tr');

    assignmentRows.forEach(row => {
        const startTime = new Date(row.querySelector('.start-time').textContent);
        const viewButton = row.querySelector('.view-assignment-button');
        const currentDate = new Date();

        if (currentDate < startTime) {
            viewButton.disabled = true;
            viewButton.textContent = 'Not Available Yet';
        }
    });
});
function viewAndMarkAsCompleted(url, button) {
    // Open the content in a new tab or iframe
    window.open('/student/view_course_book?index_path=' + encodeURIComponent(url), '_blank');

    // Mark as completed (you can replace this with an AJAX call to update the status in the database)
    button.closest('tr').querySelector('.status-cell').innerText = 'Completed';
}
</script>