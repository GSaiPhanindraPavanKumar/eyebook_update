<?php
include 'sidebar.php'; 
use Models\Database;
use Models\University;
use Models\Faculty;

$conn = Database::getConnection();
$universities = University::getAll($conn);
$faculty = Faculty::getAll($conn);
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage Faculty</h3>
                    </div>
                    <div class="col-12 col-xl-4 text-right">
                        <a href="upload_faculty" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Add Faculty
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Faculty</p><br>
                        <div class="table-responsive">
                            <div class="input-group mb-3">
                                <input class="form-control" id="searchInput" type="text" placeholder="🔍 Search Faculty...">
                                <div class="input-group-append">
                                    <select class="form-control" id="filterSelect">
                                        <option value="">Filter by...</option>
                                        <option value="name">Name</option>
                                        <option value="email">Email</option>
                                        <option value="university">University</option>
                                    </select>
                                </div>
                            </div>
                            <form id="facultyForm" method="post" action="/admin/resetFacultyPasswords">
                                <table class="table table-hover table-borderless table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th><input type="checkbox" id="selectAll"></th>
                                            <th data-sort="serialNumber">S.No<i class="fas fa-sort"></i></th>
                                            <th data-sort="name">Name <i class="fas fa-sort"></i></th>
                                            <th data-sort="email">Email <i class="fas fa-sort"></i></th>
                                            <th data-sort="university">University <i class="fas fa-sort"></i></th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="facultyTable">
                                        <?php
                                        $serialNumber = 1;
                                        $limit = 10;
                                        $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                        $offset = ($page - 1) * $limit;
                                        $total_faculty = count($faculty);
                                        $total_pages = ceil($total_faculty / $limit);
                                        $faculty_paginated = array_slice($faculty, $offset, $limit);

                                        foreach ($faculty_paginated as $member):
                                            $university_short_name = '';
                                            foreach ($universities as $university) {
                                                if ($university['id'] == $member['university_id']) {
                                                    $university_short_name = $university['short_name'];
                                                    break;
                                                }
                                            }
                                        ?>
                                            <tr>
                                                <td><input type="checkbox" name="selected[]" value="<?= $member['id'] ?>"></td>
                                                <td><?= $serialNumber++ ?></td>
                                                <td data-filter="name"><?= htmlspecialchars($member['name']) ?></td>
                                                <td data-filter="email"><?= htmlspecialchars($member['email']) ?></td>
                                                <td data-filter="university"><?= htmlspecialchars($university_short_name) ?></td>
                                                <td>
                                                    <a href="viewFacultyProfile/<?= $member['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i> View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div id="noRecords" style="display: none;" class="text-center">No records found</div>
                                <div class="text-right">
                                    <button type="submit" name="bulk_reset_password" class="btn btn-warning">Selected Reset Password</button>
                                    <button type="submit" name="bulk_delete" class="btn btn-danger">Delete Selected</button>
                                </div>
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
            <form id="editForm" method="post" action="updateFaculty.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Faculty</h5>
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
            var name = $(this).data('name');
            var email = $(this).data('email');
            $('#edit-id').val(id);
            $('#edit-name').val(name);
            $('#edit-email').val(email);
            $('#editModal').modal('show');
        });

        $('#searchInput, #filterSelect').on('input change', function() {
            var searchValue = $('#searchInput').val().toLowerCase();
            var filterValue = $('#filterSelect').val();
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

        $('#selectAll').on('click', function() {
            $('input[name="selected[]"]').prop('checked', this.checked);
        });
    });
</script>