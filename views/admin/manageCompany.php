<?php include 'sidebar.php'; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage University</h3>
                    </div>
                    <div class="col-12 col-xl-4 text-right">
                        <a href="addCompany" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Create University
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Universities</p><br>
                        <div class="table-responsive">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <input class="form-control w-75" id="searchInput" type="text" placeholder="🔍 Search Universities...">
                                <div>
                                    <select id="entriesPerPage" class="form-control">
                                        <option value="10">10 entries</option>
                                        <option value="25">25 entries</option>
                                        <option value="50">50 entries</option>
                                        <option value="100">100 entries</option>
                                    </select>
                                </div>
                            </div>
                            
                            <table class="table table-hover table-borderless table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th data-sort="serialNumber">S.no <i class="fas fa-sort"></i></th>
                                        <th data-sort="name">University Name <i class="fas fa-sort"></i></th>
                                        <th data-sort="description">Description <i class="fas fa-sort"></i></th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="companyTable">
                                    <?php foreach ($companies as $index => $company): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($company['name']) ?></td>
                                            <td><?= htmlspecialchars($company['description']) ?></td>
                                            <td>
                                                <a href="/admin/view_company/<?= $company['id'] ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i> View</a>
                                                <a href="/admin/edit_company/<?= $company['id'] ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                                <form action="/admin/deleteCompany" method="post" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?= $company['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this university?');"><i class="fas fa-trash"></i> Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div id="noRecords" style="display: none;" class="text-center">No records found</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div id="tableInfo" class="text-muted"></div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination" id="pagination"></ul>
                            </nav>
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
            <form id="editForm" method="post" action="updateCompany.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit University</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="form-group">
                        <label for="edit-name">Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-description">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" required></textarea>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>

<script>
$(document).ready(function() {
    let allRows = $('#companyTable tr').toArray();
    let currentPage = 1;
    let rowsPerPage = parseInt($('#entriesPerPage').val());
    let filteredRows = allRows;

    function updateTable() {
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;
        const rows = filteredRows.slice(startIndex, endIndex);

        $('#companyTable').empty();
        if (rows.length === 0) {
            $('#noRecords').show();
        } else {
            $('#noRecords').hide();
            rows.forEach((row, index) => {
                $(row).find('td:first').text(startIndex + index + 1);
                $('#companyTable').append(row);
            });
        }

        updatePagination();
        updateTableInfo();
    }

    function updatePagination() {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        const $pagination = $('#pagination');
        $pagination.empty();

        // Previous button
        $pagination.append(`
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
            </li>
        `);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            $pagination.append(`
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // Next button
        $pagination.append(`
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>
        `);
    }

    function updateTableInfo() {
        const totalRows = filteredRows.length;
        const startIndex = (currentPage - 1) * rowsPerPage + 1;
        const endIndex = Math.min(startIndex + rowsPerPage - 1, totalRows);
        
        $('#tableInfo').text(
            `Showing ${totalRows === 0 ? 0 : startIndex} to ${endIndex} of ${totalRows} entries`
        );
    }

    // Search functionality
    $('#searchInput').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        filteredRows = allRows.filter(row => 
            $(row).text().toLowerCase().includes(searchTerm)
        );
        currentPage = 1;
        updateTable();
    });

    // Entries per page change
    $('#entriesPerPage').on('change', function() {
        rowsPerPage = parseInt($(this).val());
        currentPage = 1;
        updateTable();
    });

    // Pagination click handlers
    $('#pagination').on('click', '.page-link', function(e) {
        e.preventDefault();
        const newPage = parseInt($(this).data('page'));
        if (newPage > 0 && newPage <= Math.ceil(filteredRows.length / rowsPerPage)) {
            currentPage = newPage;
            updateTable();
        }
    });

    // Sorting functionality
    $('th[data-sort]').on('click', function() {
        const column = $(this).data('sort');
        const index = $(this).index();
        
        filteredRows.sort((a, b) => {
            let valA = $(a).find('td').eq(index).text();
            let valB = $(b).find('td').eq(index).text();
            
            if (column === 'serialNumber') {
                return parseInt(valA) - parseInt(valB);
            }
            return valA.localeCompare(valB);
        });

        if (this.asc = !this.asc) {
            filteredRows.reverse();
        }
        updateTable();
    });

    // Initial table setup
    updateTable();
});
</script>