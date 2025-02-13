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

$facultyId = $_SESSION['faculty_id'];

// Fetch the assigned courses for the faculty
$assignedCourses = Faculty::getAssignedCourses($conn, $facultyId);

// Initialize arrays
$students = [];
$courseNames = [];
$assignedStudents = [];

// Fetch students from assigned courses
if (!empty($assignedCourses)) {
    // First get all student IDs and course names
    foreach ($assignedCourses as $courseId) {
        $sql = "SELECT id, assigned_students, name as course_name FROM courses WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$courseId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && $row['assigned_students']) {
            $studentIds = json_decode($row['assigned_students'], true);
            if (is_array($studentIds)) {
                foreach ($studentIds as $studentId) {
                    $assignedStudents[] = $studentId;
                    if (!isset($courseNames[$studentId])) {
                        $courseNames[$studentId] = [];
                    }
                    $courseNames[$studentId][] = $row['course_name'];
                }
            }
        }
    }
    
    // Remove duplicates
    $assignedStudents = array_unique($assignedStudents);
    
    // If we have students, fetch their details
    if (!empty($assignedStudents)) {
        try {
            $placeholders = str_repeat('?,', count($assignedStudents) - 1) . '?';
            $sql = "SELECT * FROM students WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            
            // Convert to array of values and execute
            $params = array_values($assignedStudents);
            $stmt->execute($params);
            
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Database error in manage_students.php: " . $e->getMessage());
            echo "<div class='alert alert-danger'>Error fetching student data. Please try again later.</div>";
        }
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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="card-title mb-0" style="font-size:larger">Students</p>
                            <div class="d-flex align-items-center">
                                <select id="entriesPerPage" class="form-control mr-2" style="width: auto;">
                                    <option value="5">5 entries</option>
                                    <option value="10" selected>10 entries</option>
                                    <option value="25">25 entries</option>
                                    <option value="50">50 entries</option>
                                </select>
                                <input class="form-control" id="searchInput" type="text" placeholder="Search...">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-borderless">
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
                                    <?php foreach ($students as $index => $student): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($student['regd_no']) ?></td>
                                            <td><?= htmlspecialchars($student['name']) ?></td>
                                            <td><?= htmlspecialchars($student['email']) ?></td>
                                            <td><?= htmlspecialchars($student['section']) ?></td>
                                            <td><?= htmlspecialchars($student['stream']) ?></td>
                                            <td><?= htmlspecialchars($student['year'] ?? 0) ?></td>
                                            <td><?= htmlspecialchars(implode(', ', $courseNames[$student['id']] ?? ['N/A'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div id="noRecords" class="text-center py-3" style="display: none;">
                                No records found
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted" id="showing-entries">
                                Showing 1 to 10 of 0 entries
                            </div>
                            <div class="pagination-container">
                                <ul class="pagination" id="pagination">
                                    <!-- Pagination will be dynamically populated -->
                                </ul>
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
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const entriesPerPage = document.getElementById('entriesPerPage');
    const studentTable = document.getElementById('studentTable');
    const noRecords = document.getElementById('noRecords');
    const showingEntries = document.getElementById('showing-entries');
    const pagination = document.getElementById('pagination');
    
    // Store all original rows for searching across all pages
    const originalRows = Array.from(studentTable.getElementsByTagName('tr'));
    let filteredRows = [...originalRows];
    let currentPage = 1;
    
    function filterRows(searchTerm) {
        searchTerm = searchTerm.toLowerCase();
        
        // Filter all rows based on search term
        filteredRows = originalRows.filter(row => {
            const cells = Array.from(row.getElementsByTagName('td'));
            return cells.some(cell => cell.textContent.toLowerCase().includes(searchTerm));
        });
        
        // Reset to first page when searching
        currentPage = 1;
        updateTableDisplay();
    }
    
    function updateTableDisplay() {
        const pageSize = parseInt(entriesPerPage.value);
        const pageCount = Math.ceil(filteredRows.length / pageSize);
        
        // Ensure current page is valid
        if (currentPage > pageCount) {
            currentPage = pageCount;
        }
        if (currentPage < 1) {
            currentPage = 1;
        }
        
        // Calculate start and end indices for current page
        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, filteredRows.length);
        
        // Update showing entries text
        showingEntries.textContent = filteredRows.length > 0 
            ? `Showing ${startIndex + 1} to ${endIndex} of ${filteredRows.length} entries`
            : 'Showing 0 entries';
        
        // Clear and update table
        studentTable.innerHTML = '';
        
        // Display rows for current page
        const visibleRows = filteredRows.slice(startIndex, endIndex);
        visibleRows.forEach((row, index) => {
            const newRow = row.cloneNode(true);
            // Update serial number to be continuous across pages
            newRow.getElementsByTagName('td')[0].textContent = startIndex + index + 1;
            studentTable.appendChild(newRow);
        });
        
        // Show/hide no records message
        noRecords.style.display = filteredRows.length === 0 ? 'block' : 'none';
        
        // Update pagination
        updatePagination(pageCount);
    }
    
    function updatePagination(pageCount) {
        pagination.innerHTML = '';
        
        // Only show pagination if we have more than one page
        if (pageCount <= 1) {
            return;
        }
        
        // Previous button
        addPaginationButton('Previous', () => {
            if (currentPage > 1) {
                currentPage--;
                updateTableDisplay();
            }
        }, currentPage === 1);
        
        // First page
        addPaginationButton(1, () => {
            currentPage = 1;
            updateTableDisplay();
        }, currentPage === 1);
        
        // Ellipsis and middle pages
        if (pageCount > 7) {
            if (currentPage > 3) {
                pagination.appendChild(createEllipsis());
            }
            
            const start = Math.max(2, currentPage - 1);
            const end = Math.min(pageCount - 1, currentPage + 1);
            
            for (let i = start; i <= end; i++) {
                addPaginationButton(i, () => {
                    currentPage = i;
                    updateTableDisplay();
                }, currentPage === i);
            }
            
            if (currentPage < pageCount - 2) {
                pagination.appendChild(createEllipsis());
            }
        } else {
            // Show all pages if total pages are 7 or less
            for (let i = 2; i < pageCount; i++) {
                addPaginationButton(i, () => {
                    currentPage = i;
                    updateTableDisplay();
                }, currentPage === i);
            }
        }
        
        // Last page if not already shown
        if (pageCount > 1) {
            addPaginationButton(pageCount, () => {
                currentPage = pageCount;
                updateTableDisplay();
            }, currentPage === pageCount);
        }
        
        // Next button
        addPaginationButton('Next', () => {
            if (currentPage < pageCount) {
                currentPage++;
                updateTableDisplay();
            }
        }, currentPage === pageCount);
    }
    
    function addPaginationButton(text, onClick, isActive = false, isDisabled = false) {
        const li = document.createElement('li');
        li.className = `page-item ${isActive ? 'active' : ''} ${isDisabled ? 'disabled' : ''}`;
        
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = text;
        
        if (!isDisabled) {
            a.onclick = (e) => {
                e.preventDefault();
                onClick();
            };
        }
        
        li.appendChild(a);
        pagination.appendChild(li);
    }
    
    function createEllipsis() {
        const li = document.createElement('li');
        li.className = 'page-item disabled';
        li.innerHTML = '<a class="page-link" href="#">...</a>';
        return li;
    }
    
    // Event listeners
    searchInput.addEventListener('input', (e) => {
        filterRows(e.target.value);
    });
    
    entriesPerPage.addEventListener('change', () => {
        currentPage = 1;
        updateTableDisplay();
    });
    
    // Initial display
    updateTableDisplay();

    const table = document.querySelector('table');
    
    table.addEventListener('click', function(e) {
        // Find the closest row to the clicked element
        const row = e.target.closest('tr');
        
        // Ensure we have a row and it's not the header row
        if (row && !row.closest('thead')) {
            // If the click was not on a button/link/form
            if (!e.target.closest('a') && !e.target.closest('button') && !e.target.closest('input')) {
                // Find the registration number from the row
                const regdNo = row.cells[1].textContent;
                // Redirect to student profile
                window.location.href = `/faculty/view_student_profile/${regdNo}`;
            }
        }
    });
});
</script>

<style>
.pagination-container {
    display: flex;
    justify-content: flex-end;
}

.pagination {
    margin: 0;
}

.page-link {
    padding: 0.5rem 0.75rem;
    margin-left: -1px;
    line-height: 1.25;
    color: #007bff;
    background-color: #fff;
    border: 1px solid #dee2e6;
}

.page-item.active .page-link {
    z-index: 3;
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
}

.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    cursor: auto;
    background-color: #fff;
    border-color: #dee2e6;
}

#searchInput {
    min-width: 200px;
}

.table th {
    white-space: nowrap;
}

.page-item {
    cursor: pointer;
}

.page-item.disabled {
    cursor: not-allowed;
}

.pagination .page-link {
    min-width: 40px;
    text-align: center;
}

.table-responsive {
    min-height: 400px;
}

tbody tr {
    cursor: pointer;
}

tbody tr:hover {
    background-color: rgba(0,0,0,0.05) !important;
}

tbody tr a,
tbody tr button,
tbody tr input {
    position: relative;
    z-index: 2;
}
</style>