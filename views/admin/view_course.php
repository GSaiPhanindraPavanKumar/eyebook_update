<?php 
include "sidebar.php"; 
use Models\Faculty;
use Models\Student;
use Models\Course;
use Models\feedback;
use Models\Cohort;

// Fetch all faculties and students for the assigned university
$feedbackEnabled = $course['feedback_enabled'];


// Fetch assigned universities
$assignedUniversities = Course::getAssignedUniversities($conn, $course['id']);

// Fetch all cohorts
$allCohorts = Cohort::getAll($conn); // Add this line

// Fetch assigned cohorts
$assignedCohorts = Course::getAssignedCohorts($conn, $course['id']);
$assignedCohorts = is_array($assignedCohorts) ? $assignedCohorts : json_decode($assignedCohorts, true);
if ($assignedCohorts === null) {
    $assignedCohorts = [];
}
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                <style>
                    /* Add this CSS rule to ensure text wraps in the feedback column */
                    #feedbackTable td[data-filter="feedback"] {
                        white-space: normal;
                        word-wrap: break-word;
                    }
                </style>
                    <h2 class="font-weight-bold mb-4">Course Management: <?php echo htmlspecialchars($course['name'] ?? 'N/A'); ?></h2>
                    <span class="badge bg-primary text-white">Course ID: <?php echo htmlspecialchars($course['id'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>

        <!-- Tabs for Course Details, Add Course Book, Assign to Universities, Assign Faculty, and Assign Students -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="true">Details</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="add-unit-tab" data-toggle="tab" href="#add-unit" role="tab" aria-controls="add-unit" aria-selected="false">Uploads</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="assign-universities-tab" data-toggle="tab" href="#assign-universities" role="tab" aria-controls="assign-universities" aria-selected="false">Assign to Universities</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="assign-faculty-tab" data-toggle="tab" href="#assign-faculty" role="tab" aria-controls="assign-faculty" aria-selected="false">Assign Faculty</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="assign-students-tab" data-toggle="tab" href="#assign-students" role="tab" aria-controls="assign-students" aria-selected="false">Assign Students</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="assign-cohort-tab" data-toggle="tab" href="#assign-cohort" role="tab" aria-controls="assign-cohort" aria-selected="false">Assign Cohort</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="assignment-tab" data-toggle="tab" href="#assignment" role="tab" aria-controls="assignment" aria-selected="false">Assignment</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="feedback-tab" data-toggle="tab" href="#feedback" role="tab" aria-controls="feedback" aria-selected="false">Feedback</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <!-- Course Details Tab -->
                            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                <h4 class="card-title mt-3">Course Details</h4>
                                <?php if ($course): ?>
                                    <p><strong>Course Name:</strong> <?php echo htmlspecialchars($course['name'] ?? 'N/A'); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($course['description'] ?? 'N/A'); ?></p>
                                    <p><strong>Status:</strong> <?php echo htmlspecialchars($course['status'] ?? 'N/A'); ?></p>
                                    <h5 class="mt-3">Assigned Universities</h5>
                                    <ul>
                                        <?php if (!empty($course['university_id'])): ?>
                                            <?php
                                            $university_ids = is_array($course['university_id']) ? $course['university_id'] : json_decode($course['university_id'], true);
                                            if (is_array($university_ids)) {
                                                foreach ($university_ids as $university_id) {
                                                    $university = array_filter($universities, function($u) use ($university_id) {
                                                        return $u['id'] == $university_id;
                                                    });
                                                    $university = array_shift($university);
                                                    if ($university) {
                                                        echo '<li>' . htmlspecialchars($university['long_name']) . '</li>';
                                                    }
                                                }
                                            } else {
                                                echo '<li>No university assigned.</li>';
                                            }
                                            ?>
                                        <?php else: ?>
                                            <li>No university assigned.</li>
                                        <?php endif; ?>
                                    </ul>
                                    <div class="actions mt-4">
                                        <a href="/admin/edit_course/<?php echo $course['id']; ?>" class="btn btn-warning">Edit Course</a>
                                        <a href="/admin/delete_course/<?php echo $course['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this course?');">Delete Course</a>
                                    </div>
                                    <!-- EC Content Table -->
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
                                                $hashedId = base64_encode($course['id']);
                                                $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                                                foreach ($ECContent as $content) {
                                                    echo "<tr>";
                                                    echo "<td>" . $serialNumber++ . "</td>";
                                                    echo "<td>" . htmlspecialchars($content['unitTitle'] ?? 'N/A') . "</td>";
                                                    $full_url = $content['indexPath'] ?? '#';
                                                    echo "<td>
                                                            <a href='/admin/viewECbook/" . urlencode($hashedId) . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View EC</a>
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
                                    <h4 class="card-title mt-3">Course Book</h4>
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
                                    <h5 class="mt-5">Additional Content</h5>
                                    <table class="table table-hover mt-2">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th scope="col">S. No.</th>
                                                <th scope="col">Title</th>
                                                <th scope="col">Link</th>                                            </tr>
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
                                                    $full_url = $content['link'] ?? '#';
                                                    echo "<td>
                                                            <a href='" . htmlspecialchars(string: $full_url) . "' target='_blank' class='btn btn-primary'>View Content</a>
                                                            <button class='btn btn-danger' onclick='removeContent(\"additional_content\", " . ($serialNumber - 2) . ")'>Remove</button>
                                                        </td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='4'>No additional content available.</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                <h4 class="card-title mt-3 d-flex justify-content-between align-items-center">
                                    Assigned Faculty
                                    <form method="POST" action="/admin/unassign_faculty">
                                        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Unassign Selected Faculty</button>
                                </h4>
                                <div class="table-responsive">
                                    <div class="input-group mb-3">
                                        <input class="form-control" id="searchInputAssignedFaculty" type="text" placeholder="🔍 Search Faculty...">
                                        <div class="input-group-append">
                                            <select class="form-control" id="filterSelectAssignedFaculty">
                                                <option value="">Filter by...</option>
                                                <option value="name">Name</option>
                                                <option value="email">Email</option>
                                                <option value="stream">Stream</option>
                                            </select>
                                        </div>
                                    </div>
                                    <table class="table table-hover table-borderless table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th><input type="checkbox" id="selectAllAssignedFaculty"></th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Stream</th>
                                            </tr>
                                        </thead>
                                        <tbody id="assignedFacultyTable">
                                            <?php foreach ($assignedFaculty as $faculty): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="faculty_ids[]" value="<?php echo $faculty['id']; ?>">
                                                    </td>
                                                    <td data-filter="name"><?php echo htmlspecialchars($faculty['name']); ?></td>
                                                    <td data-filter="email"><?php echo htmlspecialchars($faculty['email']); ?></td>
                                                    <td data-filter="stream"><?php echo htmlspecialchars($faculty['stream']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                </form>

                                <h4 class="card-title mt-3 d-flex justify-content-between align-items-center">
                                    Assigned Students
                                    <form method="POST" action="/admin/unassign_students">
                                        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Unassign Selected Students</button>
                                </h4>
                                <div class="table-responsive">
                                    <div class="input-group mb-3">
                                        <input class="form-control" id="searchInputAssignedStudents" type="text" placeholder="🔍 Search Students...">
                                        <div class="input-group-append">
                                            <select class="form-control" id="filterSelectAssignedStudents">
                                                <option value="">Filter by...</option>
                                                <option value="regd_no">Register Number</option>
                                                <option value="name">Name</option>
                                                <option value="email">Email</option>
                                                <option value="year">Year</option>
                                                <option value="section">Section</option>
                                                <option value="stream">Stream</option>
                                            </select>
                                        </div>
                                    </div>
                                    <table class="table table-hover table-borderless table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th><input type="checkbox" id="selectAllAssignedStudents"></th>
                                                <th>Register Number</th>
                                                <th style="width: 30%;">Name</th>
                                                <th>Email</th>
                                                <th>Year</th>
                                                <th>Section</th>
                                                <th>Stream</th>
                                            </tr>
                                        </thead>
                                        <tbody id="assignedStudentsTable">
                                            <?php foreach ($assignedStudents as $student): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>">
                                                    </td>
                                                    <td data-filter="regd_no"><?php echo htmlspecialchars($student['regd_no'] ?? 'N/A'); ?></td>
                                                    <td data-filter="name" style="width: 30%;"><?php echo htmlspecialchars($student['name']); ?></td>
                                                    <td data-filter="email"><?php echo htmlspecialchars($student['email']); ?></td>
                                                    <td data-filter="year"><?php echo htmlspecialchars($student['year']); ?></td>
                                                    <td data-filter="section"><?php echo htmlspecialchars($student['section']); ?></td>
                                                    <td data-filter="stream"><?php echo htmlspecialchars($student['stream']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                </form>
                                    
                                <?php else: ?>
                                    <p>Course not found.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Add Course Book Tab -->
                            <div class="tab-pane fade" id="add-unit" role="tabpanel" aria-labelledby="add-unit-tab">
                                <h5 class="mt-3">Add Course Book</h5>
                                <form method="POST" action="/admin/add_unit" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="unitName">Name</label>
                                        <input type="text" class="form-control" id="unitName" name="unit_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="scormFile">SCORM File</label>
                                        <input type="file" class="form-control" id="scormFile" name="scorm_file" required>
                                        <small class="form-text text-muted">Allowed file formats: .zip inside .xml in main directory</small>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                    <button type="submit" class="btn btn-primary">Add Course Book</button>
                                </form>
                                <h5 class="mt-5">EC Content Upload</h5>
                                <form method="POST" action="/admin/upload_ec_content" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="ecContentTitle">Title</label>
                                        <input type="text" class="form-control" id="ecContentTitle" name="ec_content_title" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="ecContentFile">Upload ZIP File</label>
                                        <input type="file" class="form-control" id="ecContentFile" name="ec_content_file" accept=".zip" required>
                                        <small class="form-text text-muted">Allowed file format: .zip</small>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                    <button type="submit" class="btn btn-primary">Upload EC Content</button>
                                </form>
                                <h5 class="mt-5">Additional Content</h5>
                                <form method="POST" action="/admin/add_additional_content" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="contentTitle">Title</label>
                                        <input type="text" class="form-control" id="contentTitle" name="content_title" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Content Type</label><br>
                                        <input type="radio" id="linkOption" name="content_type" value="link" checked>
                                        <label for="linkOption">Link</label>
                                        <input type="radio" id="fileOption" name="content_type" value="file">
                                        <label for="fileOption">File</label>
                                    </div>
                                    <div class="form-group" id="linkInput">
                                        <label for="contentLink">Link</label>
                                        <input type="url" class="form-control" id="contentLink" name="content_link">
                                    </div>
                                    <div class="form-group" id="fileInput" style="display: none;">
                                        <label for="contentFile">File</label>
                                        <input type="file" class="form-control" id="contentFile" name="content_file" accept=".pdf,video/*,image/*">
                                        <small class="form-text text-muted">Allowed file formats: .pdf, video files, image files</small>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                    <button type="submit" class="btn btn-primary">Add Additional Content</button>
                                </form>
                            </div>

                            <!-- Assign to Universities Tab -->
                            <div class="tab-pane fade" id="assign-universities" role="tabpanel" aria-labelledby="assign-universities-tab">
                                <h5 class="mt-3">Assign Course to University</h5>
                                <form id="assignCourseForm" method="POST" action="/admin/assign_course">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Select</th>
                                                    <th>Short Name</th>
                                                    <th>Full Name</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $assignedUniversities = is_array($course['university_id']) ? $course['university_id'] : json_decode($course['university_id'], true);
                                                foreach ($universities as $university): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="university_ids[]" value="<?php echo $university['id']; ?>" <?php echo in_array($university['id'], $assignedUniversities) ? 'disabled' : ''; ?>>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($university['short_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($university['long_name']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Assign Course</button>
                                </form>

                                <h5 class="mt-5">Unassign Universities</h5>
                                <form id="unassignCourseForm" method="POST" action="/admin/unassign_course">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Select</th>
                                                    <th>Short Name</th>
                                                    <th>Full Name</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($universities as $university):
                                                    if (in_array($university['id'], $assignedUniversities)): ?>
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox" name="university_ids[]" value="<?php echo $university['id']; ?>">
                                                            </td>
                                                            <td><?php echo htmlspecialchars($university['short_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($university['long_name']); ?></td>
                                                        </tr>
                                                    <?php endif;
                                                endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" class="btn btn-danger">Unassign Course</button>
                                </form>
                            </div>

                            <!-- Assign Faculty Tab -->
                            <div class="tab-pane fade" id="assign-faculty" role="tabpanel" aria-labelledby="assign-faculty-tab">
                                <h4 class="card-title mt-3">Assign Faculty</h4>
                                <form method="POST" action="/admin/assign_faculty">
                                    <div class="table-responsive">
                                        <div class="input-group mb-3">
                                            <input class="form-control" id="searchInputFaculty" type="text" placeholder="🔍 Search Faculty...">
                                            <div class="input-group-append">
                                                <select class="form-control" id="filterSelectFaculty">
                                                    <option value="">Filter by...</option>
                                                    <option value="name">Name</option>
                                                    <option value="email">Email</option>
                                                    <option value="stream">Stream</option>
                                                </select>
                                            </div>
                                        </div>
                                        <table class="table table-hover table-borderless table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th><input type="checkbox" id="selectAllFaculty"></th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Stream</th>
                                                </tr>
                                            </thead>
                                            <tbody id="facultyTable">
                                                <?php foreach ($allFaculty as $faculty): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="faculty_ids[]" value="<?php echo $faculty['id']; ?>" <?php echo in_array($faculty['id'], array_column($assignedFaculty, 'id')) ? 'disabled' : ''; ?>>
                                                        </td>
                                                        <td data-filter="name"><?php echo htmlspecialchars($faculty['name']); ?></td>
                                                        <td data-filter="email"><?php echo htmlspecialchars($faculty['email']); ?></td>
                                                        <td data-filter="stream"><?php echo htmlspecialchars($faculty['stream']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                    <button type="submit" class="btn btn-primary">Assign Faculty</button>
                                </form>
                            </div>

                            <!-- Assign Students Tab -->
                            <div class="tab-pane fade" id="assign-students" role="tabpanel" aria-labelledby="assign-students-tab">
                                <h4 class="card-title mt-3">Assign Students</h4>
                                <form method="POST" action="/admin/assign_students">
                                    <div class="table-responsive">
                                        <div class="input-group mb-3">
                                            <input class="form-control" id="searchInputStudents" type="text" placeholder="🔍 Search Students...">
                                            <div class="input-group-append">
                                                <select class="form-control" id="filterSelectStudents">
                                                    <option value="">Filter by...</option>
                                                    <option value="regd_no">Register Number</option>
                                                    <option value="name">Name</option>
                                                    <option value="email">Email</option>
                                                    <option value="year">Year</option>
                                                    <option value="section">Section</option>
                                                    <option value="stream">Stream</option>
                                                </select>
                                            </div>
                                        </div>
                                        <table class="table table-hover table-borderless table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th><input type="checkbox" id="selectAllStudents"></th>
                                                    <th>Register Number</th>
                                                    <th style="width: 30%;">Name</th>
                                                    <th>Email</th>
                                                    <th>Year</th>
                                                    <th>Section</th>
                                                    <th>Stream</th>
                                                </tr>
                                            </thead>
                                            <tbody id="studentsTable">
                                                <?php foreach ($allStudents as $student): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>" <?php echo in_array($student['id'], array_column($assignedStudents, 'id')) ? 'disabled' : ''; ?>>
                                                        </td>
                                                        <td data-filter="regd_no"><?php echo htmlspecialchars($student['regd_no'] ?? 'N/A'); ?></td>
                                                        <td data-filter="name" style="width: 30%;"><?php echo htmlspecialchars($student['name']); ?></td>
                                                        <td data-filter="email"><?php echo htmlspecialchars($student['email']); ?></td>
                                                        <td data-filter="year"><?php echo htmlspecialchars($student['year']); ?></td>
                                                        <td data-filter="section"><?php echo htmlspecialchars($student['section']); ?></td>
                                                        <td data-filter="stream"><?php echo htmlspecialchars($student['stream']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                    <button type="submit" class="btn btn-primary">Assign Students</button>
                                </form>
                            </div>

                            <!-- Assign Cohort Tab -->
                            <div class="tab-pane fade" id="assign-cohort" role="tabpanel" aria-labelledby="assign-cohort-tab">
                                <h5 class="mt-3">Assign Course to Cohort</h5>
                                <form id="assignCohortForm" method="POST" action="/admin/assign_cohort_to_course">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Select</th>
                                                    <th>Cohort Name</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($allCohorts as $cohort): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="cohort_ids[]" value="<?php echo $cohort['id']; ?>" <?php echo in_array($cohort['id'], $assignedCohorts) ? 'disabled' : ''; ?>>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($cohort['name']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Assign Cohort</button>
                                </form>

                                <h5 class="mt-5">Unassign Cohorts</h5>
                                <form id="unassignCohortForm" method="POST" action="/admin/unassign_cohort_from_course">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Select</th>
                                                    <th>Cohort Name</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($allCohorts as $cohort):
                                                    if (in_array($cohort['id'], $assignedCohorts)): ?>
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox" name="cohort_ids[]" value="<?php echo $cohort['id']; ?>">
                                                            </td>
                                                            <td><?php echo htmlspecialchars($cohort['name']); ?></td>
                                                        </tr>
                                                    <?php endif;
                                                endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" class="btn btn-danger">Unassign Cohort</button>
                                </form>
                            </div>

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
                                    <form action="/admin/create_assignment" method="post" enctype="multipart/form-data">
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
                                                echo "<td><a href='/admin/view_assignment/" . $assignment['id'] . "' class='btn btn-primary'>View</a></td>";
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
                            <form method="post" action="/admin/toggle_feedback">
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
                                            $feedbacks = Course::getFeedback($conn, $course['id']);
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
                        </div>
                        <?php if (isset($message)): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = 'manage_courses.php';
                                }, 3000); // Redirect after 3 seconds
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS -->

<!-- Include Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#assignCourseForm').on('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        $.ajax({
            url: '/admin/assign_course', // Adjust the URL as needed
            type: 'POST',
            data: $(this).serialize(),
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
    });

    $('#unassignCourseForm').on('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        $.ajax({
            url: '/admin/unassign_course', // Adjust the URL as needed
            type: 'POST',
            data: $(this).serialize(),
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
    });
});
</script>

<script>
    $(document).ready(function() {
        $('#searchInputFaculty, #filterSelectFaculty').on('input change', function() {
            var searchValue = $('#searchInputFaculty').val().toLowerCase();
            var filterValue = $('#filterSelectFaculty').val();
            var visibleRows = 0;
            $('#facultyTable tr').filter(function() {
                var text = $(this).text().toLowerCase();
                var isVisible = text.indexOf(searchValue) > -1;
                if (filterValue) {
                    var cellValue = $(this).find('td[data-filter="' + filterValue + '"]').text().toLowerCase();
                    isVisible = isVisible && cellValue.indexOf(searchValue) > -1;
                }
                $(this).toggle(isVisible);
                if (isVisible) visibleRows++;
            });
            $('#noRecords').toggle(visibleRows === 0);
        });

        $('#selectAllFaculty').on('click', function() {
            $('input[name="faculty_ids[]"]').prop('checked', this.checked);
        });

        $('#searchInputStudents, #filterSelectStudents').on('input change', function() {
            var searchValue = $('#searchInputStudents').val().toLowerCase();
            var filterValue = $('#filterSelectStudents').val();
            var visibleRows = 0;
            $('#studentsTable tr').filter(function() {
                var text = $(this).text().toLowerCase();
                var isVisible = text.indexOf(searchValue) > -1;
                if (filterValue) {
                    var cellValue = $(this).find('td[data-filter="' + filterValue + '"]').text().toLowerCase();
                    isVisible = isVisible && cellValue.indexOf(searchValue) > -1;
                }
                $(this).toggle(isVisible);
                if (isVisible) visibleRows++;
            });
            $('#noRecords').toggle(visibleRows === 0);
        });

        $('#selectAllStudents').on('click', function() {
            $('input[name="student_ids[]"]').prop('checked', this.checked);
        });

        $('#searchInputAssignedFaculty, #filterSelectAssignedFaculty').on('input change', function() {
            var searchValue = $('#searchInputAssignedFaculty').val().toLowerCase();
            var filterValue = $('#filterSelectAssignedFaculty').val();
            var visibleRows = 0;
            $('#assignedFacultyTable tr').filter(function() {
                var text = $(this).text().toLowerCase();
                var isVisible = text.indexOf(searchValue) > -1;
                if (filterValue) {
                    var cellValue = $(this).find('td[data-filter="' + filterValue + '"]').text().toLowerCase();
                    isVisible = isVisible && cellValue.indexOf(searchValue) > -1;
                }
                $(this).toggle(isVisible);
                if (isVisible) visibleRows++;
            });
            $('#noRecords').toggle(visibleRows === 0);
        });

        $('#selectAllAssignedFaculty').on('click', function() {
            $('input[name="faculty_ids[]"]').prop('checked', this.checked);
        });

        $('#searchInputAssignedStudents, #filterSelectAssignedStudents').on('input change', function() {
            var searchValue = $('#searchInputAssignedStudents').val().toLowerCase();
            var filterValue = $('#filterSelectAssignedStudents').val();
            var visibleRows = 0;
            $('#assignedStudentsTable tr').filter(function() {
                var text = $(this).text().toLowerCase();
                var isVisible = text.indexOf(searchValue) > -1;
                if (filterValue) {
                    var cellValue = $(this).find('td[data-filter="' + filterValue + '"]').text().toLowerCase();
                    isVisible = isVisible && cellValue.indexOf(searchValue) > -1;
                }
                $(this).toggle(isVisible);
                if (isVisible) visibleRows++;
            });
            $('#noRecords').toggle(visibleRows === 0);
        });

        $('#selectAllAssignedStudents').on('click', function() {
            $('input[name="student_ids[]"]').prop('checked', this.checked);
        });
    });
</script>
<script>
function showGraphicalFeedback() {
    document.getElementById('graphicalFeedback').style.display = 'block';
    document.getElementById('individualFeedback').style.display = 'none';
    renderGraphicalFeedback();
}

function showIndividualFeedback() {
    document.getElementById('graphicalFeedback').style.display = 'none';
    document.getElementById('individualFeedback').style.display = 'block';
}
function renderGraphicalFeedback() {
    var ctx = document.getElementById('feedbackChart').getContext('2d');
    var feedbackData = <?php echo json_encode($feedbacks); ?>;

    var labels = ['Depth of Coverage', 'Emphasis on Fundamentals', 'Coverage of Modern Topics', 'Overall Rating', 'Instructor Assistance', 'Instructor Feedback', 'Motivation', 'SME Help', 'Overall Very Good'];
    var data = {
        labels: labels,
        datasets: []
    };

    var questions = ['depth_of_coverage', 'emphasis_on_fundamentals', 'coverage_of_modern_topics', 'overall_rating', 'instructor_assistance', 'instructor_feedback', 'motivation', 'sme_help', 'overall_very_good'];
    var options = ['Basic Level', 'Intermediate Level', 'Advance Level', 'Poor', 'Satisfactory', 'Good', 'Very Good', 'Excellent', 'Strongly Agree', 'Agree', 'Neutral', 'Disagree', 'Strongly disagree'];

    questions.forEach(function(question, index) {
        var counts = {};
        options.forEach(function(option) {
            counts[option] = 0;
        });

        feedbackData.forEach(function(feedback) {
            var answer = feedback[question];
            if (counts[answer] !== undefined) {
                counts[answer]++;
            }
        });

        var dataset = {
            label: labels[index],
            data: options.map(function(option) { return counts[option]; }),
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        };

        data.datasets.push(dataset);
    });

    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
</script>
<script>
$(document).ready(function() {
    $('#searchInputFeedback, #filterSelectFeedback').on('input change', function() {
        var searchValue = $('#searchInputFeedback').val().toLowerCase();
        var filterValue = $('#filterSelectFeedback').val();
        var visibleRows = 0;
        $('#feedbackTable tr').filter(function() {
            var text = $(this).text().toLowerCase();
            var isVisible = text.indexOf(searchValue) > -1;
            if (filterValue) {
                var cellValue = $(this).find('td[data-filter="' + filterValue + '"]').text().toLowerCase();
                isVisible = isVisible && cellValue.indexOf(searchValue) > -1;
            }
            $(this).toggle(isVisible);
            if (isVisible) visibleRows++;
        });
        $('#noRecords').toggle(visibleRows === 0);
    });
});
</script>

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
            url: '/admin/remove_content',
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
function removeContent(type, index) {
    if (confirm('Are you sure you want to remove this content?')) {
        $.ajax({
            url: '/admin/remove_content',
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