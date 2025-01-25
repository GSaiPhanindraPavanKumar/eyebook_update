<?php include "sidebar.php"; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="font-weight-bold mb-4">Course Management: <?php echo htmlspecialchars($course['name']); ?></h2>
                    <span class="badge bg-primary text-white">Course ID: <?php echo $course['id']; ?></span>
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
                            <h5 class="d-flex justify-content-between align-items-center">
                                Course Plan
                            </h5>
                            <div>
                                <?php if ($course['status'] !== 'archived') : ?>
                                    <button type="button" class="btn btn-primary" onclick="toggleCoursePlanForm()">Upload</button>
                                <?php endif; ?>
                                <?php if (!empty($course['course_plan'])) : ?>
                                    <?php
                                    $hashedId = base64_encode($course['id']);
                                    $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                                    ?>
                                    <a href="/faculty/view_course_plan/<?php echo $hashedId; ?>" class="btn btn-primary">View</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($course['status'] !== 'archived') : ?>
                            <form id="uploadCoursePlanForm" action="upload_course_plan" method="post" enctype="multipart/form-data" style="display: none; margin-top: 10px;">
                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                <input type="file" name="course_plan_file" accept="application/pdf" required>
                                <button type="submit" class="btn btn-success">Upload</button>
                                <button type="button" class="btn btn-secondary" onclick="toggleCoursePlanForm()">Cancel</button>
                            </form>
                        <?php endif; ?>

                        <!-- Course Book Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5 class="d-flex justify-content-between align-items-center">
                                Course Book
                            </h5>
                        </div>
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
                                    foreach ($course['course_book'] as $unit) {
                                        echo "<tr>";
                                        echo "<td>" . $serialNumber++ . "</td>"; // Increment the serial number
                                        echo "<td>" . htmlspecialchars($unit['unit_name']) . "</td>";
                                        $full_url = $unit['scorm_url'];
                                        echo "<td><a href='/faculty/view_book/" . $hashedId . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View Course Book</a></td>";
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
                                        echo "<td><a href='/" . htmlspecialchars($full_url) . "' target='_blank' class='btn btn-primary'>View EC</a></td>";
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
                                    <th scope="col">Action</th>
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
                            <h5 class="d-flex justify-content-between align-items-center">
                                Course Materials
                            </h5>
                            <?php if ($course['status'] !== 'archived') : ?>
                                <button type="button" class="btn btn-primary" onclick="toggleUploadOptions()">Upload</button>
                            <?php endif; ?>
                        </div>
                        <?php if ($course['status'] !== 'archived') : ?>
                            <div id="uploadOptions" class="text-center" style="display: none; margin-top: 10px;">
                                <button type="button" class="btn btn-info" onclick="showSingleUpload()">Single Document</button>
                                <button type="button" class="btn btn-warning" onclick="showBulkUpload()">Bulk Upload</button>
                            </div>
                            <form id="uploadCourseMaterialsForm" action="upload_course_materials" method="post" enctype="multipart/form-data" style="display: none; margin-top: 10px;">
                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                <input type="hidden" name="upload_type" id="uploadType">
                                <div id="singleUpload" style="display: none;">
                                    <div class="form-group">
                                        <label for="unitNumber">Unit Number</label>
                                        <input type="number" class="form-control" name="unit_number" id="unitNumber">
                                    </div>
                                    <div class="form-group">
                                        <label for="topic">Topic</label>
                                        <input type="text" class="form-control" name="topic" id="topic">
                                    </div>
                                    <div class="form-group d-flex justify-content-between align-items-center">
                                        <input type="file" name="course_materials_file" accept="application/pdf">
                                        <div>
                                            <button type="submit" class="btn btn-success">Upload</button>
                                            <button type="button" class="btn btn-secondary" onclick="toggleCourseMaterialsForm()">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                                <div id="bulkUpload" style="display: none;">
                                    <div class="form-group">
                                        <label for="bulkUnitNumber">Unit Number</label>
                                        <input type="number" class="form-control" name="bulk_unit_number" id="bulkUnitNumber">
                                    </div>
                                    <div class="form-group d-flex justify-content-between align-items-center">
                                        <input type="file" name="bulk_course_materials_file" accept=".zip">
                                        <div>
                                            <button type="submit" class="btn btn-success">Upload</button>
                                            <button type="button" class="btn btn-secondary" onclick="toggleCourseMaterialsForm()">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>
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
                                                echo "<td><a href='/faculty/view_material/" . $hashedId . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View Material</a></td>";
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
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5 class="d-flex justify-content-between align-items-center">
                                Assignments
                            </h5>
                            <button type="button" class="btn btn-primary" onclick="toggleAssignmentForm()">Create Assignment</button>

                        </div>
                        <!-- Assignment Creation Form -->
                        <div id="assignmentForm" style="display: none; margin-top: 20px;">
                            <h5 class="card-title">Create Assignment</h5>
                            <form action="/faculty/create_assignment" method="post" enctype="multipart/form-data">
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
                                    <input type="file" class="form-control" id="assignment_file" name="assignment_file">
                                </div>
                                <input type="hidden" name="course_id[]" value="<?php echo $course['id']; ?>">
                                <button type="submit" class="btn btn-primary">Create Assignment</button>
                            </form>
                        </div>
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
                                        echo "<td><a href='/faculty/view_assignment/" . $assignment['id'] . "' class='btn btn-primary'>View</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>No assignments available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include 'footer.html'; ?>
</div>

<script>
function toggleCoursePlanForm() {
    var form = document.getElementById('uploadCoursePlanForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function toggleCourseMaterialsForm() {
    var form = document.getElementById('uploadCourseMaterialsForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function toggleUploadOptions() {
    var options = document.getElementById('uploadOptions');
    var form = document.getElementById('uploadCourseMaterialsForm');
    if (options.style.display === 'none') {
        options.style.display = 'block';
    } else {
        options.style.display = 'none';
        form.style.display = 'none';
    }
}

function showSingleUpload() {
    var singleUpload = document.getElementById('singleUpload');
    var bulkUpload = document.getElementById('bulkUpload');
    singleUpload.style.display = 'block';
    bulkUpload.style.display = 'none';
    var unitNumber = document.getElementById('unitNumber');
    var topic = document.getElementById('topic');
    var courseMaterialsFile = document.getElementById('course_materials_file');
    if (unitNumber && topic && courseMaterialsFile) {
        unitNumber.required = true;
        topic.required = true;
        courseMaterialsFile.required = true;
    }
    var bulkUnitNumber = document.getElementById('bulkUnitNumber');
    var bulkCourseMaterialsFile = document.getElementById('bulk_course_materials_file');
    if (bulkUnitNumber && bulkCourseMaterialsFile) {
        bulkUnitNumber.required = false;
        bulkCourseMaterialsFile.required = false;
    }
    document.getElementById('uploadType').value = 'single';
    document.getElementById('uploadCourseMaterialsForm').style.display = 'block';
}

function showBulkUpload() {
    var singleUpload = document.getElementById('singleUpload');
    var bulkUpload = document.getElementById('bulkUpload');
    singleUpload.style.display = 'none';
    bulkUpload.style.display = 'block';
    var unitNumber = document.getElementById('unitNumber');
    var topic = document.getElementById('topic');
    var courseMaterialsFile = document.getElementById('course_materials_file');
    if (unitNumber && topic && courseMaterialsFile) {
        unitNumber.required = false;
        topic.required = false;
        courseMaterialsFile.required = false;
    }
    var bulkUnitNumber = document.getElementById('bulkUnitNumber');
    var bulkCourseMaterialsFile = document.getElementById('bulk_course_materials_file');
    if (bulkUnitNumber && bulkCourseMaterialsFile) {
        bulkUnitNumber.required = true;
        bulkCourseMaterialsFile.required = true;
    }
    document.getElementById('uploadType').value = 'bulk';
    document.getElementById('uploadCourseMaterialsForm').style.display = 'block';
}

function redirectToCoursePlan() {
    var coursePlan = <?php echo json_encode($course['course_plan']); ?>;
    var coursePlanUrl = "";
    if (coursePlan && coursePlan.url) {
        coursePlanUrl = coursePlan.url;
    }

    if (coursePlanUrl) {
        window.open(coursePlanUrl, '_blank');
    } else {
        alert('Course Plan URL not available.');
    }
}

function redirectToCourseBook(url) {
    var courseBookUrl = encodeURIComponent(url);

    if (url) {
        window.location.href = courseBookUrl;
    } else {
        alert('Course Book URL not available.');
    }
}

function redirectToCourseMaterial(url) {
    var courseMaterialUrl = url;

    if (url) {
        window.open(courseMaterialUrl, '_blank');
    } else {
        alert('Course Material URL not available.');
    }
}

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