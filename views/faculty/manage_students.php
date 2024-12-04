<?php
include 'sidebar.php';
use Models\Database;
use Models\Faculty;
use Models\Student;
use Models\Course;

$conn = (new Database())->getConnection();
if (!isset($_SESSION['faculty_id'])) {
    die('Faculty ID not set in session.');
}

$facultyId = $_SESSION['faculty_id']; // Assuming faculty ID is stored in session

// Fetch the assigned courses for the faculty
$assignedCourses = Faculty::getAssignedCourses($conn, $facultyId);

// Fetch students from the database
$students = [];
$courseNames = [];
if (!empty($assignedCourses)) {
    $placeholders = implode(',', array_fill(0, count($assignedCourses), '?'));
    $sql = "SELECT id, assigned_students, name as course_name FROM courses WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($assignedCourses);
    $assignedStudents = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $courseId = $row['id'];
        $courseName = $row['course_name'];
        $studentIds = $row['assigned_students'] ? json_decode($row['assigned_students'], true) : [];
        if (json_last_error() === JSON_ERROR_NONE && is_array($studentIds)) {
            foreach ($studentIds as $studentId) {
                $assignedStudents[] = $studentId;
                $courseNames[$studentId] = $courseName;
            }
        }
    }
    $assignedStudents = array_unique($assignedStudents);

    if (!empty($assignedStudents)) {
        $placeholders = implode(',', array_fill(0, count($assignedStudents), '?'));
        $sql = "SELECT * FROM students WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($assignedStudents);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage Students</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Students</p><br>
                        <div class="table-responsive">
                            <input class="form-control mb-3" id="searchInput" type="text" placeholder="Search...">
                            <table class="table table-striped table-borderless table-lg">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">Serial Number</th>
                                        <th style="width: 15%;">Registration Number</th>
                                        <th style="width: 20%;">Name</th>
                                        <th style="width: 20%;">Email</th>
                                        <th style="width: 10%;">Section</th>
                                        <th style="width: 10%;">Stream</th>
                                        <th style="width: 10%;">Year</th>
                                        <th style="width: 10%;">Course Name</th>
                                    </tr>
                                </thead>
                                <tbody id="studentTable">
                                    <?php
                                    $serialNumber = 1;
                                    foreach ($students as $student): ?>
                                        <tr>
                                            <td><?= $serialNumber++ ?></td>
                                            <td><?= htmlspecialchars($student['regd_no']) ?></td>
                                            <td><?= htmlspecialchars($student['name']) ?></td>
                                            <td><?= htmlspecialchars($student['email']) ?></td>
                                            <td><?= htmlspecialchars($student['section']) ?></td>
                                            <td><?= htmlspecialchars($student['stream']) ?></td>
                                            <td><?= htmlspecialchars($student['year']) ?></td>
                                            <td><?= htmlspecialchars($courseNames[$student['id']] ?? 'N/A') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div id="noRecords" style="display: none;">No records found</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('#studentTable tr');
            let noRecords = true;

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let match = false;
                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(filter)) {
                        match = true;
                    }
                });
                if (match) {
                    row.style.display = '';
                    noRecords = false;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('noRecords').style.display = noRecords ? '' : 'none';
        });
    });
</script>