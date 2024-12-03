<?php include "sidebar.php"; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="font-weight-bold mb-4">Course Management: <?php echo htmlspecialchars($course['name'] ?? 'N/A'); ?></h2>
                    <span class="badge bg-primary text-white">Course ID: <?php echo htmlspecialchars($course['id'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>

        <!-- Tabs for Course Details, Assign Faculty, and Assign Students -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="true">Details</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="assign-faculty-tab" data-toggle="tab" href="#assign-faculty" role="tab" aria-controls="assign-faculty" aria-selected="false">Assign Faculty</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="assign-students-tab" data-toggle="tab" href="#assign-students" role="tab" aria-controls="assign-students" aria-selected="false">Assign Students</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <!-- Course Details Tab -->
                            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                <h4 class="card-title mt-3">Course Details</h4>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($course['name'] ?? 'N/A'); ?></p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($course['description'] ?? 'N/A'); ?></p>
                                <p><strong>Status:</strong> <?php echo htmlspecialchars($course['status'] ?? 'N/A'); ?></p>

                                <!-- Course Book Section -->
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
                                                echo "<td>" . $serialNumber++ . "</td>"; // Increment the serial number
                                                echo "<td>" . htmlspecialchars($unit['unit_name']) . "</td>";
                                                $full_url = $unit['scorm_url'];
                                                echo "<td><a href='/spoc/view_book/" . $hashedId . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View Course Book</a></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4'>No course books available.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>

                                <h4 class="card-title mt-3 d-flex justify-content-between align-items-center">
                                    Assigned Faculty
                                    <form method="POST" action="/spoc/unassign_faculty">
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
                                    <form method="POST" action="/spoc/unassign_students">
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
                            </div>

                            <!-- Assign Faculty Tab -->
                            <div class="tab-pane fade" id="assign-faculty" role="tabpanel" aria-labelledby="assign-faculty-tab">
                                <h4 class="card-title mt-3">Assign Faculty</h4>
                                <form method="POST" action="/spoc/assign_faculty">
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
                                <form method="POST" action="/spoc/assign_students">
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
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<!-- Include Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

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