<?php
include("sidebar.php");
use Models\Database;
$conn = Database::getConnection();

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total number of records for pagination
$count_sql = "SELECT COUNT(*) FROM courses";
$total_records = $conn->query($count_sql)->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Modified SQL query with pagination
$sql = "SELECT courses.*, courses.university_id AS university_ids 
        FROM courses 
        ORDER BY courses.id ASC 
        LIMIT :offset, :records_per_page";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':records_per_page', $records_per_page, PDO::PARAM_INT);
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
                            <!-- Enhanced Search Filters -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <input class="form-control" id="nameSearch" type="text" placeholder="🔍 Search by Course Name">
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="universitySearch" type="text" placeholder="🔍 Search by University">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" id="statusFilter">
                                        <option value="">All Statuses</option>
                                        <option value="active">Active</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Table Structure -->
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
                                        <tr data-status="<?= htmlspecialchars($course['status'] ?? 'active') ?>">
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

                            <!-- Pagination Controls -->
                            <div class="row mt-3 align-items-center">
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <label class="mb-0 mr-2">Show</label>
                                        <select class="form-control form-control-sm w-auto" id="recordsPerPage">
                                            <option value="10" <?= $records_per_page == 10 ? 'selected' : '' ?>>10</option>
                                            <option value="25" <?= $records_per_page == 25 ? 'selected' : '' ?>>25</option>
                                            <option value="50" <?= $records_per_page == 50 ? 'selected' : '' ?>>50</option>
                                            <option value="100" <?= $records_per_page == 100 ? 'selected' : '' ?>>100</option>
                                        </select>
                                        <label class="mb-0 ml-2">entries</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-0">Showing <?= $offset + 1 ?> to <?= min($offset + $records_per_page, $total_records) ?> of <?= $total_records ?> entries</p>
                                </div>
                                <div class="col-md-5">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-end mb-0">
                                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                                <a class="page-link" href="?page=<?= $page-1 ?>&limit=<?= $records_per_page ?>" <?= ($page <= 1) ? 'tabindex="-1"' : '' ?>>Previous</a>
                                            </li>
                                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                                    <a class="page-link" href="?page=<?= $i ?>&limit=<?= $records_per_page ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                                <a class="page-link" href="?page=<?= $page+1 ?>&limit=<?= $records_per_page ?>" <?= ($page >= $total_pages) ? 'tabindex="-1"' : '' ?>>Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>

                            <div id="noRecords" style="display: none;" class="text-center">No records found</div>
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

<!-- Updated JavaScript -->
<script>
$(document).ready(function() {
    // Initialize dropdowns
    $('.dropdown-toggle').dropdown();
    
    // Combined search function
    function filterTable() {
        var nameValue = $('#nameSearch').val().toLowerCase();
        var universityValue = $('#universitySearch').val().toLowerCase();
        var statusValue = $('#statusFilter').val().toLowerCase();
        var visibleRows = 0;
        
        $('#courseTable tr').each(function() {
            var row = $(this);
            var name = row.find('td.col-course-name').text().toLowerCase();
            var university = row.find('td.col-university').text().toLowerCase();
            var status = row.data('status').toLowerCase();
            
            var nameMatch = name.indexOf(nameValue) > -1;
            var universityMatch = university.indexOf(universityValue) > -1;
            var statusMatch = statusValue === '' || status === statusValue;
            
            var isVisible = nameMatch && universityMatch && statusMatch;
            row.toggle(isVisible);
            if (isVisible) visibleRows++;
        });
        
        $('#noRecords').toggle(visibleRows === 0);
    }
    
    // Attach event listeners to all search inputs
    $('#nameSearch, #universitySearch, #statusFilter').on('keyup change', filterTable);
    
    // Records per page change handler
    $('#recordsPerPage').on('change', function() {
        var newLimit = $(this).val();
        window.location.href = updateQueryStringParameter(window.location.href, 'limit', newLimit);
    });
    
    // Table sorting
    $('th[data-sort]').on('click', function() {
        var table = $(this).parents('table').eq(0);
        var rows = table.find('tbody tr').toArray().sort(comparer($(this).index()));
        this.asc = !this.asc;
        if (!this.asc) { rows = rows.reverse(); }
        for (var i = 0; i < rows.length; i++) { table.append(rows[i]); }
    });
});

// Helper function to update URL parameters
function updateQueryStringParameter(uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    }
    else {
        return uri + separator + key + "=" + value;
    }
}
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
    .pagination {
        margin-bottom: 0;
    }
    .page-link {
        padding: 0.5rem 0.75rem;
    }
    .search-filters {
        margin-bottom: 1rem;
    }
    @media (max-width: 768px) {
        .col-serial-number, .col-course-name, .col-university, .col-actions {
            width: auto;
        }
        .search-filters > div {
            margin-bottom: 0.5rem;
        }
    }
</style>