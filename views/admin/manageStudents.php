<?php 
include 'sidebar.php'; 
use Models\Database;
use Models\University;
use Models\Student;

$conn = Database::getConnection();
$universities = University::getAll($conn);

// Get all students for client-side search
$allStudents = Student::getAll($conn);

function daysAgo($date) {
    if ($date === null) {
        return 'N/A';
    }
    $now = new DateTime();
    $lastUsage = new DateTime($date);
    $interval = $now->diff($lastUsage);
    return $interval->days;
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
                    <div class="col-12 col-xl-4 text-right">
                        <a href="uploadStudents" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Add Student
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Students</p><br>

                        <!-- Display session message -->
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                                <?= $_SESSION['message'] ?>
                                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <div class="input-group mb-3">
                                <input class="form-control" id="searchInput" type="text" placeholder="🔍 Search Student by Name, Regd no or Email..." style="height: 38px; border-radius: 5px;">
                                <div class="input-group-append">
                                    <button style="font-size: 10px;" class="btn btn-secondary" type="button" id="clearSearch"><i style="font-size: 10px;" class="fas fa-times"></i></button>
                                </div>
                            </div>

                            <form id="studentForm" method="post" action="/admin/delete_students">
                                <table class="table table-hover table-borderless table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th><input type="checkbox" id="selectAll"></th>
                                            <th>S.No</th>
                                            <th class="sortable" data-sort="regd_no">
                                                Registration Number <i class="fas fa-sort"></i>
                                            </th>
                                            <th class="sortable" data-sort="name">
                                                Name <i class="fas fa-sort"></i>
                                            </th>
                                            <th class="sortable" data-sort="email">
                                                Email <i class="fas fa-sort"></i>
                                            </th>
                                            <th class="sortable" data-sort="university_short_name">
                                                University <i class="fas fa-sort"></i>
                                            </th>
                                            <th class="sortable" data-sort="last_login">
                                                Last Usage (Days Ago) <i class="fas fa-sort"></i>
                                            </th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="studentTableBody">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                                <div id="noRecords" style="display: none;" class="text-center">No records found</div>
                                <div class="text-right">
                                    <button type="button" onclick="bulkResetPassword()" class="btn btn-warning">Selected Reset Password</button>
                                    <button type="submit" name="bulk_delete" class="btn btn-danger">Delete Selected</button>
                                </div>
                            </form>

                            <!-- Pagination -->
                            <div class="pagination-container">
                                <div class="entries-container">
                                    <span class="page-info">Show</span>
                                    <select class="form-control records-per-page" id="recordsPerPage">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <span class="page-info">entries</span>
                                </div>
                                
                                <ul class="pagination" id="pagination">
                                    <!-- Pagination will be generated by JavaScript -->
                                </ul>
                                
                                <div class="page-info">
                                    Showing <span id="startRecord">1</span> to <span id="endRecord">10</span> of <span id="totalRecords">0</span> entries
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editForm" method="post" action="updateStudent.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="form-group">
                        <label for="edit-regd_no">Registration Number</label>
                        <input type="text" class="form-control" id="edit-regd_no" name="regd_no" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-name">Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-email">Email</label>
                        <input type="email" class="form-control" id="edit-email" name="email" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="update" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script>
    $(document).ready(function() {
        $('.edit-btn').on('click', function() {
            var id = $(this).data('id');
            var regd_no = $(this).data('regd_no');
            var name = $(this).data('name');
            var email = $(this).data('email');
            $('#edit-id').val(id);
            $('#edit-regd_no').val(regd_no);
            $('#edit-name').val(name);
            $('#edit-email').val(email);
            $('#editModal').modal('show');
        });

        $('#selectAll').on('click', function() {
            $('input[name="selected[]"]').prop('checked', this.checked);
        });
    });

    function bulkResetPassword() {
        var selected = [];
        $('input[name="selected[]"]:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            alert('Please select at least one student.');
            return;
        }

        var newPassword = prompt("Enter new password for selected students:");
        if (newPassword) {
            var confirmPassword = prompt("Confirm new password:");
            if (newPassword === confirmPassword) {
                $.ajax({
                    type: 'POST',
                    url: '/admin/bulk_reset_student_password',
                    data: JSON.stringify({ selected: selected, new_password: newPassword, confirm_password: confirmPassword }),
                    contentType: 'application/json',
                    success: function(response) {
                        console.log(response);
                        alert('Passwords reset successfully.');
                        location.reload();
                    },
                    error: function(response) {
                        console.log(response);
                        alert('An error occurred while resetting the passwords.');
                    }
                });
            } else {
                alert("Passwords do not match.");
            }
        }
    }
</script>
<script>
    $(document).ready(function() {
        $('#clearSearch').on('click', function() {
            $('#searchInput').val('');
            window.location.href = window.location.pathname;
        });
    });
</script>
<script>
// Store all records for client-side pagination and search
const allRecords = <?= json_encode($allStudents) ?>;
let currentPage = 1;
let recordsPerPage = 10;
let filteredRecords = [...allRecords];

// Add these variables at the top with other declarations
let currentSort = {
    column: 'name',
    direction: 'asc'
};

function generateTableRow(student, index) {
    const daysAgo = student.last_login ? calculateDaysAgo(student.last_login) : 'N/A';
    return `
        <tr>
            <td><input type="checkbox" name="selected[]" value="${student.id}"></td>
            <td>${index + 1}</td>
            <td>${student.regd_no}</td>
            <td>${student.name}</td>
            <td>${student.email}</td>
            <td>${student.university_short_name}</td>
            <td>${daysAgo}</td>
            <td>
                <a href="viewStudentProfile/${student.id}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye"></i> View
                </a>
            </td>
        </tr>
    `;
}

function calculateDaysAgo(date) {
    if (!date) return 'N/A';
    const now = new Date();
    const lastUsage = new Date(date);
    const diffTime = Math.abs(now - lastUsage);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

function filterAndDisplayRecords() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    // Filter records
    filteredRecords = allRecords.filter(student => {
        return student.name.toLowerCase().includes(searchTerm) ||
               student.regd_no.toLowerCase().includes(searchTerm) ||
               student.email.toLowerCase().includes(searchTerm) ||
               student.university_short_name.toLowerCase().includes(searchTerm);
    });
    
    // Sort records
    filteredRecords.sort((a, b) => {
        let aValue = a[currentSort.column];
        let bValue = b[currentSort.column];
        
        // Special handling for last_login
        if (currentSort.column === 'last_login') {
            aValue = aValue ? new Date(aValue).getTime() : 0;
            bValue = bValue ? new Date(bValue).getTime() : 0;
        } else {
            // Convert to lowercase for string comparison
            aValue = String(aValue).toLowerCase();
            bValue = String(bValue).toLowerCase();
        }
        
        if (aValue < bValue) return currentSort.direction === 'asc' ? -1 : 1;
        if (aValue > bValue) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    });
    
    updatePagination();
    displayCurrentPage();
    updateSortIcons();
}

function displayCurrentPage() {
    const startIndex = (currentPage - 1) * recordsPerPage;
    const endIndex = Math.min(startIndex + recordsPerPage, filteredRecords.length);
    const recordsToShow = filteredRecords.slice(startIndex, endIndex);
    
    const tableBody = document.getElementById('studentTableBody');
    tableBody.innerHTML = recordsToShow.map((student, idx) => 
        generateTableRow(student, startIndex + idx)
    ).join('');
    
    // Update page info
    document.getElementById('startRecord').textContent = filteredRecords.length ? startIndex + 1 : 0;
    document.getElementById('endRecord').textContent = endIndex;
    document.getElementById('totalRecords').textContent = filteredRecords.length;
    
    // Show/hide no records message
    const table = document.querySelector('.table');
    const noRecords = document.getElementById('noRecords');
    table.style.display = filteredRecords.length ? '' : 'none';
    noRecords.style.display = filteredRecords.length ? 'none' : 'block';
}

function updatePagination() {
    const totalPages = Math.ceil(filteredRecords.length / recordsPerPage);
    const pagination = document.getElementById('pagination');
    
    let paginationHtml = '';
    
    // Previous button
    paginationHtml += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            paginationHtml += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            paginationHtml += `
                <li class="page-item disabled">
                    <a class="page-link" href="#">...</a>
                </li>
            `;
        }
    }
    
    // Next button
    paginationHtml += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
        </li>
    `;
    
    pagination.innerHTML = paginationHtml;
    
    // Add click handlers
    pagination.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const newPage = parseInt(e.target.dataset.page);
            if (!isNaN(newPage) && newPage > 0 && newPage <= totalPages) {
                currentPage = newPage;
                displayCurrentPage();
                updatePagination();
            }
        });
    });
}

function updateSortIcons() {
    // Remove all sort classes
    document.querySelectorAll('.sortable').forEach(th => {
        th.classList.remove('asc', 'desc');
    });
    
    // Add sort class to current sort column
    const currentTh = document.querySelector(`.sortable[data-sort="${currentSort.column}"]`);
    if (currentTh) {
        currentTh.classList.add(currentSort.direction);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initial display
    filterAndDisplayRecords();
    
    // Search input handler
    document.getElementById('searchInput').addEventListener('input', () => {
        currentPage = 1;
        filterAndDisplayRecords();
    });
    
    // Clear search handler
    document.getElementById('clearSearch').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        currentPage = 1;
        filterAndDisplayRecords();
    });
    
    // Records per page handler
    document.getElementById('recordsPerPage').addEventListener('change', (e) => {
        recordsPerPage = parseInt(e.target.value);
        currentPage = 1;
        filterAndDisplayRecords();
    });
    
    // Select all checkbox handler
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('input[name="selected[]"]')
            .forEach(checkbox => checkbox.checked = this.checked);
    });
    
    // Add sort handlers
    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const column = th.dataset.sort;
            
            // Toggle sort direction if clicking the same column
            if (currentSort.column === column) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = column;
                currentSort.direction = 'asc';
            }
            
            filterAndDisplayRecords();
        });
    });
    
    // Initial sort
    updateSortIcons();
});
</script>

<!-- Add this style block after your existing styles -->
<style>
    /* Previous styles remain... */

    /* Records per page select styling */
    .entries-container {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .records-per-page {
        width: 70px !important;
        height: 35px;
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #ddd;
        background-color: white;
        font-size: 0.875rem;
    }

    .page-info {
        color: #6c757d;
        font-size: 0.875rem;
        white-space: nowrap;
    }

    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-top: 1rem;
    }

    /* Ensure the pagination is centered */
    .pagination {
        margin: 0;
    }

    .sortable {
        cursor: pointer;
        user-select: none;
    }

    .sortable:hover {
        background-color: #f8f9fa;
    }

    .sortable i {
        margin-left: 5px;
        color: #ddd;
    }

    .sortable.asc i::before {
        content: "\f0de";
        color: #1971c2;
    }

    .sortable.desc i::before {
        content: "\f0dd";
        color: #1971c2;
    }

    .table thead th {
        border-top: none;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        padding: 12px 8px;
    }
</style>