<?php
include("sidebar.php");
use Models\Database;
$conn = Database::getConnection();

$sql = "SELECT courses.*, courses.university_id AS university_ids 
        FROM courses 
        ORDER BY courses.id ASC"; // Add ordering to ensure consistent results
$stmt = $conn->prepare($sql);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug log to check courses array
error_log("Courses before processing: " . print_r($courses, true));

$processed_courses = array();
foreach ($courses as $course) {
    // Skip if we've already processed this course ID
    if (isset($processed_courses[$course['id']])) {
        continue;
    }
    
    $university_ids = !empty($course['university_ids']) ? json_decode($course['university_ids'], true) : [];
    if (is_array($university_ids) && !empty($university_ids)) {
        $placeholders = implode(',', array_fill(0, count($university_ids), '?'));
        $sql = "SELECT short_name FROM universities WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($university_ids);
        $universities = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $course['university'] = implode(', ', $universities);
    } else {
        $course['university'] = 'N/A';
    }
    
    $processed_courses[$course['id']] = $course;
}

// Debug log to check processed courses
error_log("Processed courses: " . print_r($processed_courses, true));

// Convert back to indexed array
$courses = array_values($processed_courses);
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage Courses</h3>
                    </div>
                    <div class="col-12 col-xl-4 text-right">
                        <a href="add_courses" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Create Course
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Courses</p><br>
                        <div class="table-responsive">
                            <input class="form-control mb-3" id="searchInput" type="text" placeholder="🔍 Search Courses...">
                            <form id="courseForm" method="post" action="/admin/view_course">
                                <table class="table table-hover table-borderless table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="col-serial-number" data-sort="serialNumber">S.no <i class="fas fa-sort"></i></th>
                                            <th class="col-course-name" data-sort="courseName">Course Name <i class="fas fa-sort"></i></th>
                                            <th class="col-university" data-sort="university">University <i class="fas fa-sort"></i></th>
                                            <th class="col-actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="courseTable">
                                        <?php 
                                        $serialNumber = 1;
                                        foreach ($courses as $course): 
                                        ?>
                                            <tr>
                                                <td class="col-serial-number"><?= $serialNumber++ ?></td>
                                                <td class="col-course-name"><?= htmlspecialchars($course['name']) ?></td>
                                                <td class="col-university"><?= htmlspecialchars($course['university'] ?? 'N/A') ?></td>
                                                <td class="col-actions">
                                                    <a href="/admin/view_course/<?= $course['id'] ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i> View</a>
                                                    <a href="/admin/edit_course/<?= $course['id'] ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                                    <?php if ($course['status'] === 'archived'): ?>
                                                        <form method="POST" action="/admin/unarchive_course" style="display:inline;" onsubmit="return confirmUnarchive()">
                                                            <input type="hidden" name="archive_course_id" value="<?= $course['id'] ?>">
                                                            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-archive"></i> Unarchive</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <form method="POST" action="/admin/archive_course" style="display:inline;" onsubmit="return confirmArchive()">
                                                            <input type="hidden" name="archive_course_id" value="<?= $course['id'] ?>">
                                                            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-archive"></i> Archive</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <a href="/admin/delete_course/<?= $course['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this course?');"><i class="fas fa-trash"></i> Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div id="noRecords" style="display: none;" class="text-center">No records found</div>
                            </form>
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

<!-- Move these script tags before your existing scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>

<!-- Replace your existing scripts section with this -->
<script>
$(document).ready(function() {
    // Initialize dropdowns
    $('.dropdown-toggle').dropdown();
    
    // Search functionality
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        var visibleRows = 0;
        $('#courseTable tr').filter(function() {
            var isVisible = $(this).text().toLowerCase().indexOf(value) > -1;
            $(this).toggle(isVisible);
            if (isVisible) visibleRows++;
        });
        $('#noRecords').toggle(visibleRows === 0);
    });

    // Table sorting
    $('th[data-sort]').on('click', function() {
        var table = $(this).parents('table').eq(0);
        var rows = table.find('tbody tr').toArray().sort(comparer($(this).index()));
        this.asc = !this.asc;
        if (!this.asc) { rows = rows.reverse(); }
        for (var i = 0; i < rows.length; i++) { table.append(rows[i]); }
    });

    function comparer(index) {
        return function(a, b) {
            var valA = getCellValue(a, index), valB = getCellValue(b, index);
            return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB);
        };
    }

    function getCellValue(row, index) {
        return $(row).children('td').eq(index).text();
    }
});
</script>

<style>
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .col-serial-number { width: 10%; }
    .col-course-name { width: 30%; }
    .col-university { width: 30%; }
    .col-actions { width: 30%; }
    @media (max-width: 768px) {
        .col-serial-number, .col-course-name, .col-university, .col-actions {
            width: auto;
        }
    }
</style>