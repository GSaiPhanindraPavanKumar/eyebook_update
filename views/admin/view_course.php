<?php
include "sidebar.php";
use Models\Database;
use Models\Faculty;
use Models\Student;

$conn = Database::getConnection();

// Fetch course details
$courseId = $_GET['course_id'] ?? null;
if (!$courseId) {
    // If course_id is not found in GET parameters, try to get it from the URL
    $url = $_SERVER['REQUEST_URI'];
    preg_match('/\/admin\/view_course\/(\d+)/', $url, $matches);
    $courseId = $matches[1] ?? null;
}

if ($courseId) {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch assigned faculty IDs
    $assignedFacultyIds = !empty($course['assigned_faculty']) ? json_decode($course['assigned_faculty'], true) : [];

    // Fetch assigned student IDs
    $assignedStudentIds = !empty($course['assigned_students']) ? json_decode($course['assigned_students'], true) : [];

    // Fetch faculty details
    $assignedFaculty = [];
    if (!empty($assignedFacultyIds)) {
        $placeholders = implode(',', array_fill(0, count($assignedFacultyIds), '?'));
        $stmt = $conn->prepare("SELECT * FROM faculty WHERE id IN ($placeholders)");
        $stmt->execute($assignedFacultyIds);
        $assignedFaculty = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch student details
    $assignedStudents = [];
    if (!empty($assignedStudentIds)) {
        $placeholders = implode(',', array_fill(0, count($assignedStudentIds), '?'));
        $stmt = $conn->prepare("SELECT * FROM students WHERE id IN ($placeholders)");
        $stmt->execute($assignedStudentIds);
        $assignedStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    echo "Course ID not provided.";
    exit();
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
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="true">Details</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="add-unit-tab" data-toggle="tab" href="#add-unit" role="tab" aria-controls="add-unit" aria-selected="false">Add Course Book</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="assign-universities-tab" data-toggle="tab" href="#assign-universities" role="tab" aria-controls="assign-universities" aria-selected="false">Assign to Universities</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                <h5 class="mt-3">Course Details</h5>
                                <?php if ($course): ?>
                                    <p><strong>Course Name:</strong> <?php echo htmlspecialchars($course['name'] ?? 'N/A'); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($course['description'] ?? 'N/A'); ?></p>
                                    <h5 class="mt-3">Assigned University</h5>
                                    <ul>
                                        <?php if (!empty($course['university_id'])): ?>
                                            <?php
                                            $stmt = $conn->prepare("SELECT long_name FROM universities WHERE id = ?");
                                            $stmt->execute([$course['university_id']]);
                                            $university = $stmt->fetch(PDO::FETCH_ASSOC);
                                            ?>
                                            <li><?php echo htmlspecialchars($university['long_name']); ?></li>
                                        <?php else: ?>
                                            <li>No university assigned.</li>
                                        <?php endif; ?>
                                    </ul>
                                    <div class="actions mt-4">
                                        <a href="/admin/edit_course/<?php echo $course['id']; ?>" class="btn btn-warning">Edit Course</a>
                                        <a href="/admin/delete_course/<?php echo $course['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this course?');">Delete Course</a>
                                    </div>
                                <?php else: ?>
                                    <p>Course not found.</p>
                                <?php endif; ?>

                                <h4 class="card-title mt-3 d-flex justify-content-between align-items-center">
                                    Assigned Faculty
                                    <form method="POST" action="/admin/unassign_faculty">
                                        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Unassign Selected Faculty</button>
                                    </form>
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
                                            <?php if (!empty($assignedFaculty)): ?>
                                                <?php foreach ($assignedFaculty as $faculty): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="faculty_ids[]" value="<?php echo htmlspecialchars($faculty['id']); ?>">
                                                        </td>
                                                        <td data-filter="name"><?php echo htmlspecialchars($faculty['name']); ?></td>
                                                        <td data-filter="email"><?php echo htmlspecialchars($faculty['email']); ?></td>
                                                        <td data-filter="stream"><?php echo htmlspecialchars($faculty['stream']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4">No faculty assigned.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                </form>

                                <h4 class="card-title mt-3 d-flex justify-content-between align-items-center">
                                    Assigned Students
                                    <form method="POST" action="/admin/unassign_students">
                                        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Unassign Selected Students</button>
                                    </form>
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
                                            <?php if (!empty($assignedStudents)): ?>
                                                <?php foreach ($assignedStudents as $student): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="student_ids[]" value="<?php echo htmlspecialchars($student['id']); ?>">
                                                        </td>
                                                        <td data-filter="regd_no"><?php echo htmlspecialchars($student['regd_no'] ?? 'N/A'); ?></td>
                                                        <td data-filter="name" style="width: 30%;"><?php echo htmlspecialchars($student['name']); ?></td>
                                                        <td data-filter="email"><?php echo htmlspecialchars($student['email']); ?></td>
                                                        <td data-filter="year"><?php echo htmlspecialchars($student['year']); ?></td>
                                                        <td data-filter="section"><?php echo htmlspecialchars($student['section']); ?></td>
                                                        <td data-filter="stream"><?php echo htmlspecialchars($student['stream']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7">No students assigned.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                </form>
                            </div>
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
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                    <button type="submit" class="btn btn-primary">Add Course Book</button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="assign-universities" role="tabpanel" aria-labelledby="assign-universities-tab">
                                <h5 class="mt-3">Assign Course to University</h5>
                                <form id="assignCourseForm" method="POST" action="/admin/assign_course">
                                    <div class="form-group">
                                        <label for="university">Select University</label>
                                        <select class="form-control" id="university" name="university_id" required>
                                            <option value="">Select a university</option>
                                            <?php foreach ($universities as $university): ?>
                                                <option value="<?php echo $university['id']; ?>"><?php echo htmlspecialchars($university['long_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <input type="hidden" name="confirm" id="confirm" value="false">
                                    <button type="submit" class="btn btn-primary">Assign Course</button>
                                </form>
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

<!-- Include Bootstrap CSS and JS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
document.getElementById('assignCourseForm').addEventListener('submit', function(event) {
    event.preventDefault();
    var form = this;
    var formData = new FormData(form);
    fetch(form.action, {
        method: form.method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.confirm) {
            if (confirm(data.message)) {
                document.getElementById('confirm').value = 'true';
                fetch(form.action, {
                    method: form.method,
                    body: new FormData(form)
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.message === 'Course reassigned to university successfully' || data.message === 'Course assigned to university successfully') {
                        location.reload(); // Refresh the page
                    }
                });
            }
        } else {
            alert(data.message);
            if (data.message === 'Course assigned to university successfully') {
                location.reload(); // Refresh the page
            }
        }
    });
});

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