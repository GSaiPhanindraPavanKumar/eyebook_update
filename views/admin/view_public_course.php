<?php 
include "sidebar.php"; 
use Models\PublicCourse;
use Models\feedback;
use Models\Student;

$feedbackEnabled = $course['feedback_enabled'];



// Fetch enrolled students
$enrolledStudentIds = !empty($course['enrolled_students']) ? json_decode($course['enrolled_students'], true) : [];
$enrolledStudents = [];
$transactions = [];
if (!empty($enrolledStudentIds)) {
    foreach ($enrolledStudentIds as $studentId) {
        $student = Student::getById($conn, $studentId);
        if ($student) {
            $enrolledStudents[] = $student;
            // Fetch transaction details for the student
            $stmt = $conn->prepare("SELECT * FROM transactions WHERE student_id = ? AND course_id = ?");
            $stmt->execute([$studentId, $course['id']]);
            $studentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($studentTransactions)) {
                $transactions[$studentId] = $studentTransactions;
            }
        }
    }
}
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="font-weight-bold mb-4">Public Course Management: <?php echo htmlspecialchars($course['name'] ?? 'N/A'); ?></h2>
                    <span class="badge bg-primary text-white">Course ID: <?php echo htmlspecialchars($course['id'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="true">Details</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="uploads-tab" data-toggle="tab" href="#uploads" role="tab" aria-controls="uploads" aria-selected="false">Uploads</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="assignment-tab" data-toggle="tab" href="#assignment" role="tab" aria-controls="assignment" aria-selected="false">Assignment</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="feedback-tab" data-toggle="tab" href="#feedback" role="tab" aria-controls="feedback" aria-selected="false">Feedback</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="students-tab" data-toggle="tab" href="#students" role="tab" aria-controls="students" aria-selected="false">Enrolled Students</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <!-- Course Details Tab -->
                            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                <h4 class="card-title mt-3">Course Details</h4>
                                <?php if ($course): ?>
                                    <p><strong>Course Name:</strong> <?php echo htmlspecialchars($course['name'] ?? 'N/A'); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($course['description'] ?? 'N/A'); ?></p>
                                    <p><strong>Price:</strong> <?php echo htmlspecialchars($course['price'] ?? 'N/A'); ?></p>
                                    <p><strong>Status:</strong> <?php echo htmlspecialchars($course['status'] ?? 'N/A'); ?></p>
                                    <div class="actions mt-4">
                                        <a href="/admin/edit_public_course/<?php echo $course['id']; ?>" class="btn btn-warning">Edit Course</a>
                                        <a href="/admin/delete_public_course/<?php echo $course['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this course?');">Delete Course</a>
                                    </div>
                                <?php else: ?>
                                    <p>No course details available.</p>
                                <?php endif; ?>

                                <!-- EC Content Table -->
                                <h4 class="card-title mt-5">EC Content</h4>
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
                                        $ECContent = [];
                                        if (!empty($course['EC_content'])) {
                                            if (is_string($course['EC_content'])) {
                                                $ECContent = json_decode($course['EC_content'], true);
                                            } elseif (is_array($course['EC_content'])) {
                                                $ECContent = $course['EC_content'];
                                            }
                                        }
                                        if (!empty($ECContent)) {
                                            $serialNumber = 1;
                                            foreach ($ECContent as $content) {
                                                echo "<tr>";
                                                echo "<td>" . $serialNumber++ . "</td>";
                                                echo "<td>" . htmlspecialchars($content['unitTitle'] ?? 'N/A') . "</td>";
                                                $full_url = $content['indexPath'] ?? '#';
                                                echo "<td>
                                                        <a href='/" . htmlspecialchars($full_url) . "' target='_blank' class='btn btn-primary'>View EC</a>
                                                        <button class='btn btn-danger' onclick='removeContent(\"EC_content\", " . ($serialNumber - 2) . ")'>Remove</button>
                                                    </td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='3'>No EC content available.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>

                                <!-- Course Book Table -->
                                <h4 class="card-title mt-5">Course Book</h4>
                                <table class="table table-hover mt-2">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col">S. No.</th>
                                            <th scope="col">Unit Title</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (!empty($course['course_book'])) {
                                            $serialNumber = 1; 
                                            $hashedId = base64_encode($course['id']);
                                            $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                                            foreach ($course['course_book'] as $unit) {
                                                echo "<tr>";
                                                echo "<td>" . $serialNumber++ . "</td>";
                                                echo "<td>" . htmlspecialchars($unit['unit_name'] ?? 'N/A') . "</td>";
                                                $full_url = $unit['scorm_url'] ?? '';
                                                echo "<td>
                                                        <a href='/admin/view_book/" . urlencode($hashedId) . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View Course Book</a>
                                                        <button class='btn btn-danger' onclick='removeContent(\"course_book\", " . ($serialNumber - 2) . ")'>Remove</button>
                                                    </td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4'>No course books available.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>

                                <!-- Additional Content Table -->
                                <!-- Additional Content Table -->
                                <h4 class="card-title mt-5">Additional Content</h4>
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
                                        $additionalContent = [];
                                        if (!empty($course['additional_content'])) {
                                            if (is_string($course['additional_content'])) {
                                                $additionalContent = json_decode($course['additional_content'], true);
                                            } elseif (is_array($course['additional_content'])) {
                                                $additionalContent = $course['additional_content'];
                                            }
                                        }
                                        if (!empty($additionalContent)) {
                                            $serialNumber = 1;
                                            foreach ($additionalContent as $content) {
                                                echo "<tr>";
                                                echo "<td>" . $serialNumber++ . "</td>";
                                                echo "<td>" . htmlspecialchars($content['title'] ?? 'N/A') . "</td>";
                                                $full_url = $content['link'] ?? '#';
                                                echo "<td>
                                                        <a href='" . htmlspecialchars($full_url) . "' target='_blank' class='btn btn-primary'>View Content</a>
                                                        <button class='btn btn-danger' onclick='removeContent(\"additional_content\", " . ($serialNumber - 2) . ")'>Remove</button>
                                                    </td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='3'>No additional content available.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Uploads Tab -->
                            <div class="tab-pane fade" id="uploads" role="tabpanel" aria-labelledby="uploads-tab">
                                <h4 class="card-title mt-3">Upload Content</h4>
                                <form action="/admin/upload_ec_content" method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="ec_unit_name">EC Content Unit Name</label>
                                        <input type="text" class="form-control" id="ec_unit_name" name="ec_unit_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="ec_scorm_file">EC Content SCORM File</label>
                                        <input type="file" class="form-control" id="ec_scorm_file" name="ec_scorm_file" accept=".zip" required>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <button type="submit" class="btn btn-primary">Upload EC Content</button>
                                </form>

                                <form action="/admin/upload_course_book" method="post" enctype="multipart/form-data" class="mt-4">
                                    <div class="form-group">
                                        <label for="course_book_unit_name">Course Book Unit Name</label>
                                        <input type="text" class="form-control" id="course_book_unit_name" name="course_book_unit_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="course_book_scorm_file">Course Book SCORM File</label>
                                        <input type="file" class="form-control" id="course_book_scorm_file" name="course_book_scorm_file" accept=".zip" required>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <button type="submit" class="btn btn-primary">Upload Course Book</button>
                                </form>

                                <form action="/admin/upload_additional_content" method="post" class="mt-4">
                                    <div class="form-group">
                                        <label for="additional_content_title">Additional Content Title</label>
                                        <input type="text" class="form-control" id="additional_content_title" name="additional_content_title" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="additional_content_link">Additional Content Link</label>
                                        <input type="url" class="form-control" id="additional_content_link" name="additional_content_link" required>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <button type="submit" class="btn btn-primary">Upload Additional Content</button>
                                </form>
                            </div>

                            <!-- Assignment Tab -->
                            <div class="tab-pane fade" id="assignment" role="tabpanel" aria-labelledby="assignment-tab">
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <h5 class="d-flex justify-content-between align-items-center">
                                        Assignments
                                    </h5>
                                    <button type="button" class="btn btn-primary" onclick="toggleAssignmentForm()">Create Assignment</button>

                                </div>
                                <!-- Assignment Creation Form -->
                                <div id="assignmentForm" style="display: none; margin-top: 20px;">
                                    <h5 class="card-title">Create Assignment</h5>
                                    <form action="/admin/create_public_assignment" method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="assignment_title">Assignment Title:</label>
                                            <input type="text" class="form-control" id="assignment_title" name="assignment_title" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="assignment_description">Assignment Description:</label>
                                            <textarea class="form-control" id="assignment_description" name="assignment_description" required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="start_date">Start Date:</label>
                                            <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="due_date">Due Date:</label>
                                            <input type="datetime-local" class="form-control" id="due_date" name="due_date" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="assignment_file">Upload Assignment File:</label>
                                            <input type="file" class="form-control" id="assignment_file" name="assignment_file" accept=".pdf">
                                            <small class="form-text text-muted">Allowed file format: .pdf</small>
                                        </div>
                                        <input type="hidden" name="course_id[]" value="<?php echo $course['id']; ?>">
                                        <button type="submit" class="btn btn-primary">Create Assignment</button>
                                    </form>
                                </div>
                                <!-- Add your assignment content here -->
                                 <table class="table table-hover mt-2">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col">Title</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Start Time</th>
                                            <th scope="col">Due Date</th>
                                            <th>Submissions</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (!empty($assignments)) {
                                            foreach ($assignments as $assignment) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($assignment['title']) . "</td>";
                                                echo "<td>" . htmlspecialchars($assignment['description']) . "</td>";
                                                echo "<td>" . htmlspecialchars($assignment['start_time']) . "</td>";
                                                echo "<td>" . htmlspecialchars($assignment['due_date']) . "</td>";
                                                echo "<td>" . htmlspecialchars($assignment['submission_count']). "</td>";
                                                echo "<td><a href='/admin/view_public_assignment/" . $assignment['id'] . "' class='btn btn-primary'>View</a></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5'>No assignments available.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Feedback Tab -->
                            <div class="tab-pane fade" id="feedback" role="tabpanel" aria-labelledby="feedback-tab">
                                <h4 class="card-title mt-3">Feedback</h4>
                                <form method="post" action="/admin/toggle_public_feedback">
                                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                    <div class="form-group">
                                        <label for="feedback_enabled">Enable Feedback</label>
                                        <select class="form-control" id="feedback_enabled" name="enabled">
                                            <option value="true" <?php echo $feedbackEnabled ? 'selected' : ''; ?>>Yes</option>
                                            <option value="false" <?php echo !$feedbackEnabled ? 'selected' : ''; ?>>No</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>

                                <div class="mt-4">
                                    <h5 class="d-flex justify-content-between align-items-center">View Feedback <a href="/admin/download_feedback/<?php echo $course['id']; ?>" class="btn btn-primary">Download Feedback</a></h5>
                                    <div class="table-responsive mt-4">
                                        <table class="table table-hover table-borderless table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Depth of Coverage</th>
                                                    <th>Emphasis on Fundamentals</th>
                                                    <th>Coverage of Modern Topics</th>
                                                    <th>Overall Rating</th>
                                                    <th>Benefits</th>
                                                    <th>Instructor Assistance</th>
                                                    <th>Instructor Feedback</th>
                                                    <th>Motivation</th>
                                                    <th>SME Help</th>
                                                    <th>Overall Very Good</th>
                                                </tr>
                                            </thead>
                                            <tbody id="feedbackTable">
                                                <?php
                                                $feedbacks = PublicCourse::getFeedback($conn, $course['id']);
                                                if (empty($feedbacks)) {
                                                    echo "<tr><td colspan='11'>No feedback available.</td></tr>";
                                                } else {
                                                    foreach ($feedbacks as $feedback):
                                                        $student = Student::getById($conn, $feedback['student_id']);
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($feedback['depth_of_coverage']); ?></td>
                                                        <td><?php echo htmlspecialchars($feedback['emphasis_on_fundamentals']); ?></td>
                                                        <td><?php echo htmlspecialchars($feedback['coverage_of_modern_topics']); ?></td>
                                                        <td><?php echo htmlspecialchars($feedback['overall_rating']); ?></td>
                                                        <td><?php echo htmlspecialchars($feedback['benefits']); ?></td>
                                                        <td><?php echo htmlspecialchars($feedback['instructor_assistance']); ?></td>
                                                        <td><?php echo htmlspecialchars($feedback['instructor_feedback']); ?></td>
                                                        <td><?php echo htmlspecialchars($feedback['motivation']); ?></td>
                                                        <td><?php echo htmlspecialchars($feedback['sme_help']); ?></td>
                                                        <td><?php echo htmlspecialchars($feedback['overall_very_good']); ?></td>
                                                    </tr>
                                                <?php
                                                    endforeach;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="students" role="tabpanel" aria-labelledby="students-tab">
                                <h4 class="card-title mt-3">Enrolled Students</h4>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search students...">
                                </div>
                                <table class="table table-hover mt-2" id="studentsTable">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col">S. No.</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Transaction ID</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Date of Transaction</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($enrolledStudents)): ?>
                                            <?php $serialNumber = 1; ?>
                                            <?php foreach ($enrolledStudents as $student): ?>
                                                <?php
                                                $studentTransactions = $transactions[$student['id']] ?? [];
                                                if (!empty($studentTransactions)):
                                                    foreach ($studentTransactions as $transaction):
                                                ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($serialNumber++); ?></td>
                                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                                                            <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                                                            <td><?php echo htmlspecialchars($transaction['amount']); ?></td>
                                                            <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                                                            <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
                                                        </tr>
                                                <?php
                                                    endforeach;
                                                else:
                                                ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($student['id']); ?></td>
                                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                                        <td colspan="3">No transactions found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6">No students enrolled.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS -->
<!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> -->

<script>
document.getElementById('linkOption').addEventListener('change', function() {
    document.getElementById('linkInput').style.display = 'block';
    document.getElementById('fileInput').style.display = 'none';
    document.getElementById('contentLink').required = true;
    document.getElementById('contentFile').required = false;
});

document.getElementById('fileOption').addEventListener('change', function() {
    document.getElementById('linkInput').style.display = 'none';
    document.getElementById('fileInput').style.display = 'block';
    document.getElementById('contentLink').required = false;
    document.getElementById('contentFile').required = true;
});
</script>
<script>
function removeContent(type, index) {
    if (confirm('Are you sure you want to remove this content?')) {
        $.ajax({
            url: '/admin/remove_public_content',
            type: 'POST',
            data: {
                type: type,
                index: index,
                course_id: <?php echo json_encode($course['id']); ?>
            },
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    alert(result.message);
                    location.reload(); // Refresh the page upon success
                } else {
                    alert(result.message);
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred: ' + error);
            }
        });
    }
}
</script>
<script>
function toggleAssignmentForm() {
    var form = document.getElementById('assignmentForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('assignmentForm');
    const startDateInput = document.getElementById('start_date');
    const dueDateInput = document.getElementById('due_date');

    form.addEventListener('submit', function(event) {
        const now = new Date();
        const startDate = new Date(startDateInput.value);
        const dueDate = new Date(dueDateInput.value);

        if (startDate < now) {
            alert('Start date and time cannot be in the past.');
            event.preventDefault();
            return;
        }

        if (dueDate <= startDate) {
            alert('Due date and time must be after the start date and time.');
            event.preventDefault();
            return;
        }
    });
});
</script>
<script>
document.getElementById('searchInput').addEventListener('input', function() {
    var searchValue = this.value.toLowerCase();
    var rows = document.querySelectorAll('#studentsTable tbody tr');
    rows.forEach(function(row) {
        var cells = row.querySelectorAll('td');
        var match = false;
        cells.forEach(function(cell) {
            if (cell.textContent.toLowerCase().includes(searchValue)) {
                match = true;
            }
        });
        row.style.display = match ? '' : 'none';
    });
});
</script>