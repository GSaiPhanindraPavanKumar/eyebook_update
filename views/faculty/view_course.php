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
                        <ul class="nav nav-tabs" id="courseManagementTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="course-plan-tab" data-toggle="tab" href="#course-plan" role="tab">
                                    Course Plan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="course-book-tab" data-toggle="tab" href="#course-book" role="tab">
                                    Course Book
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="course-materials-tab" data-toggle="tab" href="#course-materials" role="tab">
                                    Course Materials
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="virtual-classroom-tab" data-toggle="tab" href="#virtual-classroom" role="tab">
                                    Virtual Classroom
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="assignments-tab" data-toggle="tab" href="#assignments" role="tab">
                                    Assignments
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="courseManagementContent">
                            <!-- Course Plan Tab -->
                            <div class="tab-pane fade show active" id="course-plan" role="tabpanel">
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
                                        <div class="form-group">
                                            <div class="drag-drop-zone" id="dragDropZone">
                                                <input type="file" id="course_plan_file" name="course_plan_file" 
                                                       accept="application/pdf" style="display: none;" required>
                                                <div class="icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <p>Drag and drop your file here or <span style="color: var(--menu-icon);">browse</span></p>
                                                <div class="file-requirements">
                                                    Allowed format: PDF only<br>
                                                    Maximum file size: 128MB
                                                </div>
                                                <div id="selectedFile" class="selected-file" style="display: none;">
                                                    <span class="file-name"></span>
                                                    <span class="remove-file">
                                                        <i class="fas fa-times"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success">Upload</button>
                                        <button type="button" class="btn btn-secondary" onclick="toggleCoursePlanForm()">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <!-- Course Book Tab -->
                            <div class="tab-pane fade" id="course-book" role="tabpanel">
                                <h5 class="mt-5">Course Book</h5>
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
                            </div>

                            <!-- Course Materials Tab -->
                            <div class="tab-pane fade" id="course-materials" role="tabpanel">
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
                                            <div class="form-group">
                                                <div class="drag-drop-zone" id="singleDragDropZone">
                                                    <input type="file" id="course_materials_file" name="course_materials_file" 
                                                           accept="application/pdf" style="display: none;">
                                                    <div class="icon">
                                                        <i class="fas fa-cloud-upload-alt"></i>
                                                    </div>
                                                    <p>Drag and drop your PDF file here or <span style="color: var(--menu-icon);">browse</span></p>
                                                    <div class="file-requirements">
                                                        Allowed format: PDF only<br>
                                                        Maximum file size: 128MB
                                                    </div>
                                                    <div id="singleSelectedFile" class="selected-file" style="display: none;">
                                                        <span class="file-name"></span>
                                                        <span class="remove-file">
                                                            <i class="fas fa-times"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="bulkUpload" style="display: none;">
                                            <div class="form-group">
                                                <label for="bulkUnitNumber">Unit Number</label>
                                                <input type="number" class="form-control" name="bulk_unit_number" id="bulkUnitNumber">
                                            </div>
                                            <div class="form-group">
                                                <div class="drag-drop-zone" id="bulkDragDropZone">
                                                    <input type="file" id="bulk_course_materials_file" name="bulk_course_materials_file" 
                                                           accept=".zip" style="display: none;">
                                                    <div class="icon">
                                                        <i class="fas fa-cloud-upload-alt"></i>
                                                    </div>
                                                    <p>Drag and drop your ZIP file here or <span style="color: var(--menu-icon);">browse</span></p>
                                                    <div class="file-requirements">
                                                        Allowed format: ZIP only<br>
                                                        Maximum file size: 128MB
                                                    </div>
                                                    <div id="bulkSelectedFile" class="selected-file" style="display: none;">
                                                        <span class="file-name"></span>
                                                        <span class="remove-file">
                                                            <i class="fas fa-times"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <button type="submit" class="btn btn-success">Upload</button>
                                            <button type="button" class="btn btn-secondary" onclick="toggleCourseMaterialsForm()">Cancel</button>
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
                            </div>

                            <!-- Virtual Classroom Tab -->
                            <div class="tab-pane fade" id="virtual-classroom" role="tabpanel">
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
                            </div>

                            <!-- Assignments Tab -->
                            <div class="tab-pane fade" id="assignments" role="tabpanel">
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
                                            <div class="drag-drop-zone" id="assignmentDragDropZone">
                                                <input type="file" id="assignment_file" name="assignment_file" 
                                                       accept="application/pdf" style="display: none;">
                                                <div class="icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <p>Drag and drop your PDF file here or <span style="color: var(--menu-icon);">browse</span></p>
                                                <div class="file-requirements">
                                                    Allowed format: PDF only<br>
                                                    Maximum file size: 128MB
                                                </div>
                                                <div id="assignmentSelectedFile" class="selected-file" style="display: none;">
                                                    <span class="file-name"></span>
                                                    <span class="remove-file">
                                                        <i class="fas fa-times"></i>
                                                    </span>
                                                </div>
                                            </div>
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

