<?php include("sidebar.php"); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="font-weight-bold mb-4">Cohort Management: <?php echo htmlspecialchars($cohort['name'] ?? ''); ?></h2>
                    <span class="badge bg-primary text-white">Cohort ID: <?php echo htmlspecialchars($cohort['id'] ?? ''); ?></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <!-- Cohort Details Section -->
                        <h4 class="card-title mt-3">Cohort Details</h4>
                        <p><strong>Cohort Name:</strong> <?php echo htmlspecialchars($cohort['name'] ?? ''); ?></p>
                        
                        <!-- Courses Section -->
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mt-3">Courses</h5>
                            <button id="addCourseButton" class="btn btn-primary">Add Courses</button>
                        </div>
                        <ul>
                            <?php if (!empty($cohort['course_ids'])): ?>
                                <?php
                                $course_ids = is_array($cohort['course_ids']) ? $cohort['course_ids'] : json_decode($cohort['course_ids'], true);
                                if (is_array($course_ids)) {
                                    foreach ($course_ids as $course_id) {
                                        $course = array_filter($courses, function($c) use ($course_id) {
                                            return $c['id'] == $course_id;
                                        });
                                        $course = array_shift($course);
                                        if ($course) {
                                            echo '<li>' . htmlspecialchars($course['name']) . ' <a href="/admin/unassign_course_from_cohort/' . $cohort['id'] . '/' . $course_id . '" class="text-danger small">(Unassign)</a></li>';
                                        }
                                    }
                                } else {
                                    echo '<li>No course assigned.</li>';
                                }
                                ?>
                            <?php else: ?>
                                <li>No course assigned.</li>
                            <?php endif; ?>
                        </ul>
                        <div id="addCourseForm" class="mt-3" style="display: none;">
                            <form method="POST" action="/admin/assign_courses_to_cohort">
                                <input type="hidden" name="cohort_id" value="<?php echo htmlspecialchars($cohort['id'] ?? ''); ?>">
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Select</th>
                                                <th>Course Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allCourses as $course): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="course_ids[]" value="<?php echo $course['id']; ?>">
                                                    </td>
                                                    <td><?php echo htmlspecialchars($course['name']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <button type="button" id="cancelAddCourse" class="btn btn-secondary">Cancel</button>
                            </form>
                        </div>
                        
                        <!-- Assigned Students Section -->
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mt-3">Assigned Students</h5>
                            <button id="unassignStudentButton" class="btn btn-danger">Unassign Students</button>
                        </div>
                        <input type="text" id="assignedStudentsSearch" class="form-control mb-3" placeholder="Search Assigned Students">
                        <form id="unassignStudentForm" method="POST" action="/admin/unassign_students_from_cohort">
                            <input type="hidden" name="cohort_id" value="<?php echo htmlspecialchars($cohort['id'] ?? ''); ?>">
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless table-striped" id="assignedStudentsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th><input type="checkbox" id="selectAllAssigned"></th>
                                            <th>Student Name</th>
                                            <th>Email</th>
                                            <th>University</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>">
                                                </td>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                                <td>
                                                    <?php
                                                    $university = array_filter($universities, function($u) use ($student) {
                                                        return $u['id'] == $student['university_id'];
                                                    });
                                                    $university = array_shift($university);
                                                    echo htmlspecialchars($university['long_name'] ?? 'Unknown');
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                        
                        <!-- Add Students Section -->
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mt-3">Add Students</h5>
                            <button id="bulkAddStudentButton" class="btn btn-primary">Bulk Add Students</button>
                        </div>
                        <!-- Bulk Add Students Form -->
                        <div id="bulkAddStudentForm" class="mt-3" style="display: none;">
                            <form method="POST" action="/admin/bulk_add_students_to_cohort/<?php echo $cohort['id']; ?>" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="bulk_student_file">Upload Excel File</label>
                                    <input type="file" class="form-control" id="bulk_student_file" name="bulk_student_file" accept=".xlsx, .xls" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Upload</button>
                                <button type="button" id="cancelBulkAddStudent" class="btn btn-secondary">Cancel</button>
                                <a href="https://mobileappliaction.s3.us-east-1.amazonaws.com/Templates/cohort.xlsx" class="btn btn-info">Download Template</a>
                            </form>
                        </div>
                        <input type="text" id="addStudentsSearch" class="form-control mb-3" placeholder="Search Students to Add">
                        <form method="POST" action="/admin/add_students_to_cohort/<?php echo $cohort['id']; ?>">
                            <div class="form-group">
                                <label for="student_ids">Select Students to Add</label>
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless table-striped" id="addStudentsTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th><input type="checkbox" id="selectAllAdd"></th>
                                                <th>Student Name</th>
                                                <th>Email</th>
                                                <th>University</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allStudents as $student): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>" <?php echo in_array($student['id'], $existing_student_ids) ? 'disabled' : ''; ?>>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                                    <td>
                                                        <?php
                                                        $university = array_filter($universities, function($u) use ($student) {
                                                            return $u['id'] == $student['university_id'];
                                                        });
                                                        $university = array_shift($university);
                                                        echo htmlspecialchars($university['long_name'] ?? 'Unknown');
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Students</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Bulk Add Result Modal -->
<!-- Bulk Add Result Modal -->
<div class="modal fade" id="bulkAddResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Add Results</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['bulk_add_result'])): ?>
                    <?php $result = $_SESSION['bulk_add_result']; ?>
                    <div class="alert alert-info">
                        <strong>Processing Summary:</strong><br>
                        Total emails processed: <?php echo $result['total_processed']; ?><br>
                        New students added: <?php echo $result['added_count']; ?>
                    </div>

                    <?php if ($result['added_count'] > 0): ?>
                        <div class="alert alert-success">
                            Successfully added <?php echo $result['added_count']; ?> new students to the cohort.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            No new students were added. All valid emails were already in the cohort.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($result['duplicates_in_sheet'])): ?>
                        <div class="alert alert-warning">
                            <a href="#" class="btn btn-warning btn-sm mb-2 w-100 text-left" 
                               data-toggle="collapse" 
                               data-target="#duplicatesCollapse" 
                               role="button" 
                               aria-expanded="false">
                                <span>Duplicates Found in Sheet (<?php echo $result['duplicates_count']; ?>)</span>
                                <i class="fas fa-chevron-down float-right"></i>
                            </a>
                            <div class="collapse" id="duplicatesCollapse">
                                <ul class="list-group">
                                    <?php foreach ($result['duplicates_in_sheet'] as $email): ?>
                                        <li class="list-group-item"><?php echo htmlspecialchars($email); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($result['not_found_emails'])): ?>
                        <div class="alert alert-danger">
                            <a href="#" class="btn btn-danger btn-sm mb-2 w-100 text-left" 
                               data-toggle="collapse" 
                               data-target="#notFoundCollapse" 
                               role="button" 
                               aria-expanded="false">
                                <span>Emails Not Found (<?php echo $result['not_found_count']; ?>)</span>
                                <i class="fas fa-chevron-down float-right"></i>
                            </a>
                            <div class="collapse" id="notFoundCollapse">
                                <ul class="list-group">
                                    <?php foreach ($result['not_found_emails'] as $email): ?>
                                        <li class="list-group-item"><?php echo htmlspecialchars($email); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($result['existing_emails'])): ?>
                        <div class="alert alert-info">
                            <a href="#" class="btn btn-info btn-sm mb-2 w-100 text-left" 
                               data-toggle="collapse" 
                               data-target="#existingCollapse" 
                               role="button" 
                               aria-expanded="false">
                                <span>Already in Cohort (<?php echo $result['existing_count']; ?>)</span>
                                <i class="fas fa-chevron-down float-right"></i>
                            </a>
                            <div class="collapse" id="existingCollapse">
                                <ul class="list-group">
                                    <?php foreach ($result['existing_emails'] as $email): ?>
                                        <li class="list-group-item"><?php echo htmlspecialchars($email); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#addCourseButton').on('click', function () {
            $('#addCourseForm').show();
        });

        $('#cancelAddCourse').on('click', function () {
            $('#addCourseForm').hide();
        });

        $('#unassignStudentButton').on('click', function () {
            $('#unassignStudentForm').submit();
        });

        $('#bulkAddStudentButton').on('click', function () {
            $('#bulkAddStudentForm').show();
        });

        $('#cancelBulkAddStudent').on('click', function () {
            $('#bulkAddStudentForm').hide();
        });

        // Search functionality for Assigned Students
        $('#assignedStudentsSearch').on('keyup', function () {
            var value = $(this).val().toLowerCase();
            $('#assignedStudentsTable tbody tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Search functionality for Add Students
        $('#addStudentsSearch').on('keyup', function () {
            var value = $(this).val().toLowerCase();
            $('#addStudentsTable tbody tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Select All functionality for Assigned Students
        $('#selectAllAssigned').on('click', function () {
            var isChecked = $(this).prop('checked');
            $('#assignedStudentsTable tbody input[type="checkbox"]').prop('checked', isChecked);
        });

        // Select All functionality for Add Students
        $('#selectAllAdd').on('click', function () {
            var isChecked = $(this).prop('checked');
            $('#addStudentsTable tbody input[type="checkbox"]').prop('checked', isChecked);
        });

        // Simple collapse handler for icon rotation
        $('[data-toggle="collapse"]').on('click', function(e) {
            e.preventDefault();
            var icon = $(this).find('i');
            icon.toggleClass('fa-chevron-down fa-chevron-up');
        });

        <?php if (isset($_SESSION['bulk_add_result'])): ?>
            $('#bulkAddResultModal').modal('show');
        <?php 
            unset($_SESSION['bulk_add_result']);
        endif; ?>
    });
</script>

<!-- Update the CSS -->
<style>
.btn[data-toggle="collapse"] {
    position: relative;
}

.btn[data-toggle="collapse"] i {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    transition: transform 0.3s ease;
}

.collapse {
    display: none;
}

.collapse.show {
    display: block;
}

.collapsing {
    position: relative;
    height: 0;
    overflow: hidden;
    transition: height 0.35s ease;
}

.list-group {
    margin-top: 10px;
}
</style>