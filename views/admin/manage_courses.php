<?php
include("sidebar.php");
use Models\Database;
$conn = Database::getConnection();

// Get all courses for search and filtering
$all_sql = "SELECT courses.*, courses.university_id AS university_ids, 
            courses.name, courses.status FROM courses 
            ORDER BY courses.id ASC";
$all_stmt = $conn->prepare($all_sql);
$all_stmt->execute();
$all_courses = $all_stmt->fetchAll(PDO::FETCH_ASSOC);

// Process all courses
$processed_courses = array();
foreach ($all_courses as $course) {
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

// Convert to indexed array and JSON for JavaScript
$courses = array_values($processed_courses);
$courses_json = json_encode($courses, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

// Calculate initial pagination values
$records_per_page = 10;
$total_records = count($courses);
$total_pages = ceil($total_records / $records_per_page);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($page, $total_pages)); // Ensure page is within valid range
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
                            <!-- Search Filters -->
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
                                        <th class="col-serial-number">S.no</th>
                                        <th class="col-course-name">Course Name</th>
                                        <th class="col-university">University</th>
                                        <th class="col-actions">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="courseTable">
                                    <!-- Table content will be dynamically populated -->
                                </tbody>
                            </table>

                            <!-- Pagination Controls -->
                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <select class="form-control" id="recordsPerPage">
                                        <option value="10">10 records</option>
                                        <option value="25">25 records</option>
                                        <option value="50">50 records</option>
                                        <option value="100">100 records</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <p class="records-info mb-0"></p>
                                </div>
                                <div class="col-md-5">
                                    <nav aria-label="Page navigation" class="pagination-container">
                                        <ul class="pagination justify-content-end mb-0">
                                            <!-- Pagination will be dynamically populated -->
                                        </ul>
                                    </nav>
                                </div>
                            </div>

                            <div id="noRecords" style="display: none;" class="text-center mt-4">
                                <img src="https://i.ibb.co/0SpmPCg/empty-box.png" alt="No records found" style="max-width: 150px; margin-bottom: 15px;">
                                <p class="text-muted">No matching courses found</p>
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

<!-- Move these script tags before your existing scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>

<!-- Updated JavaScript -->
<script>
$(document).ready(function() {
    // Parse courses data from PHP
    let allCourses = <?php echo $courses_json; ?>;
    console.log('Total courses loaded:', allCourses.length); // Debug line
    
    let currentPage = 1;
    let recordsPerPage = parseInt($('#recordsPerPage').val() || 10);

    function filterCourses() {
        const nameValue = $('#nameSearch').val().toLowerCase().trim();
        const universityValue = $('#universitySearch').val().toLowerCase().trim();
        const statusValue = $('#statusFilter').val().toLowerCase();

        return allCourses.filter(course => {
            const name = (course.name || '').toLowerCase();
            const university = (course.university || '').toLowerCase();
            const status = (course.status || 'active').toLowerCase();

            return (!nameValue || name.includes(nameValue)) &&
                   (!universityValue || university.includes(universityValue)) &&
                   (!statusValue || status === statusValue);
        });
    }

    function displayCourses() {
        const filteredCourses = filterCourses();
        const totalRecords = filteredCourses.length;
        const totalPages = Math.ceil(totalRecords / recordsPerPage);
        
        // Ensure current page is within valid range
        if (currentPage > totalPages) {
            currentPage = totalPages || 1;
        }
        
        const startIndex = (currentPage - 1) * recordsPerPage;
        const endIndex = Math.min(startIndex + recordsPerPage, totalRecords);
        const displayedCourses = filteredCourses.slice(startIndex, endIndex);

        const tbody = $('#courseTable');
        tbody.empty();

        if (displayedCourses.length === 0) {
            $('#noRecords').show();
            $('.pagination-container').hide();
            $('.records-info').text('No matching records found');
        } else {
            $('#noRecords').hide();

            displayedCourses.forEach((course, index) => {
                const row = `
                    <tr data-status="${course.status || 'active'}">
                        <td class="col-serial-number">${startIndex + index + 1}</td>
                        <td class="col-course-name">${escapeHtml(course.name || '')}</td>
                        <td class="col-university">${escapeHtml(course.university || 'N/A')}</td>
                        <td class="col-actions">
                            <a href="/admin/view_course/${course.id}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="/admin/edit_course/${course.id}" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            ${getArchiveButton(course)}
                            <a href="/admin/delete_course/${course.id}" class="btn btn-outline-danger btn-sm" 
                               onclick="return confirm('Are you sure you want to delete this course?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            // Update pagination info
            updatePaginationInfo(startIndex + 1, endIndex, totalRecords);
            updatePaginationControls(totalPages);
        }
    }

    function updatePaginationInfo(start, end, total) {
        $('.records-info').html(
            `Showing ${start} to ${end} of ${total} entries`
        );
    }

    function updatePaginationControls(totalPages) {
        const pagination = $('.pagination');
        pagination.empty();

        if (totalPages <= 1) {
            $('.pagination-container').hide();
            return;
        }

        $('.pagination-container').show();

        // Previous button
        pagination.append(`
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">&laquo; Previous</a>
            </li>
        `);

        // Calculate visible page range
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        // First page and ellipsis
        if (startPage > 1) {
            pagination.append('<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>');
            if (startPage > 2) {
                pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }
        }

        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            pagination.append(`
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // Last page and ellipsis
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }
            pagination.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
                </li>
            `);
        }

        // Next button
        pagination.append(`
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next &raquo;</a>
            </li>
        `);
    }

    function getArchiveButton(course) {
        if (course.status === 'archived') {
            return `
                <form method="POST" action="/admin/unarchive_course" style="display:inline;" onsubmit="return confirmUnarchive()">
                    <input type="hidden" name="archive_course_id" value="${course.id}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-archive"></i> Unarchive
                    </button>
                </form>
            `;
        } else {
            return `
                <form method="POST" action="/admin/archive_course" style="display:inline;" onsubmit="return confirmArchive()">
                    <input type="hidden" name="archive_course_id" value="${course.id}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-archive"></i> Archive
                    </button>
                </form>
            `;
        }
    }
    
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Event Listeners
    $('#nameSearch, #universitySearch, #statusFilter').on('input change', function() {
        currentPage = 1; // Reset to first page when searching
        displayCourses();
    });

    $('#recordsPerPage').on('change', function() {
        recordsPerPage = parseInt($(this).val());
        currentPage = 1; // Reset to first page when changing records per page
        displayCourses();
    });

    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        const newPage = parseInt($(this).data('page'));
        if (!isNaN(newPage) && newPage > 0) {
            currentPage = newPage;
            displayCourses();
            // Scroll to top of table
            $('.table-responsive').get(0).scrollIntoView({ behavior: 'smooth' });
        }
    });

    // Initial display
    displayCourses();
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