// Initialize drag and drop for assignment upload
document.addEventListener('DOMContentLoaded', function() {
    // Initialize existing drag drops
    initializeDragDrop('singleDragDropZone', 'course_materials_file', 'singleSelectedFile', 'pdf');
    initializeDragDrop('bulkDragDropZone', 'bulk_course_materials_file', 'bulkSelectedFile', 'zip');
    
    // Initialize assignment drag drop
    initializeDragDrop('assignmentDragDropZone', 'assignment_file', 'assignmentSelectedFile', 'pdf');

    // Assignment form validation
    const form = document.getElementById('assignmentForm');
    const startDateInput = document.getElementById('start_date');
    const dueDateInput = document.getElementById('due_date');
    const assignmentFile = document.getElementById('assignment_file');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        // Date validation
        const now = new Date();
        const startDate = new Date(startDateInput.value);
        const dueDate = new Date(dueDateInput.value);

        if (startDate < now) {
            $('#dateErrorModal').modal('show');
            return;
        }

        if (dueDate <= startDate) {
            $('#dueDateErrorModal').modal('show');
            return;
        }

        // File validation
        if (assignmentFile.files.length > 0) {
            const file = assignmentFile.files[0];
            
            if (file.type !== 'application/pdf') {
                $('#fileFormatModal').modal('show');
                return;
            }

            if (file.size > MAX_FILE_SIZE) {
                $('#fileSizeModal').modal('show');
                return;
            }
        }

        // If all validations pass, submit the form
        form.submit();
    });
});

const dragDropZone = document.getElementById('dragDropZone');
const fileInput = document.getElementById('course_plan_file');
const selectedFileDiv = document.getElementById('selectedFile');
const selectedFileName = selectedFileDiv.querySelector('.file-name');
const removeFileBtn = selectedFileDiv.querySelector('.remove-file');

// File size limit in bytes (128MB)
const MAX_FILE_SIZE = 128 * 1024 * 1024;

function handleFile(file) {
    // Check file type
    if (file.type !== 'application/pdf') {
        $('#fileFormatModal').modal('show');
        return false;
    }

    // Check file size
    if (file.size > MAX_FILE_SIZE) {
        $('#fileSizeModal').modal('show');
        return false;
    }

    // Display selected file
    selectedFileName.textContent = file.name;
    selectedFileDiv.style.display = 'flex';
    return true;
}

// Drag and drop handlers
dragDropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dragDropZone.classList.add('dragover');
});

dragDropZone.addEventListener('dragleave', () => {
    dragDropZone.classList.remove('dragover');
});

dragDropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dragDropZone.classList.remove('dragover');
    
    const file = e.dataTransfer.files[0];
    if (file && handleFile(file)) {
        fileInput.files = e.dataTransfer.files;
    }
});

// Click to browse
dragDropZone.addEventListener('click', () => {
    fileInput.click();
});

// File input change
fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    if (file) {
        handleFile(file);
    }
});

// Remove selected file
removeFileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    fileInput.value = '';
    selectedFileDiv.style.display = 'none';
});

