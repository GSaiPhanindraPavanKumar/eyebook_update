<?php include 'sidebar.php'; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage Companies</h3>
                    </div>
                    <div class="col-12 col-xl-4 text-right">
                        <a href="addCompany" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Create Company
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Companies</p><br>
                        <div class="table-responsive">
                            <input class="form-control mb-3" id="searchInput" type="text" placeholder="🔍 Search Companies...">
                            <form id="companyForm" method="post" action="/admin/view_company">
                                <table class="table table-hover table-borderless table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th data-sort="serialNumber">S.no <i class="fas fa-sort"></i></th>
                                            <th data-sort="name">Company Name <i class="fas fa-sort"></i></th>
                                            <th data-sort="description">Description <i class="fas fa-sort"></i></th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="companyTable">
                                        <?php
                                        $serialNumber = 1;
                                        $limit = 10;
                                        $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                        $offset = ($page - 1) * $limit;
                                        $total_companies = count($companies);
                                        $total_pages = ceil($total_companies / $limit);
                                        $companies_paginated = array_slice($companies, $offset, $limit);

                                        foreach ($companies_paginated as $company): ?>
                                            <tr>
                                                <td><?= $serialNumber++ ?></td>
                                                <td><?= htmlspecialchars($company['name']) ?></td>
                                                <td><?= htmlspecialchars($company['description']) ?></td>
                                                <td>
                                                    <a href="/admin/view_company/<?= $company['id'] ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i> View</a>
                                                    <a href="/admin/edit_company/<?= $company['id'] ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                                    <form action="/admin/deleteCompany" method="post" style="display:inline;">
                                                        <input type="hidden" name="id" value="<?= $company['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this company?');"><i class="fas fa-trash"></i> Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div id="noRecords" style="display: none;" class="text-center">No records found</div>
                            </form>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
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
                    <h5 class="modal-title" id="editModalLabel">Edit Company</h5>
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
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script>
    $(document).ready(function() {
        $('.edit-btn').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var description = $(this).data('description');
            $('#edit-id').val(id);
            $('#edit-name').val(name);
            $('#edit-description').val(description);
            $('#editModal').modal('show');
        });

        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            var visibleRows = 0;
            $('#companyTable tr').filter(function() {
                var isVisible = $(this).text().toLowerCase().indexOf(value) > -1;
                $(this).toggle(isVisible);
                if (isVisible) visibleRows++;
            });
            $('#noRecords').toggle(visibleRows === 0);
        });

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