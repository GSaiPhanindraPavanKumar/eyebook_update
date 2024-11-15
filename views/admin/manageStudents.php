<?php include 'sidebar.php'; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage Students</h3>
                    </div>
                    <div class="col-12 col-xl-4 text-right">
                        <a href="addStudent.php" class="btn btn-primary">
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
                        <div class="table-responsive">
                            <input class="form-control mb-3" id="searchInput" type="text" placeholder="🔍 Search Students...">
                            <form id="studentForm" method="post" action="deleteStudent.php">
                                <table class="table table-hover table-borderless table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th data-sort="serialNumber">S.No<i class="fas fa-sort"></i></th>
                                            <th data-sort="regd_no">Registration Number <i class="fas fa-sort"></i></th>
                                            <th data-sort="name">Name <i class="fas fa-sort"></i></th>
                                            <th data-sort="email">Email <i class="fas fa-sort"></i></th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="studentTable">
                                        <?php
                                        $serialNumber = 1;
                                        $limit = 10;
                                        $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                        $offset = ($page - 1) * $limit;
                                        $total_students = count($students);
                                        $total_pages = ceil($total_students / $limit);
                                        $students_paginated = array_slice($students, $offset, $limit);

                                        foreach ($students_paginated as $student): ?>
                                            <tr>
                                                <td><?= $serialNumber++ ?></td>
                                                <td><?= htmlspecialchars($student['regd_no']) ?></td>
                                                <td><?= htmlspecialchars($student['name']) ?></td>
                                                <td><?= htmlspecialchars($student['email']) ?></td>
                                                <td>
                                                    <button type="submit" name="view" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i>View</button>
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
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
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

        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            var visibleRows = 0;
            $('#studentTable tr').filter(function() {
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