function initializeDragDrop(dragZoneId, fileInputId, selectedFileId, acceptedType) {
    const dragDropZone = document.getElementById(dragZoneId);
    const fileInput = document.getElementById(fileInputId);
    const selectedFileDiv = document.getElementById(selectedFileId);
    const selectedFileName = selectedFileDiv.querySelector('.file-name');
    const removeFileBtn = selectedFileDiv.querySelector('.remove-file');

    function handleFile(file) {
        // Check file type
        const isValidType = acceptedType === 'pdf' ? 
            file.type === 'application/pdf' : 
            file.name.toLowerCase().endsWith('.zip');

        if (!isValidType) {
            $('#fileFormatModal').modal('show');
            return false;
        }

        // Check file size
        if (file.size > MAX_FILE_SIZE) {
            $('#fileSizeModal').modal('show');
            return false;
        }

        // Display selected file
        selectedFileName.textContent = file.name;
        selectedFileDiv.style.display = 'flex';
        return true;
    }

    dragDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dragDropZone.classList.add('dragover');
    });

    dragDropZone.addEventListener('dragleave', () => {
        dragDropZone.classList.remove('dragover');
    });

    dragDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dragDropZone.classList.remove('dragover');
        
        const file = e.dataTransfer.files[0];
        if (file && handleFile(file)) {
            fileInput.files = e.dataTransfer.files;
        }
    });

    dragDropZone.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (file) {
            handleFile(file);
        }
    });

    removeFileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.value = '';
        selectedFileDiv.style.display = 'none';
    });
}

// Preserve active tab on page reload
document.addEventListener('DOMContentLoaded', function() {
    // Get active tab from localStorage
    const activeTab = localStorage.getItem('activeCourseMgmtTab');
    if (activeTab) {
        const tab = document.querySelector(activeTab);
        if (tab) {
            tab.click();
        }
    }

    // Store active tab in localStorage when changed
    const tabs = document.querySelectorAll('[data-toggle="tab"]');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            localStorage.setItem('activeCourseMgmtTab', '#' + e.target.id);
        });
    });
});
</script>

<!-- Add this modal for file format warning -->
<div class="modal fade" id="fileFormatModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invalid File Format</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>The selected file does not match the required format. Please select a file with the correct format and try again.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add this modal for file size warning -->
<div class="modal fade" id="fileSizeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Too Large</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>The selected file exceeds the maximum size limit of 128MB. Please select a smaller file.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add these new modals -->
<div class="modal fade" id="dateErrorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invalid Start Date</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Start date and time cannot be in the past.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dueDateErrorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invalid Due Date</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Due date and time must be after the start date and time.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Update the file upload form -->
<style>
.drag-drop-zone {
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    background: var(--card-bg);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.drag-drop-zone.dragover {
    background: var(--hover-bg);
    border-color: var(--menu-icon);
}

.drag-drop-zone .icon {
    font-size: 2em;
    color: var(--text-color);
    margin-bottom: 10px;
}

.selected-file {
    margin-top: 10px;
    padding: 8px;
    background: var(--hover-bg);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.selected-file .file-name {
    margin-right: 10px;
    word-break: break-all;
}

.selected-file .remove-file {
    cursor: pointer;
    color: var(--text-color);
    opacity: 0.7;
}

.selected-file .remove-file:hover {
    opacity: 1;
}

.file-requirements {
    margin-top: 8px;
    font-size: 0.85em;
    color: var(--text-color);
    opacity: 0.7;
}

/* Tab Styling */
.nav-tabs {
    border-bottom: 1px solid var(--border-color);
}

.nav-tabs .nav-item {
    margin-bottom: -1px;
}

.nav-tabs .nav-link {
    color: var(--text-color);
    border: 1px solid transparent;
    border-top-left-radius: 0.25rem;
    border-top-right-radius: 0.25rem;
    padding: 0.75rem 1.25rem;
}

.nav-tabs .nav-link:hover {
    border-color: var(--hover-bg) var(--hover-bg) var(--border-color);
    background-color: var(--hover-bg);
}

.nav-tabs .nav-link.active {
    color: var(--menu-icon);
    background-color: var(--card-bg);
    border-color: var(--border-color) var(--border-color) var(--card-bg);
}

.tab-content {
    padding: 20px 0;
}

/* Preserve existing styles */
.tab-content .card {
    border: none;
    box-shadow: none;
}

.tab-content .card-body {
    padding: 0;
}
</style>